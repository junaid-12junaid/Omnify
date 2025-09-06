'use client';

import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card";
import { getAttendees } from '@/lib/api';
import { useEffect, useState } from "react";

export default function AttendeeList({ eventId }: { eventId: string }) {
  const [attendees, setAttendees] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const fetchAttendees = async () => {
      try {
        const response = await getAttendees(eventId, 1, 10);
        setAttendees(response.data.data || []);
      } catch (err) {
        setError('Failed to fetch attendees');
      } finally {
        setLoading(false);
      }
    };
    fetchAttendees();
  }, [eventId]);

  if (loading) return <p>Loading...</p>;
  if (error) return <p className="text-red-500">{error}</p>;

  return (
    <Card>
      <CardHeader>
        <CardTitle>Attendees</CardTitle>
      </CardHeader>
      <CardContent>
        {attendees.length === 0 ? (
          <p>No attendees yet</p>
        ) : (
          <ul>
            {attendees.map((attendee) => (
              <li key={attendee.id}>{attendee.name} ({attendee.email})</li>
            ))}
          </ul>
        )}
      </CardContent>
    </Card>
  );
}
