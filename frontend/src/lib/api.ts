const BASE_URL =
	typeof import.meta.env?.VITE_API_URL === 'string'
		? import.meta.env.VITE_API_URL.replace(/\/$/, '')
		: 'http://localhost:8000';

export const api = {
	async get<T>(path: string): Promise<{ data: T }> {
		const res = await fetch(`${BASE_URL}${path}`);
		if (!res.ok) {
			throw new Error(`API ${res.status}: ${res.statusText}`);
		}
		const data = (await res.json()) as T;
		return { data };
	}
};
