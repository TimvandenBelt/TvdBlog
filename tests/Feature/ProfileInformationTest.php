<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileInformationTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_information_can_be_updated()
    {
        $this->actingAs($user = User::factory()->create());

        $response = $this->put("/user/profile-information", [
            "name" => "Test Name",
            "email" => $user->email,
        ]);

        $this->assertEquals("Test Name", $user->fresh()->name);

        Event::fake();

        $response = $this->put("/user/profile-information", [
            "name" => $user->fresh()->name,
            "email" => "test@example.com",
        ]);

        $this->assertEquals("test@example.com", $user->fresh()->email);
    }

    public function test_profile_photo_can_be_updated()
    {
        Storage::fake("photos");

        $this->actingAs($user = User::factory()->create());

        $response = $this->put("/user/profile-information", [
            "name" => $user->name,
            "email" => $user->email,
            "photo" => UploadedFile::fake()->image("profile-photo.jpg"),
        ]);

        Storage::disk(
            config("jetstream.profile_photo_disk", "public"),
        )->assertExists($user->fresh()->profile_photo_path);
    }
}
