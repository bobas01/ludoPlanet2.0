<script lang="ts">
	import { labelCategory } from '$lib/utils/games';

	type SortOption = 'players' | 'rating' | 'time';

	type Props = {
		categories: string[];
		selectedCategory: string | null;
		sortBy: SortOption;
		onCategorySelect: (cat: string | null) => void;
		onSortChange: (sort: SortOption) => void;
	};

	let { categories, selectedCategory, sortBy, onCategorySelect, onSortChange }: Props = $props();
</script>

<div
	class="mb-8 flex flex-col gap-4 rounded-xl border border-slate-200/80 bg-white/80 p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between"
>
	<div class="flex flex-wrap items-center gap-2">
		<span class="text-sm font-medium text-slate-600">Catégorie :</span>
		<button
			type="button"
			onclick={() => onCategorySelect(null)}
			class="cursor-pointer rounded-full px-3 py-1.5 text-sm font-medium transition-colors {selectedCategory ===
			null
				? 'bg-[var(--brand-accent)] text-white'
				: 'bg-slate-100 text-slate-600 hover:bg-slate-200'}"
		>
			Toutes
		</button>
		{#each categories as cat}
			<button
				type="button"
				onclick={() => onCategorySelect(cat)}
				class="cursor-pointer rounded-full px-3 py-1.5 text-sm font-medium transition-colors {selectedCategory ===
				cat
					? 'bg-[var(--brand-accent)] text-white'
					: 'bg-slate-100 text-slate-600 hover:bg-slate-200'}"
			>
				{labelCategory(cat)}
			</button>
		{/each}
	</div>
	<div class="flex items-center gap-2">
		<label for="sort" class="text-sm font-medium text-slate-600">Trier par :</label>
		<select
			id="sort"
			value={sortBy}
			onchange={(e) => onSortChange((e.currentTarget.value as SortOption) || 'rating')}
			class="cursor-pointer rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm focus:border-[var(--brand-accent)] focus:ring-1 focus:ring-[var(--brand-accent)] focus:outline-none"
		>
			<option value="rating">Note</option>
			<option value="players">Nombre de joueurs</option>
			<option value="time">Temps de jeu</option>
		</select>
	</div>
</div>
