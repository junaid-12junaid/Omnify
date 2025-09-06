<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Attendee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Info(title="Event Management API", version="1.0")
 */
class EventController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/events",
     *     summary="Create a new event",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"name","location","start_time","end_time","max_capacity"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="location", type="string"),
     *             @OA\Property(property="start_time", type="string", format="date-time"),
     *             @OA\Property(property="end_time", type="string", format="date-time"),
     *             @OA\Property(property="max_capacity", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Event created")
     * )
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'start_time' => 'required|date|after:now',
            'end_time' => 'required|date|after:start_time',
            'max_capacity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();
        $data['start_time'] = Carbon::parse($data['start_time'], 'Asia/Kolkata')->utc();
        $data['end_time'] = Carbon::parse($data['end_time'], 'Asia/Kolkata')->utc();

        $event = Event::create($data);

        return response()->json($event, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/events",
     *     summary="List all upcoming events",
     *     @OA\Response(response=200, description="List of events")
     * )
     */
    public function list()
    {
        $events = Event::where('start_time', '>', Carbon::now('UTC'))->get();
        foreach ($events as $event) {
            $event->start_time = Carbon::parse($event->start_time)->setTimezone('Asia/Kolkata');
            $event->end_time = Carbon::parse($event->end_time)->setTimezone('Asia/Kolkata');
        }
        return response()->json($events);
    }

    /**
     * @OA\Post(
     *     path="/api/events/{event_id}/register",
     *     summary="Register attendee for event",
     *     @OA\Parameter(name="event_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"name","email"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="email", type="string", format="email")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Registered")
     * )
     */
    public function register(Request $request, $event_id)
{
    \Log::info('Register attempt for event_id: ' . $event_id . ', Request: ' . json_encode($request->all()));

    try {
        $event = Event::findOrFail($event_id);
        \Log::info('Event found: ' . json_encode($event->toArray()));
    } catch (\Exception $e) {
        \Log::error('Event not found or error: ' . $e->getMessage());
        return response()->json(['error' => 'Event not found'], 404);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:255',
        'email' => 'required|email',
    ]);

    if ($validator->fails()) {
        \Log::error('Validation failed: ' . json_encode($validator->errors()->toArray()));
        return response()->json(['errors' => $validator->errors()], 422);
    }

    \Log::info('Validation passed');

    // Edge case: Event full
    $attendeeCount = $event->attendees()->count();
    \Log::info('Attendee count: ' . $attendeeCount . ', Max capacity: ' . $event->max_capacity);
    if ($attendeeCount >= $event->max_capacity) {
        \Log::warning('Event full: ' . $event_id);
        return response()->json(['error' => 'Event is at max capacity'], 422);
    }

    // Edge case: Duplicate email
    if ($event->attendees()->where('email', $request->email)->exists()) {
        \Log::warning('Duplicate email: ' . $request->email);
        return response()->json(['error' => 'Email already registered for this event'], 422);
    }

    // Edge case: Past event
    try {
        $startTime = Carbon::parse($event->start_time, 'UTC');
        $now = Carbon::now('UTC');
        \Log::info('Start time: ' . $startTime->toDateTimeString() . ', Now: ' . $now->toDateTimeString());
        if ($startTime < $now) {
            \Log::warning('Past event detected');
            return response()->json(['error' => 'Cannot register for past event'], 422);
        }
    } catch (\Exception $e) {
        \Log::error('Carbon parse error: ' . $e->getMessage());
        return response()->json(['error' => 'Invalid event date'], 422);
    }

    $attendee = Attendee::create([
        'event_id' => $event_id,
        'name' => $request->name,
        'email' => $request->email,
    ]);
    \Log::info('Attendee created: ' . json_encode($attendee->toArray()));

    return response()->json($attendee, 201);
}

    /**
     * @OA\Get(
     *     path="/api/events/{event_id}/attendees",
     *     summary="Get attendees for event with pagination",
     *     @OA\Parameter(name="event_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="per_page", in="query", @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Paginated attendees")
     * )
     */
    public function attendees($event_id)
{
    \Log::info('Attendees request for event_id: ' . $event_id);

    try {
        $event = Event::findOrFail($event_id);
        \Log::info('Event found: ' . json_encode($event->toArray()));
    } catch (\Exception $e) {
        \Log::error('Event not found: ' . $e->getMessage());
        return response()->json(['error' => 'Event not found'], 404);
    }

    $perPage = request()->input('per_page', 10);
    $page = request()->input('page', 1);

    // Validate pagination parameters
    if (!is_numeric($perPage) || $perPage <= 0) {
        \Log::warning('Invalid per_page value: ' . $perPage);
        return response()->json(['error' => 'Invalid per_page value, must be a positive number'], 400);
    }
    if (!is_numeric($page) || $page <= 0) {
        \Log::warning('Invalid page value: ' . $page);
        return response()->json(['error' => 'Invalid page value, must be a positive number'], 400);
    }

    $attendees = $event->attendees()->paginate((int)$perPage, ['*'], 'page', (int)$page);
    \Log::info('Paginated attendees: ' . json_encode($attendees->toArray()));

    // Edge case: No attendees
    if ($attendees->isEmpty()) {
        \Log::info('No attendees found');
        return response()->json([
            'message' => 'No attendees yet',
            'data' => [],
            'meta' => [
                'current_page' => $attendees->currentPage(),
                'last_page' => $attendees->lastPage(),
                'per_page' => $attendees->perPage(),
                'total' => $attendees->total(),
            ],
        ], 200);
    }

    return response()->json($attendees, 200);
}
}