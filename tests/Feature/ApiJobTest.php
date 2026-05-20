<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_job_accepts_whatsapp_number_and_google_maps_link(): void
    {
        $division = Division::create(['name' => 'Teknisi']);
        $cs = User::factory()->create(['role' => 'kepala']);
        $technician = User::factory()->create([
            'role' => 'karyawan',
            'division_id' => $division->id,
        ]);

        Sanctum::actingAs($cs);

        $response = $this->postJson('/api/jobs', [
            'title' => 'Pasang perangkat',
            'description' => 'Install unit baru',
            'technician_id' => $technician->id,
            'client_name' => 'PT Contoh',
            'whatsapp_number' => '081234567890',
            'location' => 'Jl. Sudirman',
            'google_maps_link' => 'https://maps.app.goo.gl/example',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('job.whatsapp_number', '081234567890')
            ->assertJsonPath('job.whatsapp_url', 'https://wa.me/6281234567890')
            ->assertJsonPath('job.google_maps_link', 'https://maps.app.goo.gl/example')
            ->assertJsonPath('job.maps_url', 'https://maps.app.goo.gl/example');

        $this->assertDatabaseHas('jobs', [
            'title' => 'Pasang perangkat',
            'whatsapp_number' => '081234567890',
            'google_maps_link' => 'https://maps.app.goo.gl/example',
        ]);
    }
}
