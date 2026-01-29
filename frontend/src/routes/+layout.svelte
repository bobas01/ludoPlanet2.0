<script lang="ts">
	import favicon from '$lib/assets/favicon.svg';
	import GamesHeader from '$lib/components/games/GamesHeader.svelte';
	import './layout.css';

	let { children } = $props();
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>
<div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-amber-50/30">
	<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
		<GamesHeader />
		{@render children()}
	</div>
</div>
