<script lang="ts">
	import { api, BASE_URL } from '$lib/api';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent } from '$lib/components/ui/card';

	export let active = false;

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
		shipping: {
			full_name: string | null;
			address_line1: string | null;
			address_line2: string | null;
			postal_code: string | null;
			city: string | null;
			country: string | null;
			phone: string | null;
		};
		items: OrderItem[];
		created_at: string;
		updated_at: string;
	};

	let orders: Order[] = [];
	let ordersLoading = false;
	let ordersError = '';
	let ordersLoaded = false;
	let ordersPage = 1;
	let ordersLimit = 10;
	let ordersSort = 'desc';
	let ordersStatus = '';
	let ordersHasNext = false;
	let openedOrderId: number | null = null;

	const formatMoney = (cents: number, currency: string) =>
		new Intl.NumberFormat('fr-FR', { style: 'currency', currency }).format(cents / 100);

	const resolveImageUrl = (url?: string | null) => {
		if (!url) return null;
		if (url.startsWith('http://') || url.startsWith('https://')) return url;
		return `${BASE_URL}${url.startsWith('/') ? '' : '/'}${url}`;
	};

	const statusLabel = (status: string) => {
		switch (status) {
			case 'pending':
				return 'En attente';
			case 'paid':
				return 'Payée';
			case 'pickup':
				return 'À récupérer en magasin';
			case 'cancelled':
				return 'Annulée';
			default:
				return status;
		}
	};

	const toggleOrderDetails = (orderId: number) => {
		openedOrderId = openedOrderId === orderId ? null : orderId;
	};

	const loadOrders = async () => {
		ordersLoading = true;
		ordersError = '';
		try {
			const params = new URLSearchParams({
				page: String(ordersPage),
				limit: String(ordersLimit),
				sort: ordersSort
			});
			if (ordersStatus) params.set('status', ordersStatus);

			const { data } = await api.get<{ orders: Order[] }>(`/api/my-orders?${params.toString()}`);
			orders = data.orders;
			ordersHasNext = data.orders.length === ordersLimit;
			ordersLoaded = true;
		} catch {
			ordersError = 'Erreur lors du chargement des commandes.';
		} finally {
			ordersLoading = false;
		}
	};

	const handleOrdersFilter = async (event: Event) => {
		event.preventDefault();
		ordersPage = 1;
		await loadOrders();
	};

	const handlePrevPage = async () => {
		if (ordersPage <= 1) return;
		ordersPage -= 1;
		await loadOrders();
	};

	const handleNextPage = async () => {
		if (!ordersHasNext) return;
		ordersPage += 1;
		await loadOrders();
	};

	$: if (active && !ordersLoaded && !ordersLoading) {
		loadOrders();
	}
</script>

<div class="mt-6 space-y-4">
	<form class="flex flex-wrap items-end gap-3" onsubmit={handleOrdersFilter}>
		<div>
			<label class="text-xs font-medium text-slate-600" for="orders-status">Statut</label>
			<select
				id="orders-status"
				class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
				bind:value={ordersStatus}
			>
				<option value="">Tous</option>
				<option value="pending">pending</option>
				<option value="paid">paid</option>
				<option value="pickup">pickup</option>
				<option value="cancelled">cancelled</option>
			</select>
		</div>
		<div>
			<label class="text-xs font-medium text-slate-600" for="orders-sort">Tri</label>
			<select
				id="orders-sort"
				class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
				bind:value={ordersSort}
			>
				<option value="desc">Plus récent</option>
				<option value="asc">Plus ancien</option>
			</select>
		</div>
		<div>
			<label class="text-xs font-medium text-slate-600" for="orders-limit">Par page</label>
			<select
				id="orders-limit"
				class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
				bind:value={ordersLimit}
			>
				<option value={5}>5</option>
				<option value={10}>10</option>
				<option value={20}>20</option>
			</select>
		</div>
		<Button
			type="submit"
			variant="outline"
			class="border-slate-300 px-4 py-2 text-sm text-slate-700"
		>
			Appliquer
		</Button>
	</form>

	{#if ordersLoading}
		<p class="text-sm text-slate-600">Chargement des commandes...</p>
	{:else if ordersError}
		<p class="text-sm text-red-600">{ordersError}</p>
	{:else if orders.length === 0}
		<p class="text-sm text-slate-600">Aucune commande.</p>
	{:else}
		<div class="space-y-4">
			{#each orders as order}
				<Card class="py-0">
					<CardContent class="px-4 py-4">
						<button
							type="button"
							class="flex w-full flex-wrap items-center justify-between gap-2 text-left"
							onclick={() => toggleOrderDetails(order.id)}
						>
							<div>
								<p class="text-sm font-semibold text-slate-800">Commande #{order.id}</p>
								<p class="text-xs text-slate-500">
									Créée le {order.created_at} · Statut: {statusLabel(order.status)}
								</p>
							</div>
							<p class="text-sm font-semibold text-slate-800">
								{formatMoney(order.total_cents, order.currency)}
							</p>
						</button>

						{#if openedOrderId === order.id}
							<div class="mt-3 space-y-3 border-t border-slate-100 pt-3">
								<div>
									<p class="text-xs font-semibold text-slate-500 uppercase">Articles</p>
									<ul class="mt-2 space-y-2 text-sm text-slate-700">
										{#each order.items as item}
											<li class="flex items-center gap-3">
												{#if resolveImageUrl(item.game_image_url)}
													<img
														src={resolveImageUrl(item.game_image_url) as string}
														alt={item.game_name || `Jeu #${item.game_id}`}
														class="h-10 w-10 rounded-md object-cover"
														loading="lazy"
													/>
												{:else}
													<div
														class="flex h-10 w-10 items-center justify-center rounded-md bg-slate-100 text-xs text-slate-400"
													>
														N/A
													</div>
												{/if}
												<div>
													<p class="font-medium">{item.game_name || `Jeu #${item.game_id}`}</p>
													<p class="text-xs text-slate-500">
														x{item.quantity} · {formatMoney(item.unit_price_cents, order.currency)}
													</p>
												</div>
											</li>
										{/each}
									</ul>
								</div>
								<div class="text-sm text-slate-700">
									<p class="text-xs font-semibold text-slate-500 uppercase">Retrait</p>
									<p class="mt-1">{order.shipping.full_name || 'Nom non renseigné'}</p>
									<p>
										{order.shipping.address_line1 || ''}
										{order.shipping.address_line2 || ''}
									</p>
									<p>
										{order.shipping.postal_code || ''}
										{order.shipping.city || ''}{' '}
										{order.shipping.country || ''}
									</p>
									{#if order.shipping.phone}
										<p>Tél: {order.shipping.phone}</p>
									{/if}
								</div>
							</div>
						{/if}
					</CardContent>
				</Card>
			{/each}
		</div>

		<div class="mt-4 flex items-center justify-between text-sm text-slate-600">
			<Button
				type="button"
				variant="outline"
				class="border-slate-300 px-3 py-1 disabled:opacity-50"
				onclick={handlePrevPage}
				disabled={ordersPage <= 1}
			>
				Précédent
			</Button>
			<span>Page {ordersPage}</span>
			<Button
				type="button"
				variant="outline"
				class="border-slate-300 px-3 py-1 disabled:opacity-50"
				onclick={handleNextPage}
				disabled={!ordersHasNext}
			>
				Suivant
			</Button>
		</div>
	{/if}
</div>
