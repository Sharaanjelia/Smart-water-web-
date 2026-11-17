<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'user_id'            => $this->user_id,
            'customer_id'        => $this->customer_id,
            'meter_number'       => $this->meter_number,
            'tariff_category'    => $this->tariff_category,
            'phone'              => $this->phone,
            'address'            => $this->address,
            'district'           => $this->district,
            'zone'               => $this->zone,
            'last_meter_value'   => (float)$this->last_meter_value,
            'current_meter_value'=> (float)$this->current_meter_value,
            'wallet_balance'     => (float)$this->wallet_balance,
            'points'             => (int)$this->points,
        ];
    }
}
