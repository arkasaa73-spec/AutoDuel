<?php

namespace App\Http\Controllers;

use App\Enums\GameLevel;
use App\Enums\GameMode;
use App\Http\Requests\GameAnswerRequest;
use App\Http\Requests\GameStartRequest;
use App\Models\Car;
use App\Models\GameRun;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameController extends Controller
{
    // Страница игры: уровень выбирается на ней, партия стартует запросом из JS
    public function show(GameMode $mode): View
    {
        return view('game.show', ['mode' => $mode]);
    }

    // Старт партии: состояние живёт в сессии на сервере, клиент его не меняет
    public function start(GameStartRequest $request, GameMode $mode): JsonResponse
    {
        $level = GameLevel::from($request->validated('level'));

        // Первая машина обязана иметь пару подходящей сложности.
        // Так "одинокие" машины вроде Bugatti по цене не выпадают совсем
        $left = $right = null;
        foreach (Car::inRandomOrder()->get() as $candidate) {
            $partner = $this->candidates($mode, $candidate, $level->minRatio(), $level->maxRatio())
                ->inRandomOrder()
                ->first();

            if ($partner) {
                $left = $candidate;
                $right = $partner;
                break;
            }
        }

        abort_if(! $left, 503, 'В базе недостаточно машин для игры.');

        session()->put($this->key($mode), [
            'level' => $level->value,
            'score' => 0,
            'left' => $left->id,
            'right' => $right->id,
            'used' => [$left->id, $right->id], // уже показанные в этой партии
        ]);

        return response()->json([
            'score' => 0,
            'level' => $level->value,
            'left' => $this->publicCar($left, $mode, true),
            'right' => $this->publicCar($right, $mode), // значение скрыто
        ]);
    }

    public function answer(GameAnswerRequest $request, GameMode $mode): JsonResponse
    {
        $state = session($this->key($mode));
        abort_if(! $state, 409, 'Партия не начата.');

        $level = GameLevel::tryFrom($state['level'] ?? '') ?? GameLevel::Medium;
        $left = Car::find($state['left']);
        $right = Car::find($state['right']);

        // Машину могли удалить из админки посреди партии
        if (! $left || ! $right) {
            session()->forget($this->key($mode));
            abort(409, 'Партия устарела.');
        }

        $column = $mode->column();
        $leftValue = (int) $left->{$column};
        $rightValue = (int) $right->{$column};
        $choice = $request->validated('choice');

        // Какая карточка победила. При равенстве (данные поменяли в админке) засчитываем выбор игрока
        $winnerSide = $leftValue === $rightValue ? $choice : ($leftValue > $rightValue ? 'left' : 'right');
        $correct = $winnerSide === $choice;

        $payload = [
            'correct' => $correct,
            'winner' => $winnerSide,
            // Значения раскрываем только теперь, когда ответ уже принят
            'left' => ['display' => $mode->format($leftValue)],
            'right' => ['display' => $mode->format($rightValue)],
        ];

        if (! $correct) {
            $score = $state['score'];
            session()->forget($this->key($mode));

            return response()->json($payload + [
                'score' => $score,
                'game_over' => true,
                'saved' => $this->saveRun($mode, $level, $score),
            ]);
        }

        // Счёт увеличивается только здесь, клиент его не присылает
        $score = $state['score'] + 1;
        $winner = $winnerSide === 'left' ? $left : $right; // победитель остаётся слева

        $pair = $this->nextPair($mode, $level, $winner, $state['used']);

        // Подходящие машины закончились: партия завершается победой, счёт = число верных ответов
        if (! $pair) {
            session()->forget($this->key($mode));

            return response()->json($payload + [
                'score' => $score,
                'game_over' => true,
                'completed' => true,
                'saved' => $this->saveRun($mode, $level, $score),
            ]);
        }

        [$nextLeft, $nextRight, $used] = $pair;

        session()->put($this->key($mode), [
            'level' => $level->value,
            'score' => $score,
            'left' => $nextLeft->id,
            'right' => $nextRight->id,
            'used' => $used,
        ]);

        return response()->json($payload + [
            'score' => $score,
            'game_over' => false,
            'next' => [
                'left' => $this->publicCar($nextLeft, $mode, true),
                'right' => $this->publicCar($nextRight, $mode),
            ],
        ]);
    }

    // Гость играет без сохранения, у авторизованного партия идёт в статистику и рейтинг
    private function saveRun(GameMode $mode, GameLevel $level, int $score): bool
    {
        if (! Auth::check()) {
            return false;
        }

        GameRun::create([
            'user_id' => Auth::id(),
            'mode' => $mode->value,
            'difficulty' => $level->value,
            'score' => $score,
        ]);

        return true;
    }

    // Ключ сессии: у каждого режима своя партия
    private function key(GameMode $mode): string
    {
        return 'game.' . $mode->value;
    }

    // Машины, значение которых отличается от значения $current в $minRatio...$maxRatio раз (в любую сторону).
    // $maxRatio = null снимает верхнюю границу
    private function candidates(GameMode $mode, Car $current, float $minRatio, ?float $maxRatio): Builder
    {
        $column = $mode->column();
        $value = (int) $current->{$column};

        return Car::query()
            ->where('id', '!=', $current->id)
            ->where(function (Builder $query) use ($column, $value, $minRatio, $maxRatio) {
                // Вариант 1: значение выше текущего
                $query->where(function (Builder $up) use ($column, $value, $minRatio, $maxRatio) {
                    $up->where($column, '>=', (int) ceil($value * $minRatio));
                    if ($maxRatio !== null) {
                        $up->where($column, '<=', (int) floor($value * $maxRatio));
                    }
                })
                // Вариант 2: значение ниже текущего
                ->orWhere(function (Builder $down) use ($column, $value, $minRatio, $maxRatio) {
                    $down->where($column, '<=', (int) floor($value / $minRatio));
                    if ($maxRatio !== null) {
                        $down->where($column, '>=', (int) ceil($value / $maxRatio));
                    }
                });
            });
    }

    // Следующая пара после верного ответа. Каждая машина показывается за партию не больше одного раза.
    // Возвращает [левая машина, правая машина, обновлённый список показанных] или null, если машины закончились
    private function nextPair(GameMode $mode, GameLevel $level, Car $winner, array $used): ?array
    {
        $min = $level->minRatio();
        $max = $level->maxRatio();

        // 1) Победитель остаётся, ему подбираем ещё не показанного соперника подходящей сложности
        $next = $this->candidates($mode, $winner, $min, $max)
            ->whereNotIn('id', $used)
            ->inRandomOrder()
            ->first();

        if ($next) {
            return [$winner, $next, [...$used, $next->id]];
        }

        // 2) У победителя подходящие соперники кончились: берём новую пару из ещё не показанных машин
        foreach (Car::query()->whereNotIn('id', $used)->inRandomOrder()->get() as $left) {
            $partner = $this->candidates($mode, $left, $min, $max)
                ->whereNotIn('id', [...$used, $left->id])
                ->inRandomOrder()
                ->first();

            if ($partner) {
                return [$left, $partner, [...$used, $left->id, $partner->id]];
            }
        }

        // 3) Пар подходящей сложности не осталось: машины закончились
        return null;
    }

    // Данные машины для клиента. Скрытое значение отдаём только если withValue = true
    private function publicCar(Car $car, GameMode $mode, bool $withValue = false): array
    {
        return [
            'id' => $car->id,
            'title' => $car->title,
            'brand' => $car->brand,
            'model' => $car->model,
            'year' => $car->year,
            'image' => $car->image_url,
            'credit' => $car->image_credit,
            'display' => $withValue ? $mode->format((int) $car->{$mode->column()}) : null,
        ];
    }
}
