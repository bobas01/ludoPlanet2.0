<script lang="ts">
	import { goto } from '$app/navigation';

	function backToList() {
		goto('/games');
	}
</script>

<button
	onclick={backToList}
	class="cursor-pointer mb-6 text-sm font-medium text-amber-600 hover:text-amber-700 flex items-center gap-1"
>
	← Retour aux jeux
</button>
