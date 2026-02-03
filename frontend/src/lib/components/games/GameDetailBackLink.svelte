<script lang="ts">
	import { goto } from '$app/navigation';

	function backToList() {
		goto('/games');
	}
</script>

<button
	onclick={backToList}
	class="mb-6 flex cursor-pointer items-center gap-1 text-sm font-medium text-[var(--brand-accent)] hover:text-[var(--brand-accent)]/90"
>
	← Retour aux jeux
</button>
