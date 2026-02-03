import { writable } from 'svelte/store';
import { api } from '$lib/api';

export type User = {
	id: number;
	email: string;
	roles: string[];
	firstName: string;
	lastName: string;
	address: string;
	phoneNumber: string;
	birthDate: string;
};

type AuthError = {
	message: string;
	allowedSpecials?: string;
};

const userStore = writable<User | null>(null);
const loadingStore = writable(false);
const errorStore = writable<AuthError | null>(null);

const parseAuthError = (err: unknown): AuthError => {
	if (typeof err === 'object' && err !== null && 'data' in err) {
		const data = (err as { data?: unknown }).data;
		if (data && typeof data === 'object' && 'error' in data) {
			const allowedSpecials =
				typeof (data as { allowedSpecials?: unknown }).allowedSpecials === 'string'
					? (data as { allowedSpecials: string }).allowedSpecials
					: undefined;
			return { message: String((data as { error: string }).error), allowedSpecials };
		}
	}
	return { message: 'Erreur inconnue.' };
};

export const authUser = userStore;
export const authLoading = loadingStore;
export const authError = errorStore;

export const loadMe = async () => {
	try {
		loadingStore.set(true);
		const { data } = await api.get<{ user: User }>('/api/me');
		userStore.set(data.user);
		errorStore.set(null);
	} catch (err) {
		userStore.set(null);
		errorStore.set(parseAuthError(err));
	} finally {
		loadingStore.set(false);
	}
};

export const login = async (email: string, password: string): Promise<boolean> => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.post('/api/login', { email, password });
		await loadMe();
		return true;
	} catch (err) {
		userStore.set(null);
		errorStore.set(parseAuthError(err));
		return false;
	} finally {
		loadingStore.set(false);
	}
};

export const register = async (payload: {
	email: string;
	password: string;
	firstName: string;
	lastName: string;
	address: string;
	phoneNumber: string;
	birthDate: string;
}) => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		const { data } = await api.post<{ user: User }>('/api/register', payload);
		userStore.set(data.user);
	} catch (err) {
		userStore.set(null);
		errorStore.set(parseAuthError(err));
	} finally {
		loadingStore.set(false);
	}
};

export const logout = async () => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.post('/api/logout', {});
		userStore.set(null);
	} catch (err) {
		errorStore.set(parseAuthError(err));
	} finally {
		loadingStore.set(false);
	}
};

export const updateMe = async (payload: Partial<User> & { password?: string }) => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		const { data } = await api.put<{ user: User }>('/api/me', payload);
		userStore.set(data.user);
	} catch (err) {
		errorStore.set(parseAuthError(err));
	} finally {
		loadingStore.set(false);
	}
};

export const deleteMe = async () => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.delete('/api/me');
		userStore.set(null);
	} catch (err) {
		errorStore.set(parseAuthError(err));
	} finally {
		loadingStore.set(false);
	}
};
