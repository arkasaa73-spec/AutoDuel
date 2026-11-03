<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GameLevel;
use App\Enums\GameMode;
use App\Http\Controllers\Controller;
use App\Models\GameRun;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RunController extends Controller
{
    // Все партии с фильтрами: режим, уровень, игрок; сортировка по дате или по счёту
    public function index(Request $request): View
    {
        $mode = GameMode::tryFrom($this->queryString($request, 'mode'));
        $level = GameLevel::tryFrom($this->queryString($request, 'level'));
        $search = Str::limit(trim($this->queryString($request, 'q')), 50, '');
        $sort = $this->queryString($request, 'sort') === 'score' ? 'score' : 'date';

        $runs = GameRun::query()
            ->with('user')
            ->when($mode, fn ($query) => $query->where('mode', $mode->value))
            ->when($level, fn ($query) => $query->where('difficulty', $level->value))
            ->when($search !== '', fn ($query) => $query->whereHas(
                'user',
                fn ($user) => $user->where(
                    fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%")
                )
            ))
            ->when(
                $sort === 'score',
                fn ($query) => $query->orderByDesc('score')->orderByDesc('created_at'),
                fn ($query) => $query->orderByDesc('created_at')->orderByDesc('id')
            )
            ->paginate(20)
            ->withQueryString();

        return view('admin.runs.index', compact('runs', 'mode', 'level', 'search', 'sort'));
    }

    // Удаление подозрительного результата
    public function destroy(GameRun $run): RedirectResponse
    {
        $run->delete();

        return back()->with('status', 'Результат удалён.');
    }

    // Параметр запроса как строка (на случай ?mode[]=x, где приходит массив)
    private function queryString(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }
}
