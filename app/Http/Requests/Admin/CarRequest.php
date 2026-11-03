<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

// Одна проверка и для создания, и для редактирования машины
class CarRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Основная защита - middleware маршрута, это запасная проверка
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        // Машина (марка + модель + год) должна быть уникальной; при редактировании саму себя не считаем
        $unique = Rule::unique('cars', 'model')
            ->where(fn ($query) => $query
                ->where('brand', $this->input('brand'))
                ->where('year', $this->input('year')))
            ->ignore($this->route('car'));

        return [
            'brand' => ['required', 'string', 'max:50'],
            'model' => ['required', 'string', 'max:80', $unique],
            'year' => ['required', 'integer', 'between:1950,' . (now()->year + 1)],
            'hp' => ['required', 'integer', 'between:1,3000'],
            'price' => ['required', 'integer', 'between:1000,50000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // до 2 МБ
            'image_credit' => ['nullable', 'string', 'max:255'],
            'remove_image' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'brand' => 'Марка',
            'model' => 'Модель',
            'year' => 'Год',
            'hp' => 'Мощность',
            'price' => 'Цена',
            'image' => 'Фото',
            'image_credit' => 'Подпись к фото',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Поле «:attribute» обязательно.',
            'integer' => 'Поле «:attribute» должно быть целым числом.',
            'between' => 'Поле «:attribute» должно быть в пределах от :min до :max.',
            'max' => 'Значение в поле «:attribute» слишком длинное.',
            'image.max' => 'Фото не должно быть больше 2 МБ.',
            'image' => 'Нужен файл-изображение.',
            'mimes' => 'Фото должно быть в формате jpg, png или webp.',
            'uploaded' => 'Не удалось загрузить фото.',
            'model.unique' => 'Такая машина (марка, модель и год) уже есть в базе.',
        ];
    }
}
