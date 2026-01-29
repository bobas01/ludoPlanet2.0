<script lang="ts">
	type Props = {
		currentPage: number;
		totalPages: number;
		onPrev: () => void;
		onNext: () => void;
	};

	let { currentPage, totalPages, onPrev, onNext }: Props = $props();
</script>

<nav class="mt-10 flex flex-wrap items-center justify-center gap-2" aria-label="Pagination">
	<button
		type="button"
		onclick={onPrev}
		disabled={currentPage <= 1}
		class="cursor-pointer rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
	>
		← Précédent
	</button>
	<span class="px-3 py-2 text-sm text-slate-600">
		Page {currentPage} / {totalPages}
	</span>
	<button
		type="button"
		onclick={onNext}
		disabled={currentPage >= totalPages}
		class="cursor-pointer rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
	>
		Suivant →
	</button>
</nav>
