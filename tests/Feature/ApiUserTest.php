<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_endpoint_always_returns_json_for_mobile(): void
    {
        $division = Division::create(['name' => 'Teknisi']);
        $authUser = User::factory()->create(['name' => 'Z Admin API', 'role' => 'kepala']);
        User::factory()->create([
            'name' => 'Budi Teknisi',
            'email' => 'budi@example.test',
            'role' => 'karyawan',
            'division_id' => $division->id,
        ]);

        Sanctum::actingAs($authUser);

        $response = $this->get('/api/users');

        $response->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertJsonFragment([
                'name' => 'Budi Teknisi',
                'email' => 'budi@example.test',
                'role' => 'karyawan',
            ])
            ->assertJsonPath('0.division.name', 'Teknisi');
    }
}
