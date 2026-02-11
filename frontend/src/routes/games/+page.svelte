<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
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

$effect(() => {
		const url = $page.url;

		const categoryParam = url.searchParams.get('category');
		selectedCategory = categoryParam;

		const sortParam = url.searchParams.get('sort');
		if (sortParam === 'players' || sortParam === 'rating' || sortParam === 'time') {
			sortBy = sortParam;
		}

		const pageParam = url.searchParams.get('page');
		const pageFromUrl = pageParam ? Number.parseInt(pageParam, 10) : 1;
		currentPage = Number.isNaN(pageFromUrl) || pageFromUrl < 1 ? 1 : pageFromUrl;
	});

	let categories = $derived(
		Array.from(new Set(games.flatMap((g) => g.categories ?? []))).sort((a, b) => a.localeCompare(b))
	);

	let filteredAndSortedGames = $derived(
		(() => {
			let list = games;
			if (selectedCategory) {
				list = list.filter((g) => (g.categories ?? []).includes(selectedCategory as string));
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

	let totalPages = $derived(Math.max(1, Math.ceil(filteredAndSortedGames.length / PAGE_SIZE)));
	let paginatedGames = $derived(
		filteredAndSortedGames.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE)
	);

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

	function updateUrl(params: { category?: string | null; sort?: SortOption | null; page?: number }) {
		const url = new URL($page.url);

		if (params.category !== undefined) {
			if (params.category) {
				url.searchParams.set('category', params.category);
			} else {
				url.searchParams.delete('category');
			}
		}

		if (params.sort !== undefined) {
			if (params.sort) {
				url.searchParams.set('sort', params.sort);
			} else {
				url.searchParams.delete('sort');
			}
		}

		if (params.page !== undefined) {
			url.searchParams.set('page', String(params.page));
		}

		const query = url.searchParams.toString();
		goto(query ? `${url.pathname}?${query}` : url.pathname, {
			replaceState: true,
			keepFocus: true,
			noScroll: true
		});
	}

	function handlePrev() {
		const nextPage = Math.max(1, currentPage - 1);
		currentPage = nextPage;
		updateUrl({ page: nextPage });
	}
	function handleNext() {
		const nextPage = Math.min(totalPages, currentPage + 1);
		currentPage = nextPage;
		updateUrl({ page: nextPage });
	}

	function setCategory(cat: string | null) {
		selectedCategory = cat;
		currentPage = 1;
		updateUrl({ category: cat, page: 1 });
	}

	function setSort(option: SortOption) {
		sortBy = option;
		currentPage = 1;
		updateUrl({ sort: option, page: 1 });
	}
</script>

{#if loading}
	<div class="flex items-center justify-center py-24">
		<div class="flex flex-col items-center gap-4">
			<div
				class="h-10 w-10 animate-spin rounded-full border-2 border-amber-500/30 border-t-amber-500"
			></div>
			<p class="text-slate-500">Chargement des jeux…</p>
		</div>
	</div>
{:else if error}
	<div class="rounded-2xl border border-red-100 bg-red-50 px-6 py-4 text-red-700">
		{error}
	</div>
{:else if games.length === 0}
	<div
		class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-500"
	>
		Aucun jeu pour le moment.
	</div>
{:else}
	<GamesFilters
		{categories}
		{selectedCategory}
		{sortBy}
		onCategorySelect={(cat) => setCategory(cat)}
		onSortChange={setSort}
	/>

	{#if filteredAndSortedGames.length === 0}
		<div
			class="rounded-2xl border border-slate-200 bg-slate-50 px-6 py-12 text-center text-slate-500"
		>
			Aucun jeu dans cette catégorie.
		</div>
	{:else}
		<p class="mb-4 text-sm text-slate-500">
			{filteredAndSortedGames.length} jeu(x) — page {currentPage} / {totalPages}
		</p>
		<ul
			class="grid grid-cols-1 items-stretch gap-6 sm:grid-cols-2 sm:gap-8 lg:grid-cols-3 xl:grid-cols-4"
		>
			{#each paginatedGames as game (game.bggId)}
				<li class="h-full">
					<GameCard {game} />
				</li>
			{/each}
		</ul>

		<GamesPagination {currentPage} {totalPages} onPrev={handlePrev} onNext={handleNext} />
	{/if}
{/if}
