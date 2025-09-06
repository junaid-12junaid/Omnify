'use client';

import { useState } from 'react';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { createEvent } from '@/lib/api';

export default function EventForm({ onSuccess }: { onSuccess: () => void }) {
  const [form, setForm] = useState({
    name: '',
    location: '',
    start_time: '',
    end_time: '',
    max_capacity: '',
  });
  const [error, setError] = useState<string | null>(null);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setError(null);
    try {
      await createEvent({
        ...form,
        max_capacity: parseInt(form.max_capacity),
      });
      setForm({ name: '', location: '', start_time: '', end_time: '', max_capacity: '' });
      onSuccess();
    } catch (err) {
      setError('Failed to create event. Please check your input.');
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4">
      <Input
        placeholder="Event Name"
        value={form.name}
        onChange={(e) => setForm({ ...form, name: e.target.value })}
        required
      />
      <Input
        placeholder="Location"
        value={form.location}
        onChange={(e) => setForm({ ...form, location: e.target.value })}
        required
      />
      <Input
        type="datetime-local"
        value={form.start_time}
        onChange={(e) => setForm({ ...form, start_time: e.target.value })}
        required
      />
      <Input
        type="datetime-local"
        value={form.end_time}
        onChange={(e) => setForm({ ...form, end_time: e.target.value })}
        required
      />
      <Input
        type="number"
        placeholder="Max Capacity"
        value={form.max_capacity}
        onChange={(e) => setForm({ ...form, max_capacity: e.target.value })}
        required
        min="1"
      />
      {error && <p className="text-red-500">{error}</p>}
      <Button type="submit" >Create Event</Button>
    </form>
  );
}
