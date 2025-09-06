import axios from 'axios';

const api = axios.create({
  baseURL: process.env.NEXT_PUBLIC_API_URL,
  headers: {
    'Content-Type': 'application/json',
  },
});

export const createEvent = (data: { name: string; location: string; start_time: string; end_time: string; max_capacity: number }) =>
  api.post('/events', data);

export const listEvents = () => api.get('/events');

export const registerAttendee = (eventId: string, data: { name: string; email: string }) =>
  api.post(`/events/${eventId}/register`, data);

export const getAttendees = (eventId: string, page: number = 1, perPage: number = 10) =>
  api.get(`/events/${eventId}/attendees`, { params: { page, per_page: perPage } });

export default api;
