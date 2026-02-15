<script lang="ts">
	import HomeAbout from '$lib/components/home/HomeAbout.svelte';
	import HomeBestSales from '$lib/components/home/HomeBestSales.svelte';
	import HomeNextEvent from '$lib/components/home/HomeNextEvent.svelte';
	import { page } from '$app/stores';
	import { replaceState } from '$app/navigation';

	let showRegisteredMessage = $state(false);
	$effect(() => {
		if ($page.url.searchParams.get('registered') === '1') {
			showRegisteredMessage = true;
			replaceState($page.url.pathname, $page.url.pathname);
		}
	});
</script>

{#if showRegisteredMessage}
	<div
		class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-center text-sm text-green-800"
		role="alert"
	>
		<strong>Inscription réussie.</strong> Consultez vos e-mails pour valider votre adresse. Une fois
		validée, vous pourrez vous connecter.
	</div>
{/if}

<div class="space-y-16 pb-16">
	<HomeBestSales />
	<HomeAbout />
	<HomeNextEvent />
</div>
