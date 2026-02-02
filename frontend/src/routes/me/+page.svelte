<script lang="ts">
	import { authUser, loadMe } from '$lib/stores/auth';
	import { onMount } from 'svelte';

	onMount(() => {
		loadMe();
	});
</script>

{#if $authUser}
	<div class="mx-auto max-w-xl">
		<h1 class="text-2xl font-bold text-slate-800">Mon profil</h1>
		<div class="mt-6 space-y-2 text-sm text-slate-700">
			<p><strong>Email :</strong> {$authUser.email}</p>
			<p><strong>Prénom :</strong> {$authUser.firstName}</p>
			<p><strong>Nom :</strong> {$authUser.lastName}</p>
			<p><strong>Adresse :</strong> {$authUser.address}</p>
			<p><strong>Téléphone :</strong> {$authUser.phoneNumber}</p>
			<p><strong>Date de naissance :</strong> {$authUser.birthDate}</p>
		</div>
	</div>
{:else}
	<p class="text-sm text-slate-600">Vous devez être connecté.</p>
{/if}
