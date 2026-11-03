<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_runs', function (Blueprint $table) {
            // Старые партии (если есть) получат уровень medium
            $table->string('difficulty', 10)->default('medium')->after('mode');

            // Индекс под будущий рейтинг: лучший счёт по режиму и уровню
            $table->index(['mode', 'difficulty', 'score']);
        });
    }

    public function down(): void
    {
        Schema::table('game_runs', function (Blueprint $table) {
            $table->dropIndex(['mode', 'difficulty', 'score']);
            $table->dropColumn('difficulty');
        });
    }
};
