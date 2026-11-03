<?php

namespace App\Http\Controllers;

use App\Enums\GameLevel;
use App\Enums\GameMode;
use App\Models\GameRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LeaderboardController extends Controller
{
    // Фильтры приходят в query: /leaderboard?mode=hp&level=hard&period=week
    public function index(Request $request): View
    {
        // Неверное значение в адресе просто заменяется значением по умолчанию
        $mode = GameMode::tryFrom($this->queryString($request, 'mode')) ?? GameMode::Price;
        $level = GameLevel::tryFrom($this->queryString($request, 'level')) ?? GameLevel::Medium;
        $period = $this->queryString($request, 'period') === 'week' ? 'week' : 'all';
        $weekOnly = $period === 'week';

        // Места считаем так: у игроков с равным счётом место одинаковое
        $rows = [];
        $prevBest = null;
        $prevRank = 0;
        foreach (GameRun::leaderboard($mode, $level, $weekOnly) as $index => $row) {
            $best = (int) $row->best;
            $rank = $best === $prevBest ? $prevRank : $index + 1;
            $prevBest = $best;
            $prevRank = $rank;

            $rows[] = ['rank' => $rank, 'user_id' => (int) $row->user_id, 'name' => $row->name, 'best' => $best];
        }

        // Своё место показываем, даже если игрок не попал в топ-10
        $me = Auth::check() ? GameRun::placeOf(Auth::id(), $mode, $level, $weekOnly) : null;
        $meInTop = $me !== null && collect($rows)->contains('user_id', Auth::id());

        return view('leaderboard.index', compact('mode', 'level', 'period', 'rows', 'me', 'meInTop'));
    }

    // Параметр запроса как строка (на случай ?mode[]=x, где приходит массив)
    private function queryString(Request $request, string $key): string
    {
        $value = $request->query($key);

        return is_string($value) ? $value : '';
    }
}
