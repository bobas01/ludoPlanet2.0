<script lang="ts">
	import { page } from '$app/stores';
	import { api } from '$lib/api';
	import GameDetailBackLink from '$lib/components/games/GameDetailBackLink.svelte';
	import GameDetailCard from '$lib/components/games/GameDetailCard.svelte';
	import type { Game } from '$lib/types/game';

	let game = $state<Game | null>(null);
	let loading = $state(true);
	let error = $state<string | null>(null);

	$effect(() => {
		const id = $page.params.id;
		if (!id) return;
		loading = true;
		error = null;
		game = null;
		api
			.get<{ game: Game }>(`/games/${id}`)
			.then((res) => {
				game = res.data.game;
			})
			.catch((e) => {
				error = e instanceof Error ? e.message : 'Jeu introuvable.';
			})
			.finally(() => {
				loading = false;
			});
	});
</script>

<div class="max-w-4xl mx-auto">
	<GameDetailBackLink />

	{#if loading}
		<div class="flex items-center justify-center py-24">
			<div
				class="w-10 h-10 border-2 border-amber-500/30 border-t-amber-500 rounded-full animate-spin"
			></div>
		</div>
	{:else if error}
		<div class="rounded-2xl bg-red-50 border border-red-100 px-6 py-4 text-red-700">
			{error}
		</div>
	{:else if game}
		<GameDetailCard {game} />
	{/if}
</div>
