<script lang="ts">
	import type { Game } from '$lib/types/game';
	import { formatPrice } from '$lib/utils/games';

	type Props = {
		game: Game;
	};

	let { game }: Props = $props();
</script>

<a href="/games/{game.bggId}" class="block h-full cursor-pointer">
	<article
		class="group h-full flex flex-col bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden hover:shadow-xl hover:shadow-amber-500/5 hover:border-amber-200/60 hover:-translate-y-0.5 transition-all duration-300"
	>
		<div
			class="relative h-36 sm:h-40 bg-linear-to-br from-amber-100 via-amber-50/80 to-slate-100 flex items-center justify-center overflow-hidden"
		>
			<div
				class="absolute inset-0 opacity-40 group-hover:opacity-60 transition-opacity"
				style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23d97706\' fill-opacity=\'0.15\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"
			></div>
			<span class="relative text-5xl sm:text-6xl opacity-80 select-none" aria-hidden="true"
				>🎲</span
			>
		</div>

		<div class="p-4 sm:p-5 flex-1 flex flex-col">
			<h2
				class="font-bold text-slate-800 text-lg leading-snug line-clamp-2 group-hover:text-amber-800/90 transition-colors"
			>
				{game.name}
			</h2>

			<div class="flex flex-wrap gap-2 mt-3">
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
						👥 {game.minPlayers}–{game.maxPlayers}
					</span>
				{/if}
				{#if game.playTime}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600"
					>
						⏱ {game.playTime} min
					</span>
				{/if}
				{#if game.ratingAverage != null}
					<span
						class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800"
					>
						⭐ {game.ratingAverage}
					</span>
				{/if}
			</div>

			<div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
				{#if game.priceCents != null}
					<span class="text-lg font-bold text-amber-600">
						{formatPrice(game.priceCents)}
					</span>
				{:else}
					<span class="text-sm text-slate-400">—</span>
				{/if}
				<span
					class="text-xs font-medium text-slate-400 group-hover:text-amber-600 transition-colors"
				>
					Voir →
				</span>
			</div>
		</div>
	</article>
</a>
