<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_runs', function (Blueprint $table) {
            $table->id();
            // При удалении пользователя его партии удаляются вместе с ним
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mode', 10); // price / hp
            $table->unsignedInteger('score');
            $table->timestamp('created_at')->useCurrent();

            // Индексы под рейтинг (лучший счёт по режиму) и под историю игрока
            $table->index(['mode', 'score']);
            $table->index(['user_id', 'mode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_runs');
    }
};
