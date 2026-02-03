import { derived, writable } from 'svelte/store';
import type { Game } from '$lib/types/game';

export type CartItem = {
	game: Game;
	quantity: number;
};

const loadInitialItems = (): CartItem[] => {
	if (typeof localStorage === 'undefined') {
		return [];
	}
	const raw = localStorage.getItem('cart');
	if (!raw) return [];
	try {
		const parsed = JSON.parse(raw);
		return Array.isArray(parsed) ? (parsed as CartItem[]) : [];
	} catch {
		return [];
	}
};

const itemsStore = writable<CartItem[]>(loadInitialItems());

if (typeof localStorage !== 'undefined') {
	itemsStore.subscribe((items) => {
		localStorage.setItem('cart', JSON.stringify(items));
	});
}

const addToCart = (game: Game, quantity = 1) => {
	itemsStore.update((items) => {
		const existing = items.find((item) => item.game.bggId === game.bggId);
		if (existing) {
			return items.map((item) =>
				item.game.bggId === game.bggId
					? { ...item, quantity: item.quantity + quantity }
					: item
			);
		}
		return [...items, { game, quantity }];
	});
};

const updateQuantity = (bggId: number, quantity: number) => {
	if (quantity <= 0) {
		removeFromCart(bggId);
		return;
	}
	itemsStore.update((items) =>
		items.map((item) => (item.game.bggId === bggId ? { ...item, quantity } : item))
	);
};

const removeFromCart = (bggId: number) => {
	itemsStore.update((items) => items.filter((item) => item.game.bggId !== bggId));
};

const clearCart = () => {
	itemsStore.set([]);
};

const cartCount = derived(itemsStore, (items) =>
	items.reduce((total, item) => total + item.quantity, 0)
);

const cartTotalCents = derived(itemsStore, (items) =>
	items.reduce((total, item) => total + (item.game.priceCents ?? 0) * item.quantity, 0)
);

export const cartItems = {
	subscribe: itemsStore.subscribe
};

export { addToCart, updateQuantity, removeFromCart, clearCart, cartCount, cartTotalCents };
