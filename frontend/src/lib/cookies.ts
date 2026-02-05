/**
 * Utilitaires pour les cookies (côté navigateur uniquement).
 * À utiliser dans onMount ou après vérification typeof document !== 'undefined'.
 */

const COOKIE_CONSENT_NAME = 'cookie_consent';

/** "all" = tout accepter (essentiels + optionnels), "essential" = essentiels uniquement */
export type CookieConsentValue = 'all' | 'essential';

export function getCookie(name: string): string | null {
	if (typeof document === 'undefined') return null;
	const match = document.cookie.match(new RegExp('(?:^|; )' + name.replace(/([.$?*|{}()[\]\\/+^])/g, '\\$1') + '=([^;]*)'));
	return match ? decodeURIComponent(match[1]) : null;
}

export function setCookie(name: string, value: string, maxAgeDays = 365): void {
	if (typeof document === 'undefined') return;
	const maxAge = maxAgeDays * 24 * 60 * 60;
	document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${maxAge}; SameSite=Lax`;
}

export function getCookieConsent(): CookieConsentValue | null {
	const v = getCookie(COOKIE_CONSENT_NAME);
	if (v === 'all' || v === 'essential') return v;
	return null;
}

export function setCookieConsent(value: CookieConsentValue): void {
	setCookie(COOKIE_CONSENT_NAME, value);
}

/** À utiliser pour charger analytics / pub : ne charger que si l'utilisateur a choisi "all". */
export function hasOptionalCookieConsent(): boolean {
	return getCookieConsent() === 'all';
}
