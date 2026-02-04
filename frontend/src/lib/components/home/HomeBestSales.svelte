<script lang="ts">
	import { api } from '$lib/api';
	import GameCard from '$lib/components/games/GameCard.svelte';
	import type { Game } from '$lib/types/game';
	import { onMount } from 'svelte';

	const FEATURED_COUNT = 8;

	let games = $state<Game[]>([]);
	let loading = $state(true);
	let error = $state<string | null>(null);

	const getRating = (game: Game) =>
		game.ratingAverage != null ? parseFloat(game.ratingAverage) : null;

	const featuredGames = $derived(
		(() => {
			if (games.length === 0) return [];
			const rated = games.filter((g) => getRating(g) != null);
			const best = [...rated].sort((a, b) => (getRating(b) ?? 0) - (getRating(a) ?? 0));
			const worst = [...rated].sort((a, b) => (getRating(a) ?? 0) - (getRating(b) ?? 0));

			const selection = new Map<number, Game>();
			const addFrom = (list: Game[], count: number) => {
				for (const game of list) {
					if (selection.size >= FEATURED_COUNT || count <= 0) break;
					if (!selection.has(game.bggId)) {
						selection.set(game.bggId, game);
						count -= 1;
					}
				}
			};

			addFrom(best, 4);
			addFrom(worst, 4);

			if (selection.size < FEATURED_COUNT) {
				const remaining = games.filter((g) => !selection.has(g.bggId));
				const shuffled = [...remaining].sort(() => Math.random() - 0.5);
				addFrom(shuffled, FEATURED_COUNT - selection.size);
			}

			return Array.from(selection.values()).slice(0, FEATURED_COUNT);
		})()
	);

	onMount(async () => {
		try {
			const response = await api.get<{ games: Game[] }>('/games');
			games = response.data.games ?? [];
		} catch (e) {
			error = e instanceof Error ? e.message : 'Erreur lors du chargement des jeux.';
		} finally {
			loading = false;
		}
	});
</script>

<section class="px-4 pt-10">
	<div class="mb-6 flex flex-col gap-2 text-center">
		<h2 class="text-2xl font-bold text-[var(--brand-accent)] sm:text-3xl">Meilleures ventes</h2>
	</div>

	{#if loading}
		<p class="text-center text-sm text-slate-500">Chargement des jeux…</p>
	{:else if error}
		<p class="text-center text-sm text-red-600">{error}</p>
	{:else}
		<ul
			class="grid grid-cols-1 items-stretch gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3 xl:grid-cols-4"
		>
			{#each featuredGames as game (game.bggId)}
				<li class="h-full">
					<GameCard {game} />
				</li>
			{/each}
		</ul>
	{/if}
</section>
