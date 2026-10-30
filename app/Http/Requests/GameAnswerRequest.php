<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GameAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // играть может любой, в том числе гость
    }

    public function rules(): array
    {
        return [
            'choice' => ['required', 'in:left,right'],
        ];
    }
}
