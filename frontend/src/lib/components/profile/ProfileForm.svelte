<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';

	type AuthError = {
		message: string;
		allowedSpecials?: string;
	};

	export let firstName = '';
	export let lastName = '';
	export let address = '';
	export let phoneNumber = '';
	export let birthDate = '';
	export let email = '';
	export let password = '';
	export let submitted = false;
	export let loading = false;
	export let error: AuthError | null = null;
	export let onSubmit: (event: Event) => void = () => {};
	export let onDelete: () => void = () => {};
</script>

<form class="mt-6 space-y-4" onsubmit={onSubmit}>
	<div class="grid gap-4 sm:grid-cols-2">
		<div>
			<Label for="me-first-name">Prénom</Label>
			<Input id="me-first-name" class="mt-1" bind:value={firstName} required />
		</div>
		<div>
			<Label for="me-last-name">Nom</Label>
			<Input id="me-last-name" class="mt-1" bind:value={lastName} required />
		</div>
	</div>

	<div>
		<Label for="me-address">Adresse</Label>
		<Input id="me-address" class="mt-1" bind:value={address} required />
	</div>

	<div class="grid gap-4 sm:grid-cols-2">
		<div>
			<Label for="me-phone">Téléphone</Label>
			<Input id="me-phone" class="mt-1" bind:value={phoneNumber} required />
		</div>
		<div>
			<Label for="me-birth-date">Date de naissance</Label>
			<Input id="me-birth-date" type="date" class="mt-1" bind:value={birthDate} required />
		</div>
	</div>

	<div class="grid gap-4 sm:grid-cols-2">
		<div>
			<Label for="me-email">Email</Label>
			<Input id="me-email" type="email" class="mt-1" bind:value={email} required />
		</div>
		<div>
			<Label for="me-password">Nouveau mot de passe (optionnel)</Label>
			<Input id="me-password" type="password" class="mt-1" bind:value={password} />
		</div>
	</div>

	{#if error && submitted}
		<div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
			<p>{error.message}</p>
			{#if error.allowedSpecials}
				<p class="mt-2">Caractères spéciaux autorisés : {error.allowedSpecials}</p>
			{/if}
		</div>
	{/if}

	<div class="flex flex-col gap-3 sm:flex-row">
		<Button
			type="submit"
			disabled={loading}
			class="bg-amber-600 px-4 py-2 text-white hover:bg-amber-700 disabled:opacity-60"
		>
			{#if loading}Enregistrement...{:else}Enregistrer{/if}
		</Button>
		<Button
			type="button"
			variant="outline"
			class="border-red-300 px-4 py-2 text-red-700 hover:bg-red-50"
			onclick={onDelete}
		>
			Supprimer mon compte
		</Button>
	</div>
</form>
