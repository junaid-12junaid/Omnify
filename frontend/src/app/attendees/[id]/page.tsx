// import AttendeeForm from '@/components/ui/AttendeeForm';
// import AttendeeList from '@/components/ui/AttendeeList';

// export default function AttendeePage({ params }: { params: { id: string } }) {
//     console.log(params.id,  'params.id');
//   const handleAttendeeRegistered = () => {
//     // Refresh page or state
//     window.location.reload();
//   };

//   return (
//     <div className="container mx-auto p-4">
//       <h1 className="text-2xl font-bold mb-4">Event {params.id} Attendees</h1>
//       <AttendeeForm eventId={params.id} onSuccess={handleAttendeeRegistered} />
//       <div className="mt-4">
//         <AttendeeList eventId={params.id} />
//       </div>
//     </div>
//   );
// }



'use client';

import { useState, useEffect, use } from 'react';
import AttendeeForm from '@/components/ui/AttendeeForm';
import AttendeeList from '@/components/ui/AttendeeList';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { getAttendees } from '@/lib/api';

export default function AttendeePage({ params }: { params: Promise<{ id: string }> }) {
    const unwrappedParams = use(params); // Unwrap the params Promise
    const eventId = parseInt(unwrappedParams.id, 10); // Convert to number and validate
  if (isNaN(eventId)) {
    return <div className="container mx-auto p-4"><p className="text-red-500">Invalid event ID</p></div>;
  }
  const [eventName, setEventName] = useState<string | null>(null);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchEventDetails = async () => {
      if (isNaN(eventId)) {
        setError('Invalid event ID');
        setLoading(false);
        return;
      }
      try {
        const response = await getAttendees(unwrappedParams.id, 1, 1); // Fetch first page to check event
        setEventName(response.data.message === 'No attendees yet' ? `Event ${unwrappedParams.id}` : `Event ${unwrappedParams.id} - ${response.data.data[0]?.event?.name || 'Unnamed'}`);
      } catch (err) {
        setError('Failed to fetch event details');
      } finally {
        setLoading(false);
      }
    };
    fetchEventDetails();
  }, [unwrappedParams.id]);

  const handleAttendeeRegistered = () => {
    // Consider using state management (e.g., useReducer) instead of reload for better UX
    window.location.reload();
  };

  if (loading) return <p>Loading...</p>;
  if (error) return <p className="text-red-500">{error}</p>;
  if (!eventName) return <p>Event not found</p>;

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-2xl font-bold mb-4">{eventName} Attendees</h1>
      <Card className="mb-4">
        <CardHeader>
          <CardTitle>Register Attendee</CardTitle>
        </CardHeader>
        <CardContent>
          <AttendeeForm eventId={unwrappedParams.id} onSuccess={handleAttendeeRegistered} />
        </CardContent>
      </Card>
      <AttendeeList eventId={unwrappedParams.id} />
    </div>
  );
}