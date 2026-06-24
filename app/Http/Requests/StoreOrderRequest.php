<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'status' => ['nullable', 'in:pending,confirmed,cancelled'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_name.required' => 'The customer name is required.',
            'customer_name.string' => 'The customer name must be a string.',
            'customer_name.max' => 'The customer name may not exceed 255 characters.',
            'customer_email.required' => 'The customer email is required.',
            'customer_email.email' => 'The customer email must be a valid email address.',
            'customer_email.max' => 'The customer email may not exceed 255 characters.',
            'status.in' => 'The status must be one of: pending, confirmed, cancelled.',
            'items.required' => 'The order must contain at least one item.',
            'items.array' => 'The items must be an array.',
            'items.min' => 'The order must contain at least one item.',
            'items.*.product_name.required' => 'Each item must have a product name.',
            'items.*.product_name.string' => 'The product name must be a string.',
            'items.*.product_name.max' => 'The product name may not exceed 255 characters.',
            'items.*.quantity.required' => 'Each item must have a quantity.',
            'items.*.quantity.integer' => 'The quantity must be an integer.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
            'items.*.price.required' => 'Each item must have a price.',
            'items.*.price.numeric' => 'The price must be a number.',
            'items.*.price.min' => 'The price must be at least 0.',
        ];
    }
}
