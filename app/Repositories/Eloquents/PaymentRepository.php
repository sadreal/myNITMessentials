<?php

namespace App\Repositories\Eloquents;

use App\Http\Resources\api\v2\Withdraw\WithdrawAccountDetailsResource;
use App\Http\Resources\api\v2\Withdraw\WithdrawListResource;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use App\User;
use Modules\Payment\Entities\InstructorPayout;
use Modules\Payment\Entities\InstructorTotalPayout;
use Modules\Payment\Entities\Withdraw;
use Modules\PaymentMethodSetting\Entities\PaymentMethod;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function totalEarning(): object
    {
        $totalEarning   = InstructorTotalPayout::where('instructor_id', auth()->id())->sum('amount');
        $nextPayout     = InstructorPayout::where('instructor_id', auth()->id())->where('status', 0)->sum('reveune');

        $data['total_earning']  = (float)$totalEarning;
        $data['next_payout']    = (float)$nextPayout;
        $data['payout_account'] = new WithdrawAccountDetailsResource(auth()->user());

        $response = [
            'success'   => true,
            'data'      => $data,
            'message'   => trans('api.Operation successful')
        ];
        return response()->json($response);
    }

    public function paymentList(object $request): object
    {
        $payoutList = Withdraw::where('instructor_id', auth()->id())
            ->when($search = $request->search, function ($query) use ($search) {
                $query->whereLike('amount', $search);
            })->when($request->sort_by_date, function ($query) {
                $query->latest('created_at');
            })->paginate($request->per_page ?? 10);

        $response = [
            'success'   => true,
            'data'      => WithdrawListResource::collection($payoutList),
            'message'   => trans('api.Getting payment history successfully')
        ];

        return response()->json($response);
    }

    public function paymentMethods(): object
    {
        $data = PaymentMethod::where('module_status', 1)->get()->map(function ($method) {
            return [
                'id' => (int)$method->id,
                'name' => (string)$method->method,
                'image' => $method->logo ? (string)asset($method->logo) : '',
            ];
        });

        if (empty($data)) {
            $response = [
                'success'   => false,
                'message'   => trans('api.Payment method list not found')
            ];
            $status = 404;
        } else {
            $response = [
                'success'   => true,
                'data'      => $data,
                'message'   => trans('api.Getting payment method list successfully'),
            ];
        }

        return response()->json($response, $status ?? 200);
    }

    public function savePayout(object $request): object
    {
        if (demoCheck()) {
            return response()->json(['success' => false, 'message' => 'Your are not allowed for this action']);
        }

        $method = PaymentMethod::where('method', $request->payment_method)->first();

        $request->merge([
            'payout' => $request->payment_method,
            'payout_icon' => $method->logo
        ]);
        if ($request->payout == "Bank Payment") {
            $rules = [
                'bank_name' => 'required',
                'branch_name' => 'required',
                'bank_account_number' => 'required',
                'account_holder_name' => 'required',
                'bank_type' => 'required',
            ];

            $request->validate($rules, validationMessage($rules));
        } elseif ($request->payout == "Bkash") {
            $rules = [
                'payout_number' => 'required',
            ];

            $request->validate($rules, validationMessage($rules));
        } else {
            $rules = ['payout_email' => 'required|email'];
            $request->validate($rules, validationMessage($rules));
        }

        $user = User::find(auth()->id());
        $user->payout = $request->payout;
        if ($request->payout == "Bank Payment") {
            $user->bank_name = $request->bank_name;
            $user->branch_name = $request->branch_name;
            $user->bank_account_number = $request->bank_account_number;
            $user->account_holder_name = $request->account_holder_name;
            $user->bank_type = $request->bank_type;
            $user->payout_icon = '';
            $user->payout_email = '';
            if (isModuleActive('Bkash')) {
                $user->bkash_number = '';
            }
        } elseif ($request->payout == "Bkash") {
            $user->bank_name = '';
            $user->branch_name = '';
            $user->bank_account_number = '';
            $user->account_holder_name = '';
            $user->bank_type = '';
            if (isModuleActive('Bkash')) {
                $user->bkash_number = $request->payout_number;
            }
            $user->payout_icon = $request->payout_icon;
            $user->payout_email = '';
        } else {
            $user->bank_name = '';
            $user->branch_name = '';
            $user->bank_account_number = '';
            $user->account_holder_name = '';
            $user->bank_type = '';
            if (isModuleActive('Bkash')) {
                $user->bkash_number = '';
            }
            $user->payout_icon = $request->payout_icon;
            $user->payout_email = $request->payout_email;
        }
        $user->save();

        $response = [
            'success'   => true,
            'message'   => trans('api.Payout account added successfully'),
        ];

        return response()->json($response);
    }
}
