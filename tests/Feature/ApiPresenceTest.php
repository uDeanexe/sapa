<?php

namespace Tests\Feature;

use App\Models\OfficeSetting;
use App\Models\Presence;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiPresenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_today_status_detects_existing_check_in_even_when_category_is_blank(): void
    {
        $user = User::factory()->create(['role' => 'karyawan']);
        Presence::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'category' => '',
            'check_in' => '08:05:00',
            'is_approved' => 'approved',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/presence/today-status');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('has_checkin', true)
            ->assertJsonPath('check_in', '08:05:00')
            ->assertJsonPath('auto_approved', true);
    }

    public function test_mobile_check_in_is_stored_for_web_history(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-05-04 08:05:00', config('app.timezone')));
        Storage::fake('public');

        OfficeSetting::create([
            'name' => 'Kantor Pusat',
            'latitude' => -6.2,
            'longitude' => 106.8166,
            'radius' => 100,
            'check_in_time' => '08:00:00',
            'check_out_time' => '17:00:00',
            'late_tolerance' => 15,
            'radius_enforced' => true,
        ]);

        $user = User::factory()->create(['role' => 'karyawan']);
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/presence/check-in', [
            'latitude' => -6.2,
            'longitude' => 106.8166,
            'notes' => 'Absen Masuk Mobile',
            'photo' => UploadedFile::fake()->image('check-in.jpg'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('status', 'approved');

        $presence = Presence::first();

        $this->assertNotNull($presence);
        $this->assertSame($user->id, $presence->user_id);
        $this->assertSame('2026-05-04', $presence->date->toDateString());
        $this->assertSame('08:05:00', $presence->check_in);
        $this->assertSame('approved', $presence->is_approved);
        $this->assertStringStartsWith('presence_photos/', $presence->photo_in);
        $this->assertStringStartsWith(url('/storage/presence_photos/'), $presence->photo_in_url);
        Storage::disk('public')->assertExists($presence->photo_in);

        Carbon::setTestNow();
    }
}
