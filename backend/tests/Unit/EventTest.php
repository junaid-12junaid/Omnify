<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class EventTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_event()
    {
        $response = $this->postJson('/api/events', [
            'name' => 'Test Event',
            'location' => 'Online',
            'start_time' => Carbon::now('Asia/Kolkata')->addDays(1)->toDateTimeString(),
            'end_time' => Carbon::now('Asia/Kolkata')->addDays(1)->addHours(2)->toDateTimeString(),
            'max_capacity' => 10,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', ['name' => 'Test Event']);
    }

    public function test_list_events()
    {
        Event::factory()->create(['start_time' => Carbon::now('UTC')->addDays(1)]);
        $response = $this->getJson('/api/events');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
    }

    public function test_register_attendee()
    {
        $event = Event::factory()->create(['max_capacity' => 10]);
        $response = $this->postJson("/api/events/{$event->id}/register", [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        $response->assertStatus(201);
        $this->assertDatabaseHas('attendees', ['email' => 'john@example.com']);
    }

    public function test_register_over_capacity()
    {
        $event = Event::factory()->create(['max_capacity' => 1]);
        Attendee::factory()->create(['event_id' => $event->id]);
        $response = $this->postJson("/api/events/{$event->id}/register", [
            'name' => 'Jane',
            'email' => 'jane@example.com',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['error' => 'Event is at max capacity']);
    }

    public function test_duplicate_registration()
    {
        $event = Event::factory()->create();
        Attendee::factory()->create(['event_id' => $event->id, 'email' => 'dup@example.com']);
        $response = $this->postJson("/api/events/{$event->id}/register", [
            'name' => 'Dup',
            'email' => 'dup@example.com',
        ]);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['error' => 'Email already registered for this event']);
    }

    public function test_past_event_registration()
    {
        $event = Event::factory()->create(['start_time' => Carbon::now('UTC')->subDay()]);
        $response = $this->postJson("/api/events/{$event->id}/register", [
            'name' => 'Past',
            'email' => 'past@example.com',
        ]);
        $response->assertStatus(422);
    }

    public function test_attendees_pagination()
    {
        $event = Event::factory()->create();
        Attendee::factory(15)->create(['event_id' => $event->id]);
        $response = $this->getJson("/api/events/{$event->id}/attendees?per_page=10");
        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'links', 'meta']);
    }

    // More tests for invalid inputs, non-existent events, etc.
    public function test_invalid_event_id()
    {
        $response = $this->getJson('/api/events/999/attendees');
        $response->assertStatus(404);
    }
}