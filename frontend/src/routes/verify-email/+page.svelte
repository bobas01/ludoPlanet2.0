<script lang="ts">
	import { goto } from '$app/navigation';
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
	import { authError, authLoading, verifyEmail } from '$lib/stores/auth';

	$: token = $page.url.searchParams.get('token') ?? '';

	let verified = false;
	let checked = false;

	onMount(async () => {
		if (!token) {
			checked = true;
			return;
		}
		verified = await verifyEmail(token);
		checked = true;
		if (verified) {
			setTimeout(() => goto('/login?verified=1', { replaceState: true }), 2500);
		}
	});
</script>

<div class="mx-auto max-w-md text-center">
	{#if !checked}
		<p class="text-slate-600">Vérification de votre adresse e-mail...</p>
		{#if $authLoading}
			<p class="mt-2 text-sm text-slate-500">Veuillez patienter.</p>
		{/if}
	{:else if !token}
		<h1 class="text-2xl font-bold text-slate-800">Lien invalide</h1>
		<p class="mt-2 text-sm text-slate-600">Ce lien de confirmation est invalide ou manquant.</p>
		<p class="mt-6">
			<a href="/login" class="font-medium text-amber-700 hover:underline">Aller à la connexion</a>
		</p>
	{:else if verified}
		<h1 class="text-2xl font-bold text-slate-800">E-mail confirmée</h1>
		<p class="mt-4 rounded-md border border-green-200 bg-green-50 px-3 py-3 text-sm text-green-800">
			Votre adresse e-mail est confirmée. Vous allez être redirigé vers la page de connexion.
		</p>
		<p class="mt-4 text-sm text-slate-500">Redirection en cours...</p>
	{:else}
		<h1 class="text-2xl font-bold text-slate-800">Lien invalide ou expiré</h1>
		{#if $authError}
			<p class="mt-2 text-sm text-red-600">{$authError.message}</p>
		{:else}
			<p class="mt-2 text-sm text-slate-600">Ce lien a peut-être déjà été utilisé ou a expiré.</p>
		{/if}
		<p class="mt-6">
			<a href="/login" class="font-medium text-amber-700 hover:underline">Aller à la connexion</a>
		</p>
	{/if}
</div>
