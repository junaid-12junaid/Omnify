'use client';

import { useState } from 'react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { registerAttendee } from '@/lib/api';

export default function AttendeeForm({ eventId, onSuccess }: { eventId: string; onSuccess: () => void }) {
  const [form, setForm] = useState({ name: '', email: '' });
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      await registerAttendee(eventId, form);
      setForm({ name: '', email: '' });
      onSuccess();
    } catch (err) {
      setError('Failed to register attendee. Check email or capacity.');
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <Input
        placeholder="Name"
        value={form.name}
        onChange={(e) => setForm({ ...form, name: e.target.value })}
        required
      />
      <Input
        type="email"
        placeholder="Email"
        value={form.email}
        onChange={(e) => setForm({ ...form, email: e.target.value })}
        required
      />
      {error && <p className="text-red-500">{error}</p>}
      <Button type="submit">Register</Button>
    </form>
  );
}
