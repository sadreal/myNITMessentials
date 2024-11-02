<?php

namespace App\Http\Resources\api\v2\Withdraw;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'date'                  => $this->invoiceDate,
            'payment_method'        => $this->method,
            'payment_method_image'  => $this->paymentMethod->logo ? (string)asset($this->paymentMethod->logo) : '',
            'status'                => $this->status == 1 ? ucwords('paid') : ucwords('pending'),
            'paid_amount'           => (float)$this->amount
        ];
    }
}
