<?php

namespace Database\Seeders;

use App\Models\Car;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        // [brand, model, year, hp, price USD] - значения приблизительные, базовая комплектация.
        // Значения hp и price у всех машин разные, чтобы пар с равенством почти не было.
        $cars = [
            ['Hyundai', 'Elantra', 2023, 147, 20950],
            ['Toyota', 'Corolla', 2023, 169, 21550],
            ['Honda', 'Civic', 2023, 158, 23950],
            ['Mazda', 'Mazda3', 2023, 191, 23500],
            ['Kia', 'K5', 2023, 180, 24990],
            ['Toyota', 'Camry', 2023, 203, 26420],
            ['Mazda', 'CX-5', 2023, 187, 26700],
            ['Honda', 'Accord', 2023, 192, 27295],
            ['Mazda', 'MX-5 Miata', 2023, 181, 28050],
            ['Subaru', 'BRZ', 2023, 228, 28995],
            ['Toyota', 'RAV4 Hybrid', 2023, 219, 30525],
            ['Volkswagen', 'Golf GTI', 2023, 241, 31090],
            ['Jeep', 'Wrangler', 2023, 285, 32295],
            ['Kia', 'Telluride', 2023, 291, 33190],
            ['Ford', 'F-150', 2023, 290, 34585],
            ['Tesla', 'Model 3', 2023, 283, 40240],
            ['Audi', 'A4', 2023, 261, 41300],
            ['Nissan', 'Z', 2023, 400, 41015],
            ['Ford', 'Mustang GT', 2023, 480, 42000],
            ['Chevrolet', 'Camaro SS', 2023, 455, 42495],
            ['BMW', '330i', 2023, 255, 43800],
            ['Lexus', 'RX 350', 2023, 275, 47900],
            ['Toyota', 'GR Supra 3.0', 2023, 382, 52500],
            ['Land Rover', 'Defender 110', 2023, 296, 55800],
            ['Chevrolet', 'Corvette Stingray', 2023, 490, 62195],
            ['Dodge', 'Challenger SRT Hellcat', 2023, 717, 62440],
            ['BMW', 'M3', 2023, 473, 74700],
            ['Porsche', 'Cayenne', 2023, 348, 76050],
            ['Alfa Romeo', 'Giulia Quadrifoglio', 2023, 505, 80195],
            ['Porsche', '911 Carrera', 2023, 379, 106100],
            ['BMW', 'M5', 2023, 600, 106000],
            ['Tesla', 'Model S Plaid', 2023, 1020, 108490],
            ['Nissan', 'GT-R', 2023, 565, 113540],
            ['Audi', 'RS6 Avant', 2023, 591, 118500],
            ['Audi', 'R8 V10', 2023, 562, 158600],
            ['Mercedes-AMG', 'GT 63 S', 2023, 630, 167500],
            ['Porsche', '911 GT3', 2023, 502, 169700],
            ['Honda', 'NSX Type S', 2022, 573, 171000],
            ['Mercedes-Benz', 'G 63 AMG', 2023, 577, 179000],
            ['Porsche', 'Taycan Turbo S', 2023, 750, 187610],
            ['Maserati', 'MC20', 2023, 621, 213000],
            ['Ferrari', 'Roma', 2023, 612, 222620],
            ['Lamborghini', 'Urus S', 2023, 657, 233000],
            ['Aston Martin', 'DB12', 2024, 671, 245000],
            ['Lamborghini', 'Huracan EVO', 2023, 631, 261274],
            ['McLaren', '720S', 2022, 710, 299000],
            ['Ferrari', '296 GTB', 2023, 819, 322986],
            ['Rolls-Royce', 'Ghost', 2023, 563, 343000],
            ['Bugatti', 'Chiron', 2022, 1479, 3000000],
        ];

        foreach ($cars as [$brand, $model, $year, $hp, $price]) {
            // updateOrCreate: повторный запуск seeder не создаёт дубликаты
            Car::updateOrCreate(
                ['brand' => $brand, 'model' => $model, 'year' => $year],
                ['hp' => $hp, 'price' => $price]
            );
        }
    }
}
