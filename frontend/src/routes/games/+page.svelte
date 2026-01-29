<script lang="ts">
	import { api } from '$lib/api';
	import GameCard from '$lib/components/games/GameCard.svelte';
	import GamesFilters from '$lib/components/games/GamesFilters.svelte';
	import GamesPagination from '$lib/components/games/GamesPagination.svelte';
	import type { Game } from '$lib/types/game';
	import { onMount } from 'svelte';

	const PAGE_SIZE = 12;
	type SortOption = 'players' | 'rating' | 'time';

	let games = $state<Game[]>([]);
	let loading = $state(true);
	let error = $state<string | null>(null);
	let selectedCategory = $state<string | null>(null);
	let sortBy = $state<SortOption>('rating');
	let currentPage = $state(1);

	let categories = $derived(
		Array.from(new Set(games.flatMap((g) => g.categories ?? []))).sort((a, b) =>
			a.localeCompare(b)
		)
	);

	let filteredAndSortedGames = $derived(
		(() => {
			let list = games;
			if (selectedCategory) {
				list = list.filter((g) =>
					(g.categories ?? []).includes(selectedCategory as string)
				);
			}
			return [...list].sort((a, b) => {
				if (sortBy === 'players') {
					const maxA = a.maxPlayers ?? a.minPlayers ?? 0;
					const maxB = b.maxPlayers ?? b.minPlayers ?? 0;
					return maxB - maxA;
				}
				if (sortBy === 'rating') {
					const rA = a.ratingAverage != null ? parseFloat(a.ratingAverage) : 0;
					const rB = b.ratingAverage != null ? parseFloat(b.ratingAverage) : 0;
					return rB - rA;
				}
				if (sortBy === 'time') {
					const tA = a.playTime ?? 0;
					const tB = b.playTime ?? 0;
					return tB - tA;
				}
				return 0;
			});
		})()
	);

	let totalPages = $derived(
		Math.max(1, Math.ceil(filteredAndSortedGames.length / PAGE_SIZE))
	);
	let paginatedGames = $derived(
		filteredAndSortedGames.slice(
			(currentPage - 1) * PAGE_SIZE,
			currentPage * PAGE_SIZE
		)
	);

	$effect(() => {
		selectedCategory;
		sortBy;
		currentPage = 1;
	});
	$effect(() => {
		if (currentPage > totalPages) {
			currentPage = totalPages;
		}
	});

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

	function handlePrev() {
		currentPage = Math.max(1, currentPage - 1);
	}
	function handleNext() {
		currentPage = Math.min(totalPages, currentPage + 1);
	}
</script>

{#if loading}
	<div class="flex items-center justify-center py-24">
		<div class="flex flex-col items-center gap-4">
			<div
				class="w-10 h-10 border-2 border-amber-500/30 border-t-amber-500 rounded-full animate-spin"
			></div>
			<p class="text-slate-500">Chargement des jeux…</p>
		</div>
	</div>
{:else if error}
	<div class="rounded-2xl bg-red-50 border border-red-100 px-6 py-4 text-red-700">
		{error}
	</div>
{:else if games.length === 0}
	<div
		class="rounded-2xl bg-slate-50 border border-slate-200 px-6 py-12 text-center text-slate-500"
	>
		Aucun jeu pour le moment.
	</div>
{:else}
	<GamesFilters
		{categories}
		{selectedCategory}
		{sortBy}
		onCategorySelect={(cat) => (selectedCategory = cat)}
		onSortChange={(s) => (sortBy = s)}
	/>

	{#if filteredAndSortedGames.length === 0}
		<div
			class="rounded-2xl bg-slate-50 border border-slate-200 px-6 py-12 text-center text-slate-500"
		>
			Aucun jeu dans cette catégorie.
		</div>
	{:else}
		<p class="mb-4 text-sm text-slate-500">
			{filteredAndSortedGames.length} jeu(x) — page {currentPage} / {totalPages}
		</p>
		<ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 sm:gap-8">
			{#each paginatedGames as game (game.bggId)}
				<li>
					<GameCard {game} />
				</li>
			{/each}
		</ul>

		<GamesPagination
			{currentPage}
			{totalPages}
			onPrev={handlePrev}
			onNext={handleNext}
		/>
	{/if}
{/if}
