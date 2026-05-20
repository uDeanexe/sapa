<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\InternalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_notifications_endpoint_returns_mobile_friendly_payload(): void
    {
        $user = User::factory()->create();
        $user->notify(new InternalNotification([
            'title' => 'Tugas Baru',
            'message' => 'Anda mendapatkan tugas baru.',
            'type' => 'job_assigned',
            'route' => 'job_detail',
            'route_id' => '12',
        ]));

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonPath('0.title', 'Tugas Baru')
            ->assertJsonPath('0.message', 'Anda mendapatkan tugas baru.')
            ->assertJsonPath('0.type', 'job_assigned')
            ->assertJsonPath('0.route', 'job_detail')
            ->assertJsonPath('0.route_id', '12')
            ->assertJsonPath('0.is_read', false)
            ->assertJsonStructure([
                '*' => [
                    'id',
                    'title',
                    'message',
                    'type',
                    'category',
                    'label',
                    'icon',
                    'color',
                    'route',
                    'route_id',
                    'is_read',
                    'created_at',
                    'created_at_human',
                ],
            ]);
    }
}
