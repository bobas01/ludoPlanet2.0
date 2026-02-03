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

	const handleAddToCart = () => {
		addToCart(game, 1);
		showCartAlert = true;
	};

	const goToCart = () => {
		showCartAlert = false;
		goto('/cart');
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

<article class="overflow-hidden rounded-2xl border border-slate-200/80 bg-white shadow-sm">
	<div class="grid gap-0 sm:grid-cols-[1fr_2fr]">
		<div
			class="flex h-48 min-h-64 items-center justify-center bg-linear-to-br from-[color:var(--brand-accent)]/15 via-[color:var(--brand-accent)]/5 to-slate-100 sm:h-full"
		>
			{#if resolvedImageUrl}
				<img class="h-full w-full object-cover" src={resolvedImageUrl} alt={game.name} />
			{:else}
				<span class="text-7xl opacity-80">🎲</span>
			{/if}
		</div>

		<div class="flex flex-col p-6 sm:p-8">
			<h1 class="text-2xl leading-tight font-bold text-slate-800 sm:text-3xl">
				{game.name}
			</h1>

			<div class="mt-4 flex flex-wrap gap-2">
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
						👥 {game.minPlayers}–{game.maxPlayers} joueurs
					</span>
				{/if}
				{#if game.playTime}
					<span
						class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600"
					>
						⏱ {game.playTime} min
					</span>
				{/if}
				{#if game.minAge}
					<span
						class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600"
					>
						À partir de {game.minAge} ans
					</span>
				{/if}
				{#if game.ratingAverage != null}
					<span
						class="inline-flex items-center rounded-full bg-[color:var(--brand-accent)]/15 px-2.5 py-0.5 text-xs font-medium text-[var(--brand-accent)]"
					>
						⭐ {game.ratingAverage}
					</span>
				{/if}
				{#if game.bggRank != null}
					<span
						class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-600"
					>
						Rank BGG #{game.bggRank}
					</span>
				{/if}
			</div>

			{#if game.description}
				<div class="prose prose-slate mt-6 max-w-none text-slate-600">
					<p class="whitespace-pre-wrap">{game.description}</p>
				</div>
			{/if}

			<div class="mt-8 flex flex-wrap items-center gap-4 border-t border-slate-100 pt-6">
				{#if game.priceCents != null}
					<span class="text-2xl font-bold text-[var(--brand-accent)]">
						{formatPrice(game.priceCents)}
					</span>
				{/if}
				<Button
					variant="outline"
					class="border-slate-200 text-[var(--brand-accent)] hover:text-[var(--brand-accent)]"
					onclick={handleAddToCart}
				>
					<img src={iconCharriot} alt="" class="h-4 w-4" aria-hidden="true" />
					Ajouter au panier
				</Button>
				{#if game.complexityAverage != null}
					<span class="text-sm text-slate-500">
						Complexité : {game.complexityAverage}/5
					</span>
				{/if}
				{#if game.usersRated != null}
					<span class="text-sm text-slate-500">
						{game.usersRated} avis
					</span>
				{/if}
			</div>
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
