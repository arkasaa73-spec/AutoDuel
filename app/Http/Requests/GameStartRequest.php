<?php

namespace App\Http\Requests;

use App\Enums\GameLevel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GameStartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'level' => ['required', Rule::enum(GameLevel::class)],
        ];
    }
}
