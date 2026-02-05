<script lang="ts">
	import { Card, CardContent, CardHeader, CardTitle } from '$lib/components/ui/card';

	const types = [
		{
			title: 'Cookies essentiels',
			required: true,
			description:
				"Ils sont nécessaires au fonctionnement du site : session, préférences de langue, panier, authentification. Sans eux, certaines fonctionnalités ne sont pas disponibles. Nous n'utilisons pas de prestataires tiers pour ces cookies."
		},
		{
			title: 'Cookies d’analyse (analytics)',
			required: false,
			description:
				"Ils permettent de mesurer l'audience (nombre de visites, pages vues). Pour l'instant, ce site n'utilise pas d'outils d'analyse tiers (type Google Analytics). Si nous en ajoutons plus tard, tu pourras les refuser en choisissant « Essentiels uniquement » dans la bannière cookies."
		},
		{
			title: 'Cookies publicitaires / partenaires',
			required: false,
			description:
				"Ils servent à afficher des publicités ciblées ou à suivre ton parcours sur d'autres sites. Ce site n'utilise actuellement aucun cookie publicitaire ni partenaire (réseaux sociaux, régies publicitaires). La bannière te permet de refuser à l'avance ces catégories si nous les activons un jour."
		}
	];
</script>

<svelte:head>
	<title>Politique de cookies</title>
</svelte:head>

<div class="mx-auto max-w-3xl space-y-8">
	<div>
		<h1 class="font-title text-3xl font-bold text-slate-900">Politique de cookies</h1>
		<p class="mt-2 text-slate-600">
			Cette page décrit les types de cookies utilisés sur le site et comment gérer tes préférences.
		</p>
	</div>

	<p class="text-sm text-slate-700">
		Un cookie est un petit fichier enregistré par ton navigateur. Nous utilisons des cookies pour faire
		fonctionner le site (connexion, panier, préférences). Tu peux à tout moment choisir d’accepter tout,
		ou uniquement les cookies essentiels, via la bannière affichée en bas de la page (ou en supprimant
		le cookie <code class="rounded bg-slate-100 px-1 py-0.5 text-xs">cookie_consent</code> pour la faire réapparaître).
	</p>

	<div class="space-y-4">
		<h2 class="font-title text-xl font-semibold text-slate-900">Types de cookies</h2>
		{#each types as item}
			<Card class="border-slate-200">
				<CardHeader class="pb-2">
					<CardTitle class="flex items-center gap-2 text-base">
						{item.title}
						{#if item.required}
							<span class="rounded bg-slate-100 px-2 py-0.5 text-xs font-normal text-slate-600">
								Obligatoires
							</span>
						{:else}
							<span class="rounded bg-amber-100 px-2 py-0.5 text-xs font-normal text-amber-800">
								Optionnels
							</span>
						{/if}
					</CardTitle>
				</CardHeader>
				<CardContent class="pt-0">
					<p class="text-sm text-slate-700">{item.description}</p>
				</CardContent>
			</Card>
		{/each}
	</div>

	<p class="text-sm text-slate-600">
		Pour modifier ton choix, supprime le cookie de consentement dans les paramètres de ton navigateur,
		puis rafraîchis la page : la bannière cookies réapparaîtra.
	</p>
</div>
