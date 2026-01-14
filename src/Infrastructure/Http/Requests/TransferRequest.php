<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Requests;

use App\Domain\User\Services\AuthContextInterface;
use Illuminate\Foundation\Http\FormRequest;

final class TransferRequest extends FormRequest
{
    public function __construct(
        private readonly AuthContextInterface $authProvider
    ) {
        parent::__construct();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'recipient_email' => ['required', 'email'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'metadata' => ['sometimes', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient_email.required' => 'Recipient email is required',
            'recipient_email.email' => 'Recipient email must be valid',
            'amount.required' => 'Amount is required',
            'amount.numeric' => 'Amount must be a number',
            'amount.min' => 'Amount must be greater than zero',
        ];
    }

    public function idempotencyKey(): string
    {
        return $this->header('Idempotency-Key', '');
    }

    public function recipientEmail(): string
    {
        return (string) $this->input('recipient_email');
    }

    public function amount(): string
    {
        return (string) $this->input('amount');
    }

    public function metadata(): array
    {
        return $this->input('metadata', []);
    }

    public function senderEmail(): string
    {
        return $this->authProvider->getEmail()->value;
    }

    public function senderWalletId(): string
    {
        return $this->authProvider->getWalletId()->value;
    }
}
