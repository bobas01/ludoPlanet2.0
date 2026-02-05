import type { Game } from './game';

export type AdminGame = Pick<Game, 'bggId' | 'name' | 'priceCents' | 'description'> & {
	domainIds: number[];
	mechanicIds: number[];
};

export type AdminOption = {
	id: number;
	name: string;
};

export type CategorySlug = 'enfants' | 'ambiance' | 'plateau' | 'cartes' | 'expert';

export type CategoryImage = {
	slug: CategorySlug;
	label: string;
	url: string;
	updating: boolean;
};

export type OrderItem = {
	id: number;
	game_id: number | null;
	game_name: string | null;
	game_image_url: string | null;
	quantity: number;
	unit_price_cents: number;
};

export type Order = {
	id: number;
	user_id: number | null;
	status: string;
	total_cents: number;
	currency: string;
	shipping: {
		full_name: string | null;
		city: string | null;
		postal_code: string | null;
	};
	items: OrderItem[];
	created_at: string;
};

export type DashboardTab = 'games' | 'categories' | 'domains' | 'mechanics' | 'orders';
