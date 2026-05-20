<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\Presence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPresenceHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_counts_approved_check_in_as_hadir_even_without_checkout(): void
    {
        $division = Division::create(['name' => 'teknisi GPS']);
        $admin = User::factory()->create(['role' => 'kepala']);
        $employee = User::factory()->create([
            'name' => 'asep supriani',
            'role' => 'karyawan',
            'division_id' => $division->id,
        ]);

        Presence::create([
            'user_id' => $employee->id,
            'date' => now()->toDateString(),
            'category' => 'masuk',
            'check_in' => '08:05:00',
            'check_out' => null,
            'photo_in' => 'presence_photos/mobile-checkin.jpg',
            'is_approved' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.presence.history'));

        $response->assertOk()
            ->assertSee('asep supriani')
            ->assertSee('1 presensi')
            ->assertSee('1 hadir')
            ->assertSee('1 belum out')
            ->assertSee('08:05')
            ->assertSee('/storage/presence_photos/mobile-checkin.jpg', false);
    }
}
