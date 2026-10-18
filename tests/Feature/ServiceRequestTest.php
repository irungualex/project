<?php

namespace Tests\Feature;

use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_citizen_can_submit_a_request_and_only_see_their_own_requests(): void
    {
        $disk = config('filesystems.default');
        Storage::fake($disk);
        $citizen = User::factory()->create();
        $otherCitizen = User::factory()->create();
        ServiceRequest::create([
            'user_id' => $otherCitizen->id,
            'request_number' => 'CD-OTHER',
            'category' => 'Other',
            'location' => 'Another street',
            'description' => 'A separate citizen request.',
            'status' => 'Pending',
        ]);

        $this->actingAs($citizen)->post(route('requests.store'), [
            'category' => 'Street Lighting',
            'location' => 'Kilimani, Block 4',
            'description' => 'The streetlight at the corner is not working.',
            'image' => UploadedFile::fake()->image('streetlight.jpg'),
        ])->assertRedirect(route('requests.index'));

        $this->assertDatabaseHas('service_requests', [
            'user_id' => $citizen->id,
            'category' => 'Street Lighting',
            'status' => 'Pending',
        ]);
        $this->assertCount(1, $citizen->serviceRequests()->get());
        $this->actingAs($citizen)
            ->get(route('requests.index'))
            ->assertOk()
            ->assertSee('Kilimani, Block 4')
            ->assertDontSee('Another street');
        Storage::disk($disk)->assertExists($citizen->serviceRequests()->first()->image_path);
    }

    public function test_an_administrator_can_advance_a_request_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $serviceRequest = ServiceRequest::create([
            'request_number' => 'CD-TEST',
            'category' => 'Other',
            'location' => 'Civic centre',
            'description' => 'Test request description.',
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.requests.status', $serviceRequest), ['status' => 'In Progress'])
            ->assertRedirect();

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'In Progress',
        ]);
    }

    public function test_a_citizen_cannot_access_admin_request_management(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.requests.index'))
            ->assertForbidden();
    }

    public function test_an_administrator_cannot_skip_a_request_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $serviceRequest = ServiceRequest::create([
            'request_number' => 'CD-TEST',
            'category' => 'Other',
            'location' => 'Civic centre',
            'description' => 'Test request description.',
            'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.requests.status', $serviceRequest), ['status' => 'Resolved'])
            ->assertSessionHasErrors('status');

        $this->assertDatabaseHas('service_requests', [
            'id' => $serviceRequest->id,
            'status' => 'Pending',
        ]);
    }
}
