# Event Management Backend

## Setup
1. composer install
2. cp .env.example .env
3. touch database/database.sqlite
4. php artisan migrate
5. php artisan serve

## Assumptions
- Timezones: Inputs in IST, stored in UTC, outputs in app timezone (IST).
- Edge cases handled: validation, overbooking, duplicates, past events, pagination, 404s.

## Sample API Requests (cURL)

Create Event:
curl -X POST http://127.0.0.1:8000/api/events -H "Content-Type: application/json" -d '{"name":"Test","location":"Online","start_time":"2025-09-10 10:00:00","end_time":"2025-09-10 12:00:00","max_capacity":50}'

List Events:
curl http://127.0.0.1:8000/api/events

Register:
curl -X POST http://127.0.0.1:8000/api/events/1/register -H "Content-Type: application/json" -d '{"name":"John","email":"john@example.com"}'

Attendees:
curl http://127.0.0.1:8000/api/events/1/attendees?page=1&per_page=10


# Event Management Frontend

## Setup
1. Ensure the Laravel backend is running at http://127.0.0.1:8000/api.
2. Clone this repo or unzip the project folder.
3. Install dependencies: `npm install`
4. Create `.env.local` with `NEXT_PUBLIC_API_URL=http://127.0.0.1:8000/api`
5. Run the development server: `npm run dev`
6. Access at http://localhost:3000/events

## Assumptions
- Backend API endpoints are available as provided.
- Timezone conversion is handled by the backend.

## Sample Requests
- Create Event: (Handled via frontend form)
- List Events: http://localhost:3000/events
- Register Attendee: http://localhost:3000/attendees/1
- View Attendees: http://localhost:3000/attendees/1