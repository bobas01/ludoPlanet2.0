export const BASE_URL ='http://localhost:8000'

type ApiError = Error & { status?: number; data?: unknown };

const request = async <T>(path: string, options: RequestInit): Promise<T> => {
	const res = await fetch(`${BASE_URL}${path}`, {
		credentials: 'include',
		...options
	});
	const contentType = res.headers.get('content-type') ?? '';
	let data: unknown = null;
	if (contentType.includes('application/json')) {
		data = await res.json();
	} else if (contentType) {
		data = await res.text();
	}
	if (!res.ok) {
		const error: ApiError = new Error(`API ${res.status}: ${res.statusText}`);
		error.status = res.status;
		error.data = data;
		throw error;
	}
	return data as T;
};

export const api = {
	async get<T>(path: string): Promise<{ data: T }> {
		const data = await request<T>(path, { method: 'GET' });
		return { data };
	},
	async post<T>(path: string, body: unknown): Promise<{ data: T }> {
		const data = await request<T>(path, {
			method: 'POST',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(body)
		});
		return { data };
	},
	async put<T>(path: string, body: unknown): Promise<{ data: T }> {
		const data = await request<T>(path, {
			method: 'PUT',
			headers: { 'Content-Type': 'application/json' },
			body: JSON.stringify(body)
		});
		return { data };
	},
	async delete<T>(path: string): Promise<{ data: T }> {
		const data = await request<T>(path, { method: 'DELETE' });
		return { data };
	},
	async postForm<T>(path: string, body: FormData): Promise<{ data: T }> {
		const data = await request<T>(path, {
			method: 'POST',
			body
		});
		return { data };
	}
};
