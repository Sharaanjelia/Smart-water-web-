<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\Notification;
use App\Models\Tariff;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * Generate a bill from an approved meter reading.
     * Idempotent: if bill already exists for this meter reading, return it.
     */
    public function generateBillFromReading(MeterReading $reading): Bill
    {
        // 1. Idempotency check: if bill already exists, return existing bill
        if ($reading->bill) {
            return $reading->bill->load('details');
        }

        $existing = Bill::where('meter_reading_id', $reading->id)->first();
        if ($existing) {
            return $existing->load('details');
        }

        // 2. Validate reading status
        $allowedStatuses = ['Terverifikasi AI', 'Terverifikasi'];
        if (!in_array($reading->status, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'meter_reading' => 'Tagihan hanya dapat diterbitkan untuk laporan meter yang telah disetujui atau terverifikasi.',
            ]);
        }

        return DB::transaction(function () use ($reading) {
            // 3. Find customer profile and tariff
            $customerProfile = CustomerProfile::where('user_id', $reading->user_id)->first();
            $tariffCategory = $customerProfile?->tariff_category ?? '2A - Rumah Tangga Mandiri';

            // Find matching active tariff or fallback to first active tariff
            $tariff = Tariff::with('tiers')
                ->where('is_active', true)
                ->where(function ($query) use ($tariffCategory) {
                    $query->where('category_name', $tariffCategory)
                          ->orWhere('category_code', strtok($tariffCategory, ' -'));
                })
                ->first();

            if (!$tariff) {
                $tariff = Tariff::with('tiers')->where('is_active', true)->first();
            }

            // 4. Calculate consumption and tiered charges
            $consumption = max(0.0, (float) $reading->consumption);
            $breakdown = $this->calculateConsumptionCharges($consumption, $tariff);

            // 5. Billing Period & Due Date
            $readingDate = $reading->created_at ? Carbon::parse($reading->created_at) : now();
            $billingPeriod = $readingDate->format('Y-m');
            $periodLabel = $readingDate->locale('id')->isoFormat('MMMM Y');

            $dueDay = $tariff?->due_day_of_month ?? 20;
            $dueDate = $readingDate->copy()->addMonth()->startOfMonth()->setDay($dueDay);

            $billNumber = 'INV-PYK-' . $readingDate->format('Ym') . '-' . str_pad((string)$reading->id, 4, '0', STR_PAD_LEFT);

            // 6. Create Bill record
            $bill = Bill::create([
                'bill_number' => $billNumber,
                'meter_reading_id' => $reading->id,
                'user_id' => $reading->user_id,
                'customer_id' => $customerProfile?->customer_id ?? ('CUST-' . $reading->user_id),
                'meter_number' => $reading->meter_number,
                'billing_period' => $billingPeriod,
                'period_label' => $periodLabel,
                'tariff_category' => $tariff?->category_name ?? $tariffCategory,
                'previous_reading' => $reading->previous_reading ?? 0.00,
                'current_reading' => $reading->reading,
                'consumption' => $consumption,
                'water_charge' => $breakdown['water_charge'],
                'admin_fee' => $breakdown['admin_fee'],
                'maintenance_fee' => $breakdown['maintenance_fee'],
                'penalty_fee' => 0.00,
                'total_amount' => $breakdown['total_amount'],
                'due_date' => $dueDate->format('Y-m-d'),
                'status' => 'UNPAID',
                'tariff_snapshot' => [
                    'category_code' => $tariff?->category_code,
                    'category_name' => $tariff?->category_name,
                    'admin_fee' => $tariff?->admin_fee,
                    'maintenance_fee' => $tariff?->maintenance_fee,
                    'tiers' => $tariff?->tiers->map(fn($t) => [
                        'tier_number' => $t->tier_number,
                        'name' => $t->tier_name,
                        'min' => $t->min_consumption,
                        'max' => $t->max_consumption,
                        'rate' => (float)$t->rate_per_m3,
                    ])->toArray() ?? [],
                ],
            ]);

            // 7. Save Line Items into bill_details
            foreach ($breakdown['line_items'] as $item) {
                BillDetail::create([
                    'bill_id' => $bill->id,
                    'item_type' => $item['type'],
                    'description' => $item['description'],
                    'volume' => $item['volume'],
                    'rate' => $item['rate'],
                    'subtotal' => $item['subtotal'],
                ]);
            }

            // 8. Send in-app notification to customer
            Notification::create([
                'user_id' => $reading->user_id,
                'type' => 'billing',
                'title' => 'Tagihan Air Telah Terbit',
                'desc' => "Tagihan air periode {$periodLabel} sebesar Rp " . number_format($bill->total_amount, 0, ',', '.') . " telah diterbitkan. Silakan lakukan pembayaran sebelum tanggal " . $dueDate->format('d/m/Y') . ".",
                'link' => '/bill',
                'is_read' => false,
            ]);

            return $bill->load('details');
        });
    }

    /**
     * Compute consumption charges dynamically based on tariff tiers and admin fees.
     */
    public function calculateConsumptionCharges(float $consumption, ?Tariff $tariff): array
    {
        $lineItems = [];
        $totalWaterCharge = 0.0;

        if ($tariff && $tariff->tiers->isNotEmpty()) {
            $remaining = $consumption;
            $sortedTiers = $tariff->tiers->sortBy('tier_number');

            foreach ($sortedTiers as $tier) {
                if ($remaining <= 0 && $consumption > 0) {
                    break;
                }

                $min = (float) $tier->min_consumption;
                $max = $tier->max_consumption !== null ? (float) $tier->max_consumption : null;
                $rate = (float) $tier->rate_per_m3;

                // Determine tier capacity
                $capacity = ($max !== null) ? ($max - max(0.0, $min > 0 ? $min - 1 : 0)) : null;

                if ($capacity !== null) {
                    $billedVolume = min($remaining, $capacity);
                } else {
                    $billedVolume = $remaining;
                }

                $billedVolume = max(0.0, $billedVolume);
                $subtotal = $billedVolume * $rate;

                if ($billedVolume > 0 || $consumption == 0) {
                    $lineItems[] = [
                        'type' => 'WATER_USAGE',
                        'description' => $tier->tier_name . ' (' . ($max !== null ? "{$min}-{$max} m³" : ">{$min} m³") . ')',
                        'volume' => $billedVolume,
                        'rate' => $rate,
                        'subtotal' => $subtotal,
                    ];
                }

                $totalWaterCharge += $subtotal;
                $remaining -= $billedVolume;
            }
        } else {
            // Default flat fallback if no tiers configured
            $flatRate = 7800.00;
            $subtotal = $consumption * $flatRate;
            $lineItems[] = [
                'type' => 'WATER_USAGE',
                'description' => 'Pemakaian Air Bersih (' . number_format($consumption, 1) . ' m³)',
                'volume' => $consumption,
                'rate' => $flatRate,
                'subtotal' => $subtotal,
            ];
            $totalWaterCharge = $subtotal;
        }

        $adminFee = (float) ($tariff?->admin_fee ?? 2600.00);
        $maintenanceFee = (float) ($tariff?->maintenance_fee ?? 0.00);

        if ($adminFee > 0) {
            $lineItems[] = [
                'type' => 'ADMIN_FEE',
                'description' => 'Biaya Administrasi & Beban Tetap',
                'volume' => 1.00,
                'rate' => $adminFee,
                'subtotal' => $adminFee,
            ];
        }

        if ($maintenanceFee > 0) {
            $lineItems[] = [
                'type' => 'MAINTENANCE_FEE',
                'description' => 'Biaya Pemeliharaan Meter Air',
                'volume' => 1.00,
                'rate' => $maintenanceFee,
                'subtotal' => $maintenanceFee,
            ];
        }

        $totalAmount = $totalWaterCharge + $adminFee + $maintenanceFee;

        return [
            'water_charge' => $totalWaterCharge,
            'admin_fee' => $adminFee,
            'maintenance_fee' => $maintenanceFee,
            'total_amount' => $totalAmount,
            'line_items' => $lineItems,
        ];
    }

    /**
     * Get active/unpaid bill for user.
     */
    public function getUnpaidBillForUser(int $userId): ?Bill
    {
        return Bill::with(['details', 'latestPayment'])
            ->where('user_id', $userId)
            ->whereIn('status', ['UNPAID', 'PENDING_VERIFICATION'])
            ->latest('id')
            ->first();
    }

    /**
     * Get bill history for user.
     */
    public function getBillHistoryForUser(int $userId, int $perPage = 10)
    {
        return Bill::with(['details', 'latestPayment'])
            ->where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->paginate($perPage);
    }
}
