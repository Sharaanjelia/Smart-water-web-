<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bill_number' => $this->bill_number,
            'meter_reading_id' => $this->meter_reading_id,
            'user_id' => $this->user_id,
            'customer_id' => $this->customer_id,
            'meter_number' => $this->meter_number,
            'billing_period' => $this->billing_period,
            'period_label' => $this->period_label,
            'tariff_category' => $this->tariff_category,
            'previous_reading' => (float) $this->previous_reading,
            'current_reading' => (float) $this->current_reading,
            'consumption' => (float) $this->consumption,
            'water_charge' => (float) $this->water_charge,
            'admin_fee' => (float) $this->admin_fee,
            'maintenance_fee' => (float) $this->maintenance_fee,
            'penalty_fee' => (float) $this->penalty_fee,
            'total_amount' => (float) $this->total_amount,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'status' => $this->status,
            'tariff_snapshot' => $this->tariff_snapshot,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'details' => BillDetailResource::collection($this->whenLoaded('details')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'latest_payment' => new PaymentResource($this->whenLoaded('latestPayment')),
        ];
    }
}
