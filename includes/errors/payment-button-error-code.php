<?php

namespace Razorpay\SubscriptionButtonElementor\Errors;

use Razorpay\Api\Errors as ApiErrors;

class Subscription_Button_Elementor_Error_Code extends ApiErrors\ErrorCode
{
    // Razorpay Payment Button
    const API_SUBSCRIPTION_BUTTON_FETCH_FAILED     = 'Razorpay subscription button fetch request failed';
    const API_SUBSCRIPTION_BUTTON_ACTION_FAILED     = 'Razorpay API subscription button activate/deactivate failed';
}
