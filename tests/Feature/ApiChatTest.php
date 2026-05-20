<?php

namespace Tests\Feature;

use App\Models\Chat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiChatTest extends TestCase
{
    use RefreshDatabase;

    public function test_mention_message_is_visible_only_to_tagged_users_and_sender(): void
    {
        $sender = User::factory()->create(['name' => 'Sender']);
        $mentioned = User::factory()->create(['name' => 'Bob Mentioned']);
        $other = User::factory()->create(['name' => 'Other User']);

        Sanctum::actingAs($sender);

        $this->postJson('/api/chats', [
            'type' => 'text',
            'message' => 'Halo @['.$mentioned->id.'|'.$mentioned->name.']',
        ])->assertCreated();

        $this->postJson('/api/chats', [
            'type' => 'text',
            'message' => 'Pesan public',
        ])->assertCreated();

        Sanctum::actingAs($mentioned);
        $mentionedIndex = $this->getJson('/api/chats')->assertOk();
        $this->assertSame(['Halo @['.$mentioned->id.'|'.$mentioned->name.']', 'Pesan public'], $mentionedIndex->json('*.message'));

        Sanctum::actingAs($other);
        $otherIndex = $this->getJson('/api/chats')->assertOk();
        $this->assertSame(['Pesan public'], $otherIndex->json('*.message'));
    }

    public function test_chat_media_upload_returns_public_storage_path(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/chats', [
            'type' => 'image',
            'message' => 'Bukti pekerjaan',
            'file' => UploadedFile::fake()->create('proof.jpg', 10, 'image/jpeg'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('type', 'image')
            ->assertJsonPath('file_path', fn ($path) => str_starts_with($path, 'storage/uploads/'))
            ->assertJsonPath('file_url', fn ($url) => str_starts_with($url, 'http://localhost/storage/uploads/'));

        $this->assertCount(1, Storage::disk('public')->files('uploads'));
    }

    public function test_chat_upload_does_not_reject_large_files_at_application_validation(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/chats', [
            'type' => 'file',
            'message' => '',
            'file' => UploadedFile::fake()->create('large-report.zip', 60 * 1024, 'application/zip'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('type', 'file');

        $this->assertCount(1, Storage::disk('public')->files('uploads'));
    }

    public function test_chat_video_upload_is_encrypted_and_still_playable_through_media_endpoint(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/chats', [
            'type' => 'video',
            'message' => '',
            'file' => UploadedFile::fake()->create('clip.mp4', 128, 'video/mp4'),
        ]);

        $response->assertCreated()
            ->assertJsonPath('type', 'video')
            ->assertJsonPath('file_path', fn ($path) => str_starts_with($path, '/chat-media/'))
            ->assertJsonPath('file_url', fn ($url) => str_contains($url, '/api/chats/'));

        $chat = Chat::firstOrFail();

        $this->assertTrue($chat->isEncryptedVideo());
        Storage::disk('local')->assertExists($chat->file_path);
        $this->assertCount(0, Storage::disk('public')->files('uploads'));

        $this->getJson("/api/chats/{$chat->id}/media")
            ->assertOk()
            ->assertHeader('content-type', 'video/mp4');
    }

    public function test_chat_video_can_be_uploaded_in_chunks(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $uploadId = 'upload-test-1';

        $this->postJson('/api/chats/chunks', [
            'upload_id' => $uploadId,
            'chunk_index' => 0,
            'total_chunks' => 2,
            'chunk' => UploadedFile::fake()->createWithContent('clip.part0', 'hello '),
        ])->assertOk();

        $this->postJson('/api/chats/chunks', [
            'upload_id' => $uploadId,
            'chunk_index' => 1,
            'total_chunks' => 2,
            'chunk' => UploadedFile::fake()->createWithContent('clip.part1', 'world'),
        ])->assertOk();

        $response = $this->postJson('/api/chats/chunks/complete', [
            'upload_id' => $uploadId,
            'total_chunks' => 2,
            'type' => 'video',
            'message' => '',
            'file_name' => 'clip.mp4',
        ]);

        $response->assertCreated()
            ->assertJsonPath('type', 'video');

        $chat = Chat::firstOrFail();

        $this->assertTrue($chat->isEncryptedVideo());
        Storage::disk('local')->assertExists($chat->file_path);
        Storage::disk('local')->assertMissing('chat-chunks/'.$uploadId);
    }

    public function test_chat_index_normalizes_existing_public_disk_paths(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Chat::create([
            'user_id' => $user->id,
            'message' => '',
            'type' => 'audio',
            'file_path' => 'uploads/audio.m4a',
        ]);

        $response = $this->getJson('/api/chats');

        $response->assertOk()
            ->assertJsonPath('0.type', 'audio')
            ->assertJsonPath('0.file_path', 'storage/uploads/audio.m4a')
            ->assertJsonPath('0.file_url', 'http://localhost/storage/uploads/audio.m4a');
    }

    public function test_public_storage_fallback_route_serves_uploaded_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('uploads/readme.txt', 'hello');

        $response = $this->get('/storage/uploads/readme.txt');

        $response->assertOk();
        $this->assertSame('hello', $response->baseResponse->getFile()->getContent());
    }
}
