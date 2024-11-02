<?php

namespace App\View\Components;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\Component;
use Modules\Payment\Entities\Checkout;
use Modules\Subscription\Entities\SubscriptionCheckout;

class MyPurchasePageSecton extends Component
{

    public function render()
    {
        $enrolls = Checkout::where('user_id', Auth::id())->where('status', 1)->with('coupon', 'courses.course')->latest()->paginate(10);
        if (isModuleActive("Subscription")) {
            $checkouts = SubscriptionCheckout::where('user_id', Auth::id())->whereNotNull('plan_id')->with('plan')->latest()->paginate(10);
        } else {
            $checkouts = [];
        }
        return view(theme('components.my-purchase-page-secton'), compact('enrolls', 'checkouts'));
    }
}
