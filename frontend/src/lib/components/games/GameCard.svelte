<script lang="ts">
	import { goto } from '$app/navigation';
	import { BASE_URL } from '$lib/api';
	import iconCharriot from '$lib/assets/icons/iconCharriot.png';
	import { Button } from '$lib/components/ui/button';
	import {
		Card,
		CardContent,
		CardDescription,
		CardHeader,
		CardTitle
	} from '$lib/components/ui/card';
	import { addToCart } from '$lib/stores/cart';
	import type { Game } from '$lib/types/game';
	import { formatPrice } from '$lib/utils/games';

	type Props = {
		game: Game;
	};

	let { game }: Props = $props();
	const resolveImageUrl = (url: string | null) => {
		if (!url) return null;
		const normalized = url.startsWith('/images/categories/') ? url.replace(/\.svg$/, '.png') : url;
		return normalized.startsWith('http') ? normalized : `${BASE_URL}${normalized}`;
	};

	const imageUrl = $derived(
		game.primaryImageUrl ??
			game.images?.find((img) => img.isPrimary)?.url ??
			game.images?.[0]?.url ??
			null
	);
	const resolvedImageUrl = $derived(resolveImageUrl(imageUrl));

	let showCartAlert = $state(false);

	const handleAddToCart = (event: Event) => {
		event.preventDefault();
		addToCart(game, 1);
		showCartAlert = true;
	};

	const goToCart = () => {
		showCartAlert = false;
		goto('/card');
	};

	$effect(() => {
		if (!showCartAlert) {
			return;
		}
		const previousOverflow = document.body.style.overflow;
		document.body.style.overflow = 'hidden';
		return () => {
			document.body.style.overflow = previousOverflow;
		};
	});
</script>

<article
	class="group flex h-full flex-col overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:border-[var(--brand-accent)]/40 hover:shadow-lg"
>
	<a href="/games/{game.bggId}" class="block">
		<div
			class="relative flex h-36 items-center justify-center overflow-hidden bg-linear-to-br from-[color:var(--brand-accent)]/15 via-[color:var(--brand-accent)]/5 to-slate-100 sm:h-40"
		>
			{#if resolvedImageUrl}
				<img
					class="absolute inset-0 h-full w-full object-cover"
					src={resolvedImageUrl}
					alt={game.name}
					loading="lazy"
				/>
				<div
					class="absolute inset-0 bg-gradient-to-t from-slate-900/40 via-transparent to-transparent"
				></div>
			{:else}
				<div
					class="absolute inset-0 opacity-40 transition-opacity group-hover:opacity-60"
					style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23c45318\' fill-opacity=\'0.15\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"
				></div>
				<span class="relative text-5xl opacity-80 select-none sm:text-6xl" aria-hidden="true"
					>🎲</span
				>
			{/if}
		</div>
	</a>

	<div class="flex flex-1 flex-col p-4 sm:p-5">
		<a href="/games/{game.bggId}" class="block">
			<h2
				class="line-clamp-2 text-lg leading-snug font-bold text-slate-800 transition-colors group-hover:text-[var(--brand-accent)]"
			>
				{game.name}
			</h2>
		</a>

		<div class="mt-3 flex flex-wrap gap-2">
			{#if game.yearPublished}
				<span
					class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600"
				>
					{game.yearPublished}
				</span>
			{/if}
			{#if game.minPlayers != null && game.maxPlayers != null}
				<span
					class="inline-flex items-center rounded-full bg-[color:var(--brand-accent)]/10 px-2.5 py-0.5 text-xs font-medium text-[var(--brand-accent)]"
				>
					👥 {game.minPlayers}–{game.maxPlayers}
				</span>
			{/if}
			{#if game.playTime}
				<span
					class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600"
				>
					⏱ {game.playTime} min
				</span>
			{/if}
			{#if game.ratingAverage != null}
				<span
					class="inline-flex items-center rounded-full bg-[color:var(--brand-accent)]/15 px-2.5 py-0.5 text-xs font-medium text-[var(--brand-accent)]"
				>
					⭐ {game.ratingAverage}
				</span>
			{/if}
		</div>

		<div class="mt-4 flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
			{#if game.priceCents != null}
				<span class="text-lg font-bold text-[var(--brand-accent)]">
					{formatPrice(game.priceCents)}
				</span>
			{:else}
				<span class="text-sm text-slate-400">—</span>
			{/if}
			<Button
				variant="outline"
				class="border-slate-200 text-[var(--brand-accent)] hover:text-[var(--brand-accent)]"
				onclick={handleAddToCart}
			>
				<img src={iconCharriot} alt="" class="h-4 w-4" aria-hidden="true" />
				Ajouter au panier
			</Button>
		</div>
	</div>
</article>

{#if showCartAlert}
	<div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 p-4">
		<Card class="w-full max-w-sm">
			<CardHeader>
				<CardTitle>Produit ajouté au panier</CardTitle>
				<CardDescription>Souhaitez-vous aller au panier ?</CardDescription>
			</CardHeader>
			<CardContent class="flex flex-col gap-3">
				<Button
					class="bg-[var(--brand-accent)] text-white hover:bg-[var(--brand-accent)]/90"
					onclick={goToCart}
				>
					Aller au panier
				</Button>
				<Button variant="outline" onclick={() => (showCartAlert = false)}>
					Continuer mes achats
				</Button>
			</CardContent>
		</Card>
	</div>
{/if}
