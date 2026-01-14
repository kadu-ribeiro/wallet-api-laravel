<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class DepositRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be greater than zero',
        ];
    }

    public function walletId(): string
    {
        return $this->route('walletId');
    }

    public function idempotencyKey(): string
    {
        return $this->header('Idempotency-Key', '');
    }

    public function amount(): string
    {
        return (string) $this->input('amount');
    }

    public function metadata(): array
    {
        return $this->input('metadata', []);
    }
}
