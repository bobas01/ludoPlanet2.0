<script lang="ts">
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Alert, AlertDescription, AlertTitle } from '$lib/components/ui/alert';
	import type { Order } from '$lib/types/dashboard';

	type Props = {
		orders: Order[];
		loading: boolean;
		error: string | null;
	};

	let { orders, loading, error }: Props = $props();
</script>

<Card class="border-slate-200 bg-white/80 shadow-sm backdrop-blur">
	<CardHeader>
		<CardTitle>Commandes</CardTitle>
		<CardDescription>Voir les commandes de tous les utilisateurs.</CardDescription>
	</CardHeader>
	<CardContent>
		{#if loading}
			<div class="flex items-center justify-center py-10 text-slate-500">
				Chargement des commandes…
			</div>
		{:else if error}
			<Alert variant="destructive">
				<AlertTitle>Erreur</AlertTitle>
				<AlertDescription>{error}</AlertDescription>
			</Alert>
		{:else if orders.length === 0}
			<p class="text-sm text-slate-500">Aucune commande pour le moment.</p>
		{:else}
			<div class="hidden md:block">
				<div class="overflow-x-auto">
					<table class="min-w-full table-auto text-sm">
						<thead>
							<tr
								class="border-b border-slate-200 bg-slate-50/80 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
							>
								<th class="px-3 py-2">ID</th>
								<th class="px-3 py-2">Client</th>
								<th class="px-3 py-2">Statut</th>
								<th class="px-3 py-2 text-right">Total</th>
								<th class="px-3 py-2">Date</th>
							</tr>
						</thead>
						<tbody>
							{#each orders as order}
								<tr class="border-b border-slate-100 hover:bg-slate-50/60">
									<td class="px-3 py-2 align-middle text-slate-700">#{order.id}</td>
									<td class="px-3 py-2 align-middle text-slate-700">
										{order.shipping.full_name ?? '—'}
									</td>
									<td class="px-3 py-2 align-middle text-slate-700">{order.status}</td>
									<td class="px-3 py-2 text-right align-middle text-slate-700">
										{(order.total_cents / 100).toFixed(2)} €
									</td>
									<td class="px-3 py-2 align-middle text-slate-600">{order.created_at}</td>
								</tr>
							{/each}
						</tbody>
					</table>
				</div>
			</div>

			<div class="space-y-3 md:hidden">
				{#each orders as order}
					<Card class="border-slate-200 bg-white/90 p-3 shadow-sm">
						<CardContent class="p-0">
							<p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
								Commande #{order.id}
							</p>
							<p class="mt-1 text-sm font-semibold text-slate-900">
								{order.shipping.full_name ?? '—'}
							</p>
							<p class="mt-0.5 text-xs text-slate-600">Statut&nbsp;: {order.status}</p>
							<p class="mt-0.5 text-xs text-slate-600">
								Total&nbsp;: {(order.total_cents / 100).toFixed(2)} €
							</p>
							<p class="mt-0.5 text-xs text-slate-500">{order.created_at}</p>
						</CardContent>
					</Card>
				{/each}
			</div>
		{/if}
	</CardContent>
</Card>
