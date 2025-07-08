const BASE = '/api';

export async function getTickets(query = '') {
    const res = await fetch(`${BASE}/tickets?${query}`);
    return res.json();
}

export async function getTicket(id) {
    const res = await fetch(`${BASE}/tickets/${id}`);
    return res.json();
}

export async function createTicket(data) {
    const res = await fetch(`${BASE}/tickets`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    return res.json();
}

export async function classify(id) {
    const res = await fetch(`${BASE}/tickets/${id}/classify`, {
        method: 'POST',
    });
    return res.json();
}

export async function getStats() {
    const res = await fetch(`${BASE}/stats`);
    return res.json();
}
