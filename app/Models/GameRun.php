<?php

namespace App\Models;

use App\Enums\GameLevel;
use App\Enums\GameMode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRun extends Model
{
    // В таблице есть только created_at, поля updated_at нет
    const UPDATED_AT = null;

    protected $fillable = ['user_id', 'mode', 'difficulty', 'score'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Какие партии участвуют в рейтинге: режим + уровень (+ только за последнюю неделю).
    // Партии со счётом 0 в рейтинг не идут
    public function scopeForBoard(Builder $query, GameMode $mode, GameLevel $level, bool $weekOnly): Builder
    {
        return $query
            ->where('game_runs.mode', $mode->value)
            ->where('game_runs.difficulty', $level->value)
            ->where('game_runs.score', '>', 0)
            ->when($weekOnly, fn (Builder $q) => $q->where('game_runs.created_at', '>=', now()->subWeek()));
    }

    // Топ игроков: одна строка на игрока, берётся его лучший счёт
    public static function leaderboard(GameMode $mode, GameLevel $level, bool $weekOnly, int $limit = 10): Collection
    {
        return static::query()
            ->forBoard($mode, $level, $weekOnly)
            ->join('users', 'users.id', '=', 'game_runs.user_id')
            ->selectRaw('users.id as user_id, users.name as name, MAX(game_runs.score) as best')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('best')
            ->orderBy('users.id') // при равном счёте порядок стабильный
            ->limit($limit)
            ->get();
    }

    // Лучший счёт игрока и его место в рейтинге (null, если в этом рейтинге у него нет результатов).
    // Место = 1 + число игроков с лучшим счётом, у равных счёт и место одинаковые
    public static function placeOf(int $userId, GameMode $mode, GameLevel $level, bool $weekOnly): ?array
    {
        $best = static::query()
            ->forBoard($mode, $level, $weekOnly)
            ->where('game_runs.user_id', $userId)
            ->max('game_runs.score');

        if ($best === null) {
            return null;
        }

        $better = static::query()
            ->forBoard($mode, $level, $weekOnly)
            ->select('game_runs.user_id')
            ->groupBy('game_runs.user_id')
            ->havingRaw('MAX(game_runs.score) > ?', [(int) $best])
            ->get()
            ->count();

        return ['best' => (int) $best, 'place' => $better + 1];
    }

    // Сводка игрока: по строке на каждую пару "режим + уровень"
    public static function summaryFor(int $userId): Collection
    {
        return static::query()
            ->where('user_id', $userId)
            ->selectRaw('mode, difficulty, COUNT(*) as games, MAX(score) as best, SUM(score) as total, ROUND(AVG(score), 1) as average')
            ->groupBy('mode', 'difficulty')
            ->orderBy('mode')
            ->orderByRaw("CASE difficulty WHEN 'easy' THEN 1 WHEN 'medium' THEN 2 ELSE 3 END")
            ->get();
    }

    // Последние партии игрока
    public static function recentFor(int $userId, int $limit = 10): Collection
    {
        return static::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }
}
