<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WithdrawalRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|string|in:paypal,bank_transfer',
            'payment_details' => 'required|string|max:255'
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'Please enter a withdrawal amount',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Minimum withdrawal amount is 1',
            'payment_method.required' => 'Please select a payment method',
            'payment_method.in' => 'Invalid payment method selected',
            'payment_details.required' => 'Please provide payment details',
            'payment_details.max' => 'Payment details are too long'
        ];
    }
}