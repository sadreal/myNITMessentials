<?php

namespace App\Repositories\Interfaces;

interface PaymentRepositoryInterface
{
    public function totalEarning(): object;
    public function paymentList(object $request): object;
    public function paymentMethods(): object;
    public function savePayout(object $request):object;
}
