<?php

namespace Tests\Feature;

use App\Models\GameRun;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    // role не в $fillable, поэтому ставим её через forceFill
    private function makeUser(string $role = 'user'): User
    {
        $user = User::factory()->create();
        $user->forceFill(['role' => $role])->save();

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/cars')->assertRedirect('/login');
    }

    public function test_regular_user_cannot_open_admin(): void
    {
        $this->actingAs($this->makeUser())->get('/admin/cars')->assertForbidden();
    }

    public function test_admin_can_open_car_list(): void
    {
        $this->actingAs($this->makeUser('admin'))->get('/admin/cars')->assertOk();
    }

    public function test_admin_can_create_car(): void
    {
        $response = $this->actingAs($this->makeUser('admin'))->post('/admin/cars', [
            'brand' => 'Test',
            'model' => 'Alpha',
            'year' => 2023,
            'hp' => 200,
            'price' => 30000,
        ]);

        $response->assertRedirect(route('admin.cars.index'));
        $this->assertDatabaseHas('cars', ['brand' => 'Test', 'model' => 'Alpha']);
    }

    public function test_car_form_is_validated(): void
    {
        $response = $this->actingAs($this->makeUser('admin'))->post('/admin/cars', [
            'brand' => '',
            'model' => 'Alpha',
            'year' => 2023,
            'hp' => 'abc',
            'price' => 30000,
        ]);

        $response->assertSessionHasErrors(['brand', 'hp']);
    }

    public function test_admin_can_delete_run(): void
    {
        $player = $this->makeUser();
        $run = GameRun::create(['user_id' => $player->id, 'mode' => 'price', 'difficulty' => 'medium', 'score' => 99]);

        $this->actingAs($this->makeUser('admin'))->delete(route('admin.runs.destroy', $run))->assertRedirect();

        $this->assertDatabaseMissing('game_runs', ['id' => $run->id]);
    }

    public function test_regular_user_cannot_delete_run(): void
    {
        $player = $this->makeUser();
        $run = GameRun::create(['user_id' => $player->id, 'mode' => 'price', 'difficulty' => 'medium', 'score' => 5]);

        $this->actingAs($player)->delete(route('admin.runs.destroy', $run))->assertForbidden();

        $this->assertDatabaseHas('game_runs', ['id' => $run->id]);
    }
}
