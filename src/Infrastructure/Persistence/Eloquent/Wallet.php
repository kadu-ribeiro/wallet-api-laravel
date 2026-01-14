<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    public $incrementing = false;

    public static $snakeAttributes = false;

    protected $table = 'wallets';

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'balance_cents',
        'currency',
    ];

    protected $casts = [
        'balance_cents' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }
}
