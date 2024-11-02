<?php

namespace App\Http\Controllers\Api\V2\Payment;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Interfaces\PaymentRepositoryInterface;

class PaymentController extends Controller
{
    private $paymentRepository;
    public function __construct(PaymentRepositoryInterface $paymentRepository)
    {
        $this->paymentRepository = $paymentRepository;
    }
    public function totalEarning(): object
    {
        return $this->paymentRepository->totalEarning();
    }
    public function paymentList(Request $request): object
    {
        return $this->paymentRepository->paymentList($request);
    }
    public function paymentMethods(): object
    {
        return $this->paymentRepository->paymentMethods();
    }
    public function savePayout(Request $request): object
    {
        return $this->paymentRepository->savePayout($request);
    }
}
