<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CreditCard = 'credit_card';
    case PayPal = 'paypal';
}
