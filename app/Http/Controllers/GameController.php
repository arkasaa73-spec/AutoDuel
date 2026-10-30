<?php

namespace App\Http\Controllers;

use App\Enums\GameMode;
use App\Http\Requests\GameAnswerRequest;
use App\Models\Car;
use App\Models\GameRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GameController extends Controller
{
    // Страница игры: сама партия стартует запросом из JS
    public function show(GameMode $mode): View
    {
        return view('game.show', ['mode' => $mode]);
    }

    // Старт партии: состояние живёт в сессии на сервере, клиент его не меняет
    public function start(GameMode $mode): JsonResponse
    {
        $left = Car::inRandomOrder()->first();
        $right = $left ? $this->pickNext($mode, $left, [$left->id]) : null;

        abort_if(! $left || ! $right, 503, 'В базе недостаточно машин для игры.');

        session()->put($this->key($mode), [
            'score' => 0,
            'left' => $left->id,
            'right' => $right->id,
            'used' => [$left->id, $right->id], // уже показанные в этой партии
        ]);

        return response()->json([
            'score' => 0,
            'left' => $this->publicCar($left, $mode, true),
            'right' => $this->publicCar($right, $mode), // значение скрыто
        ]);
    }

    public function answer(GameAnswerRequest $request, GameMode $mode): JsonResponse
    {
        $state = session($this->key($mode));
        abort_if(! $state, 409, 'Партия не начата.');

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

            // Гость играет без сохранения, у авторизованного партия идёт в рейтинг
            $saved = Auth::check();
            if ($saved) {
                GameRun::create(['user_id' => Auth::id(), 'mode' => $mode->value, 'score' => $score]);
            }

            return response()->json($payload + ['score' => $score, 'game_over' => true, 'saved' => $saved]);
        }

        // Счёт увеличивается только здесь, клиент его не присылает
        $score = $state['score'] + 1;
        $winner = $winnerSide === 'left' ? $left : $right; // победитель остаётся слева
        $used = $state['used'];

        $next = $this->pickNext($mode, $winner, $used);
        if (! $next) {
            // Весь список показан: начинаем новый круг, победитель остаётся
            $used = [$winner->id];
            $next = $this->pickNext($mode, $winner, $used);
        }
        abort_if(! $next, 503, 'В базе недостаточно машин для игры.');

        $used[] = $next->id;
        session()->put($this->key($mode), [
            'score' => $score,
            'left' => $winner->id,
            'right' => $next->id,
            'used' => $used,
        ]);

        return response()->json($payload + [
            'score' => $score,
            'game_over' => false,
            'next' => [
                'left' => $this->publicCar($winner, $mode, true),
                'right' => $this->publicCar($next, $mode),
            ],
        ]);
    }

    // Ключ сессии: у каждого режима своя партия
    private function key(GameMode $mode): string
    {
        return 'game.' . $mode->value;
    }

    // Случайная машина, которой ещё не было в партии и с другим значением (пары с равенством не показываем)
    private function pickNext(GameMode $mode, Car $current, array $used): ?Car
    {
        $column = $mode->column();

        return Car::query()
            ->whereNotIn('id', $used)
            ->where($column, '!=', $current->{$column})
            ->inRandomOrder()
            ->first();
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
