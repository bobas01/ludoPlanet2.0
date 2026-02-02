<script lang="ts">
	import favicon from '$lib/assets/favicon.png';
	import Header from '$lib/components/layout/Header.svelte';
	import './layout.css';
	import Footer from '$lib/components/layout/Footer.svelte';

	let { children } = $props();
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>
<div class="min-h-screen bg-linear-to-br from-slate-50 via-white to-amber-50/30">
	<Header />
	<div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14">
		{@render children()}
	</div>
	<Footer />
</div>
