<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRun extends Model
{
    // В таблице есть только created_at, поля updated_at нет
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'mode', 'score'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
