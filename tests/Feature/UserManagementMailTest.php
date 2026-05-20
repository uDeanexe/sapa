<?php

namespace Tests\Feature;

use App\Mail\UserCreatedMail;
use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class UserManagementMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_is_sent_to_new_employee_after_user_is_added(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['role' => 'kepala']);
        $division = Division::create(['name' => 'Teknisi']);

        $response = $this->actingAs($admin)->post(route('users-management.store'), [
            'name' => 'Tatang',
            'email' => 'tatang@gmail.com',
            'division_id' => $division->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('users', [
            'name' => 'Tatang',
            'email' => 'tatang@gmail.com',
            'role' => 'karyawan',
            'is_default_password' => true,
        ]);

        Mail::assertSent(UserCreatedMail::class, function (UserCreatedMail $mail) {
            return $mail->hasTo('tatang@gmail.com')
                && $mail->user->email === 'tatang@gmail.com'
                && $mail->defaultPassword === 'jonusa123';
        });
    }
}
