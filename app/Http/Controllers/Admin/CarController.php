<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CarRequest;
use App\Models\Car;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CarController extends Controller
{
    // Какие поля формы попадают в базу (фото обрабатывается отдельно)
    private const FIELDS = ['brand', 'model', 'year', 'hp', 'price', 'image_credit'];

    // Список с поиском по марке и модели и пагинацией
    public function index(Request $request): View
    {
        $search = $request->query('q');
        $search = is_string($search) ? Str::limit(trim($search), 50, '') : '';

        $cars = Car::query()
            ->when($search !== '', fn ($query) => $query->where(
                fn ($w) => $w->where('brand', 'like', "%{$search}%")->orWhere('model', 'like', "%{$search}%")
            ))
            ->orderBy('brand')
            ->orderBy('model')
            ->paginate(15)
            ->withQueryString();

        return view('admin.cars.index', compact('cars', 'search'));
    }

    public function create(): View
    {
        return view('admin.cars.create', ['car' => new Car()]);
    }

    public function store(CarRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(self::FIELDS);

        if ($request->hasFile('image')) {
            $data['image_path'] = $this->storeImage($request->file('image'), $data);
        }

        Car::create($data);

        return redirect()->route('admin.cars.index')->with('status', 'Машина добавлена.');
    }

    public function edit(Car $car): View
    {
        return view('admin.cars.edit', compact('car'));
    }

    public function update(CarRequest $request, Car $car): RedirectResponse
    {
        $data = $request->safe()->only(self::FIELDS);

        if ($request->hasFile('image')) {
            // Новое фото заменяет старое, старый файл удаляем
            $this->deleteImage($car->image_path);
            $data['image_path'] = $this->storeImage($request->file('image'), $data);
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($car->image_path);
            $data['image_path'] = null;
            $data['image_credit'] = null;
        }

        $car->update($data);

        return redirect()->route('admin.cars.index')->with('status', 'Изменения сохранены.');
    }

    public function destroy(Car $car): RedirectResponse
    {
        $this->deleteImage($car->image_path);
        $car->delete();

        return redirect()->route('admin.cars.index')->with('status', 'Машина удалена.');
    }

    // Сохраняет загруженное фото в public/images/cars и возвращает путь для базы
    private function storeImage(UploadedFile $file, array $data): string
    {
        File::ensureDirectoryExists(public_path('images/cars'));

        // Расширение берём по реальному типу файла, а не из имени, которое прислал браузер
        $name = Str::slug($data['brand'] . ' ' . $data['model'] . ' ' . $data['year'])
            . '-' . Str::lower(Str::random(6))
            . '.' . $file->extension();

        $file->move(public_path('images/cars'), $name);

        return 'images/cars/' . $name;
    }

    // Удаляем только файлы из папки с фото машин
    private function deleteImage(?string $path): void
    {
        if ($path && str_starts_with($path, 'images/cars/') && ! str_contains($path, '..')) {
            File::delete(public_path($path));
        }
    }
}
