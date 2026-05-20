<?php

namespace Tests\Feature;

use App\Models\DailyChecklist;
use App\Models\Division;
use App\Models\FormTemplate;
use App\Models\Holiday;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAgendaTest extends TestCase
{
    use RefreshDatabase;

    public function test_agenda_endpoint_returns_jobs_checklists_and_holidays_for_mobile(): void
    {
        $division = Division::create(['name' => 'Teknisi']);
        $user = User::factory()->create([
            'role' => 'karyawan',
            'division_id' => $division->id,
        ]);
        $cs = User::factory()->create(['role' => 'kepala']);

        FormTemplate::create([
            'division_id' => $division->id,
            'tipe_form' => 'Pagi',
            'questions' => [['question' => 'Briefing selesai?']],
        ]);

        DailyChecklist::create([
            'user_id' => $user->id,
            'answers' => ['0' => 'Ya'],
            'date' => now()->toDateString(),
            'tipe_form' => 'Pagi',
        ]);

        Holiday::create([
            'holiday_date' => now()->toDateString(),
            'name' => 'Libur Testing',
        ]);

        Job::create([
            'title' => 'Perbaikan perangkat',
            'description' => 'Cek perangkat pelanggan',
            'cs_id' => $cs->id,
            'technician_id' => $user->id,
            'status' => 'pending',
            'start_time' => now()->addHour(),
            'end_time' => now()->addHours(3),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/agenda?scope=today');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('summary.agenda_count', 1)
            ->assertJsonPath('summary.task_count', 2)
            ->assertJsonPath('agendas.0.title', 'Libur Testing')
            ->assertJsonFragment(['title' => 'Perbaikan perangkat'])
            ->assertJsonFragment(['title' => 'Checklist Pagi'])
            ->assertJsonFragment(['status' => 'Selesai']);
    }
}
