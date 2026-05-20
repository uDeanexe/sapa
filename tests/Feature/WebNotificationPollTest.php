<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\InternalNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebNotificationPollTest extends TestCase
{
    use RefreshDatabase;

    public function test_web_notification_poll_returns_server_side_notifications_after_cursor(): void
    {
        $user = User::factory()->create(['role' => 'kepala']);

        $first = $this->actingAs($user)->getJson(route('web.notifications.poll'));

        $first->assertOk()
            ->assertJsonPath('notifications', []);

        $user->notify(new InternalNotification([
            'title' => 'Chat baru',
            'message' => 'Kamaludin: yfdf',
            'type' => 'chat',
            'route' => 'chat',
        ]));

        $second = $this->actingAs($user)->getJson(route('web.notifications.poll', [
            'since_id' => $first->json('latest_id'),
        ]));

        $second->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('notifications.0.title', 'Chat baru')
            ->assertJsonPath('notifications.0.message', 'Kamaludin: yfdf')
            ->assertJsonPath('notifications.0.type', 'chat');
    }
}
