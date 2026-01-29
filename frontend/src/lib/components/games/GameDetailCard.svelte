<script lang="ts">
	import type { Game } from '$lib/types/game';
	import { formatPrice } from '$lib/utils/games';

	type Props = {
		game: Game;
	};

	let { game }: Props = $props();
</script>

<article class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
	<div class="grid sm:grid-cols-[1fr_2fr] gap-0">
		<div
			class="h-48 sm:h-full min-h-64 bg-linear-to-br from-amber-100 via-amber-50/80 to-slate-100 flex items-center justify-center"
		>
			<span class="text-7xl opacity-80">🎲</span>
		</div>

		<div class="p-6 sm:p-8 flex flex-col">
			<h1 class="text-2xl sm:text-3xl font-bold text-slate-800 leading-tight">
				{game.name}
			</h1>

			<div class="flex flex-wrap gap-2 mt-4">
				{#if game.yearPublished}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
					>
						{game.yearPublished}
					</span>
				{/if}
				{#if game.minPlayers != null && game.maxPlayers != null}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-50 text-amber-700"
					>
						👥 {game.minPlayers}–{game.maxPlayers} joueurs
					</span>
				{/if}
				{#if game.playTime}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
					>
						⏱ {game.playTime} min
					</span>
				{/if}
				{#if game.minAge}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
					>
						À partir de {game.minAge} ans
					</span>
				{/if}
				{#if game.ratingAverage != null}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"
					>
						⭐ {game.ratingAverage}
					</span>
				{/if}
				{#if game.bggRank != null}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
					>
						Rank BGG #{game.bggRank}
					</span>
				{/if}
			</div>

			{#if game.description}
				<div class="mt-6 prose prose-slate max-w-none text-slate-600">
					<p class="whitespace-pre-wrap">{game.description}</p>
				</div>
			{/if}

			<div class="mt-8 pt-6 border-t border-slate-100 flex flex-wrap items-center gap-4">
				{#if game.priceCents != null}
					<span class="text-2xl font-bold text-amber-600">
						{formatPrice(game.priceCents)}
					</span>
				{/if}
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
