<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethod;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ProcessPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The allowed payment methods are derived dynamically from the PaymentMethod enum,
     * so adding a new enum case automatically registers it as a valid option.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $allowedMethods = implode(',', array_map(
            fn (PaymentMethod $m) => $m->value,
            PaymentMethod::cases()
        ));

        return [
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'payment_method' => ['required', 'in:' . $allowedMethods],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $allowedMethods = implode(', ', array_map(
            fn (PaymentMethod $m) => $m->value,
            PaymentMethod::cases()
        ));

        return [
            'order_id.required' => 'The order ID is required.',
            'order_id.integer' => 'The order ID must be an integer.',
            'order_id.exists' => 'The specified order does not exist.',
            'payment_method.required' => 'The payment method is required.',
            'payment_method.in' => "The payment method must be one of: {$allowedMethods}.",
        ];
    }
}
