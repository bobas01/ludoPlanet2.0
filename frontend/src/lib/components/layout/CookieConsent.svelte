<script lang="ts">
	import { onMount } from 'svelte';
	import { Button } from '$lib/components/ui/button';
	import { getCookieConsent, setCookieConsent } from '$lib/cookies';

	let visible = $state(false);
	let mounted = $state(false);

	onMount(() => {
		const consent = getCookieConsent();
		visible = consent === null;
		mounted = true;
	});

	function acceptAll() {
		setCookieConsent('all');
		visible = false;
	}

	function acceptEssentialOnly() {
		setCookieConsent('essential');
		visible = false;
	}
</script>

{#if mounted && visible}
	<div
		class="fixed bottom-0 left-0 right-0 z-50 border-t border-slate-200 bg-white/95 px-4 py-4 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] backdrop-blur-sm sm:px-6"
		role="dialog"
		aria-label="Préférences cookies"
	>
		<div class="mx-auto flex max-w-4xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
			<p class="text-sm text-slate-700">
				Nous utilisons des cookies pour le bon fonctionnement du site (session, panier, préférences).
				Tu peux tout accepter ou uniquement les essentiels.
				<a href="/cookies" class="font-medium underline hover:text-[var(--brand-accent)]">En savoir plus</a>
			</p>
			<div class="flex shrink-0 flex-wrap gap-2">
				<Button type="button" variant="outline" size="sm" onclick={acceptEssentialOnly}>
					Essentiels uniquement
				</Button>
				<Button
					type="button"
					size="sm"
					class="bg-[var(--brand-accent)] hover:opacity-90"
					onclick={acceptAll}
				>
					Tout accepter
				</Button>
			</div>
		</div>
	</div>
{/if}
