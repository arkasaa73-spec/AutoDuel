<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'brand', 'model', 'year', 'hp', 'price', 'image_path', 'image_credit',
    ];

    // Полное название для карточки: "Toyota Corolla"
    protected function title(): Attribute
    {
        return Attribute::make(get: fn () => $this->brand . ' ' . $this->model);
    }

    // image_path хранится относительно папки public (например images/cars/toyota-corolla-2023.jpg).
    // Если фото ещё нет, показываем заглушку
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->image_path
                ? asset($this->image_path)
                : asset('images/car-placeholder.svg')
        );
    }
}
