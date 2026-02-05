<script lang="ts">
	import { onMount } from 'svelte';
	import favicon from '$lib/assets/favicon.png';
	import Header from '$lib/components/layout/Header.svelte';
	import Footer from '$lib/components/layout/Footer.svelte';
	import CookieConsent from '$lib/components/layout/CookieConsent.svelte';
	import { loadMe } from '$lib/stores/auth';
	import './layout.css';

	let { children } = $props();

	onMount(() => {
		loadMe();
	});
</script>

<svelte:head><link rel="icon" href={favicon} /></svelte:head>
<div class="flex min-h-screen flex-col bg-linear-to-br from-slate-50 via-white to-amber-50/30">
	<Header />
	<main class="mx-auto w-full max-w-7xl flex-1 px-4 py-10 sm:px-6 sm:py-14">
		{@render children()}
	</main>
	<Footer />
	<CookieConsent />
</div>
