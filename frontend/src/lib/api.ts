export const BASE_URL =
	import.meta.env.MODE === 'dev'
		? 'http://localhost:8000'
		: 'http://localhost:9000';

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
