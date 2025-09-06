// import EventForm from '@/components/ui/EventForm';
// import { listEvents } from '@/lib/api';
// import { useEffect, useState } from 'react';
// import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

// export default function EventsPage() {
//   const [events, setEvents] = useState<any[]>([]);
//   const [loading, setLoading] = useState(true);
//   const [error, setError] = useState<string | null>(null);

//   useEffect(() => {
//     const fetchEvents = async () => {
//       try {
//         const response = await listEvents();
//         setEvents(response.data);
//       } catch (err) {
//         setError('Failed to fetch events');
//       } finally {
//         setLoading(false);
//       }
//     };
//     fetchEvents();
//   }, []);

//   const handleEventCreated = () => {
//     fetchEvents();
//   };

//   if (loading) return <p>Loading...</p>;
//   if (error) return <p className="text-red-500">{error}</p>;

//   return (
//     <div className="container mx-auto p-4">
//       <h1 className="text-2xl font-bold mb-4">Events</h1>
//       <EventForm onSuccess={handleEventCreated} />
//       <div className="mt-4">
//         {events.length === 0 ? (
//           <p>No upcoming events</p>
//         ) : (
//           events.map((event) => (
//             <Card key={event.id} className="mb-4">
//               <CardHeader>
//                 <CardTitle>{event.name}</CardTitle>
//               </CardHeader>
//               <CardContent>
//                 <p>Location: {event.location}</p>
//                 <p>Start: {new Date(event.start_time).toLocaleString()}</p>
//                 <p>End: {new Date(event.end_time).toLocaleString()}</p>
//                 <p>Capacity: {event.max_capacity}</p>
//               </CardContent>
//             </Card>
//           ))
//         )}
//       </div>
//     </div>
//   );
// }
'use client';

import EventForm from '@/components/ui/EventForm';
import { listEvents } from '@/lib/api';
import { useEffect, useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import Link from 'next/link';

export default function EventsPage() {
  const [events, setEvents] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  const fetchEvents = async () => {
    try {
      const response = await listEvents();
      setEvents(response.data);
    } catch (err) {
      setError('Failed to fetch events');
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    fetchEvents();
  }, []); // Empty dependency array runs once on mount

  const handleEventCreated = () => {
    fetchEvents(); // Now accessible
  };

  if (loading) return <p>Loading...</p>;
  if (error) return <p className="text-red-500">{error}</p>;

  return (
    <div className="container mx-auto p-4">
      <h1 className="text-2xl font-bold mb-4">Events</h1>
      <EventForm onSuccess={handleEventCreated} />
      <div className="mt-4">
        {events.length === 0 ? (
          <p>No upcoming events</p>
        ) : (
          events.map((event) => (
            <Card key={event.id} className="mb-4">
              <CardHeader>
                <CardTitle>{event.name}</CardTitle>
              </CardHeader>
              <CardContent>
                <p> id : {event.id}</p>
                <p>Location: {event.location}</p>
                <p>Start: {new Date(event.start_time).toLocaleString()}</p>
                <p>End: {new Date(event.end_time).toLocaleString()}</p>
                <p>Capacity: {event.max_capacity}</p>
                <Link href={`/attendees/${event.id}`} className="text-blue-500 underline mt-2 block">
                  View Attendees
                </Link>
              </CardContent>
            </Card>
          ))
        )}
      </div>
    </div>
  );
}