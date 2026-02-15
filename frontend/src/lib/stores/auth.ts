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

const SESSION_FLAG = 'ludoplanet_has_session';

const hasSessionFlag = (): boolean => typeof document !== 'undefined' && sessionStorage.getItem(SESSION_FLAG) === '1';
const setSessionFlag = (): void => {
	if (typeof document !== 'undefined') sessionStorage.setItem(SESSION_FLAG, '1');
};
const clearSessionFlag = (): void => {
	if (typeof document !== 'undefined') sessionStorage.removeItem(SESSION_FLAG);
};

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
		if (!hasSessionFlag()) {
			userStore.set(null);
			errorStore.set(null);
			return;
		}
		const { data } = await api.get<{ user: User }>('/api/me');
		userStore.set(data.user);
		errorStore.set(null);
	} catch (err) {
		userStore.set(null);
		const status = err && typeof err === 'object' && 'status' in err ? (err as { status: number }).status : 0;
		if (status === 401) {
			clearSessionFlag();
			errorStore.set(null);
		} else {
			errorStore.set(parseAuthError(err));
		}
	} finally {
		loadingStore.set(false);
	}
};

export const login = async (email: string, password: string): Promise<boolean> => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.post('/api/login', { email, password });
		setSessionFlag();
		await loadMe();
		return true;
	} catch (err) {
		userStore.set(null);
		const status =
			err && typeof err === 'object' && 'status' in err
				? (err as { status: number }).status
				: 0;

		if (status === 401) {
			errorStore.set({ message: "Adresse e-mail ou mot de passe incorrect." });
		} else {
			errorStore.set(parseAuthError(err));
		}
		return false;
	} finally {
		loadingStore.set(false);
	}
};

export const register = async (payload: {
	email: string;
	password: string;
	firstName?: string;
	lastName?: string;
	address?: string;
	phoneNumber?: string;
	birthDate?: string;
}): Promise<boolean> => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.post<{ user: User }>('/api/register', payload);
		return true;
	} catch (err) {
		errorStore.set(parseAuthError(err));
		return false;
	} finally {
		loadingStore.set(false);
	}
};

export const logout = async () => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.post('/api/logout', {});
		clearSessionFlag();
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
		clearSessionFlag();
		userStore.set(null);
	} catch (err) {
		errorStore.set(parseAuthError(err));
	} finally {
		loadingStore.set(false);
	}
};

export const forgotPassword = async (email: string): Promise<boolean> => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.post('/api/forgot-password', { email });
		return true;
	} catch (err) {
		errorStore.set(parseAuthError(err));
		return false;
	} finally {
		loadingStore.set(false);
	}
};

export const resetPassword = async (token: string, password: string): Promise<boolean> => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.post('/api/reset-password', { token, password });
		return true;
	} catch (err) {
		errorStore.set(parseAuthError(err));
		return false;
	} finally {
		loadingStore.set(false);
	}
};

export const verifyEmail = async (token: string): Promise<boolean> => {
	try {
		loadingStore.set(true);
		errorStore.set(null);
		await api.get(`/api/verify-email?token=${encodeURIComponent(token)}`);
		return true;
	} catch (err) {
		errorStore.set(parseAuthError(err));
		return false;
	} finally {
		loadingStore.set(false);
	}
};
