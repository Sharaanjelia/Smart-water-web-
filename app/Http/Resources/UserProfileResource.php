<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roleName = 'customer';
        if (method_exists($this->resource, 'hasRole')) {
            if ($this->resource->hasRole('admin')) {
                $roleName = 'admin';
            } elseif ($this->resource->hasRole('staff')) {
                $roleName = 'staff';
            }
        } elseif (method_exists($this->resource, 'primaryRoleName')) {
            $roleName = $this->resource->primaryRoleName();
        } elseif (!empty($this->role)) {
            $roleName = $this->role;
        }

        $isAdmin = in_array($roleName, ['admin', 'staff'], true);

        $customerProfile = $this->relationLoaded('customerProfile')
            ? $this->customerProfile
            : $this->customerProfile()->first();

        $staffProfile = $this->relationLoaded('staffProfile')
            ? $this->staffProfile
            : $this->staffProfile()->first();

        $phone = $customerProfile?->phone ?? $staffProfile?->phone_dinas ?? '';

        return [
            'id'                => (string)$this->id,
            'user_id'           => $this->id,
            'name'              => $this->name,
            'fullName'          => $this->name,
            'email'             => $this->email,
            'phone'             => $phone,
            'phoneNumber'       => $phone,
            'role'              => $roleName,
            'isAdmin'           => $isAdmin,
            'customerId'        => $customerProfile?->customer_id ?? ($isAdmin ? 'ADMIN-04' : (string)$this->id),
            'meterNumber'       => $customerProfile?->meter_number ?? ($isAdmin ? 'ADM-04-SCADA' : '01000'),
            'tariffCategory'    => $customerProfile?->tariff_category ?? ($isAdmin ? 'Petugas Teknis & Operator SCADA' : '2A - Rumah Tangga Mandiri'),
            'address'           => $customerProfile?->address ?? ($isAdmin ? 'Kantor Pusat PDAM Tirta Sago, Jl. Imam Bonjol No. 14, Kota Payakumbuh' : 'Jl. Tan Malaka No. 42, Kota Payakumbuh'),
            'zone'              => $customerProfile?->zone ?? ($isAdmin ? 'Wilayah Payakumbuh Kota & SCADA Central' : 'DIST-A Sektor Utara'),
            'district'          => $customerProfile?->district ?? 'Payakumbuh Barat',
            'lastMeterValue'    => (string)($customerProfile?->last_meter_value ?? '00981'),
            'currentMeterValue' => (string)($customerProfile?->current_meter_value ?? '01000'),
            'walletBalance'     => (float)($customerProfile?->wallet_balance ?? 0),
            'points'            => (int)($customerProfile?->points ?? 0),
            'staffId'           => $staffProfile?->nip ?? ($isAdmin ? 'NIP-198804-004' : null),
            'nip'               => $staffProfile?->nip ?? null,
            'department'        => $staffProfile?->department ?? null,
            'position'          => $staffProfile?->position ?? null,
        ];
    }
}
