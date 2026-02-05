<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { BASE_URL, api } from '$lib/api';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { clearCart } from '$lib/stores/cart';
	import { formatPrice } from '$lib/utils/games';

	type OrderItem = {
		id: number;
		game_id: number;
		game_name?: string | null;
		game_image_url?: string | null;
		quantity: number;
		unit_price_cents: number;
	};

	type Order = {
		id: number;
		status: string;
		total_cents: number;
		currency: string;
		items: OrderItem[];
		created_at: string;
	};

	let order: Order | null = null;
	let loading = true;
	let error = '';

	const sessionId = $page.url.searchParams.get('session_id');

	const resolveImageUrl = (url: string | null) => {
		if (!url) return null;
		const normalized = url.startsWith('/images/categories/') ? url.replace(/\.svg$/, '.png') : url;
		return normalized.startsWith('http') ? normalized : `${BASE_URL}${normalized}`;
	};

	onMount(() => {
		clearCart();
		if (!sessionId) {
			error = 'Session invalide.';
			loading = false;
			return;
		}
		api
			.get<{ order: Order }>(
				`/api/checkout/order-by-session?session_id=${encodeURIComponent(sessionId)}`
			)
			.then(({ data }) => {
				order = data.order;
			})
			.catch(() => {
				error = 'Impossible de charger le détail de la commande.';
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

<div class="mx-auto max-w-2xl">
	{#if loading}
		<Card class="mt-8">
			<CardContent class="py-10 text-center text-slate-500">Chargement…</CardContent>
		</Card>
	{:else if error}
		<Card class="mt-8">
			<CardContent class="py-10 text-center">
				<p class="text-red-600">{error}</p>
				<Button class="mt-4" onclick={() => goto('/me')}>Voir mes commandes</Button>
			</CardContent>
		</Card>
	{:else if order}
		<h1 class="text-2xl font-bold text-slate-800">Paiement réussi</h1>
		<p class="mt-1 text-slate-600">
			Merci pour votre commande. Retirez-la en magasin. Vous la retrouverez dans « Mes commandes ».
		</p>

		<Card class="mt-6">
			<CardHeader>
				<CardTitle>Récapitulatif de la commande #{order.id}</CardTitle>
			</CardHeader>
			<CardContent class="space-y-4">
				<ul class="space-y-3">
					{#each order.items as item}
						<li class="flex items-center gap-4 border-b border-slate-100 pb-3 last:border-0">
							{#if resolveImageUrl(item.game_image_url ?? null)}
								<img
									src={resolveImageUrl(item.game_image_url ?? null) as string}
									alt={item.game_name ?? ''}
									class="h-12 w-12 rounded-md object-cover"
								/>
							{:else}
								<div
									class="flex h-12 w-12 items-center justify-center rounded-md bg-slate-100 text-xs text-slate-400"
								>
									N/A
								</div>
							{/if}
							<div class="min-w-0 flex-1">
								<p class="text-sm font-medium text-slate-800">{item.game_name ?? 'Jeu'}</p>
								<p class="text-xs text-slate-500">
									{item.quantity} × {formatPrice(item.unit_price_cents)}
								</p>
							</div>
							<p class="text-sm font-semibold text-slate-800">
								{formatPrice(item.quantity * item.unit_price_cents)}
							</p>
						</li>
					{/each}
				</ul>
				<div class="border-t border-slate-200 pt-3 text-right">
					<strong class="text-slate-800">Total : {formatPrice(order.total_cents)}</strong>
				</div>
			</CardContent>
		</Card>

		<div class="mt-6 flex flex-wrap justify-center gap-3">
			<Button variant="outline" onclick={() => goto('/games')}>Continuer mes achats</Button>
			<Button class="bg-[var(--brand-accent)] text-white" onclick={() => goto('/me')}>
				Mes commandes
			</Button>
		</div>
	{/if}
</div>
