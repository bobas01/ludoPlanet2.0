<script lang="ts">
	import { authError, authLoading, authUser, deleteMe, loadMe, updateMe } from '$lib/stores/auth';
	import { onMount } from 'svelte';

	onMount(() => {
		loadMe();
	});

	let firstName = '';
	let lastName = '';
	let address = '';
	let phoneNumber = '';
	let birthDate = '';
	let email = '';
	let password = '';
	let submitted = false;

	$: if ($authUser) {
		firstName = $authUser.firstName;
		lastName = $authUser.lastName;
		address = $authUser.address;
		phoneNumber = $authUser.phoneNumber;
		birthDate = $authUser.birthDate;
		email = $authUser.email;
	}

	const handleUpdate = async (event: Event) => {
		event.preventDefault();
		submitted = true;
		await updateMe({
			firstName,
			lastName,
			address,
			phoneNumber,
			birthDate,
			email,
			password: password || undefined
		});
		password = '';
	};

	const handleDelete = async () => {
		const confirmed = confirm('Confirmer la suppression du compte ? Cette action est définitive.');
		if (!confirmed) return;
		await deleteMe();
	};
</script>

{#if $authUser}
	<div class="mx-auto max-w-xl">
		<h1 class="text-2xl font-bold text-slate-800">Mon profil</h1>
		<form class="mt-6 space-y-4" onsubmit={handleUpdate}>
			<div class="grid gap-4 sm:grid-cols-2">
				<div>
					<label class="text-sm font-medium text-slate-700" for="me-first-name">Prénom</label>
					<input
						id="me-first-name"
						class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
						bind:value={firstName}
						required
					/>
				</div>
				<div>
					<label class="text-sm font-medium text-slate-700" for="me-last-name">Nom</label>
					<input
						id="me-last-name"
						class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
						bind:value={lastName}
						required
					/>
				</div>
			</div>

			<div>
				<label class="text-sm font-medium text-slate-700" for="me-address">Adresse</label>
				<input
					id="me-address"
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					bind:value={address}
					required
				/>
			</div>

			<div class="grid gap-4 sm:grid-cols-2">
				<div>
					<label class="text-sm font-medium text-slate-700" for="me-phone">Téléphone</label>
					<input
						id="me-phone"
						class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
						bind:value={phoneNumber}
						required
					/>
				</div>
				<div>
					<label class="text-sm font-medium text-slate-700" for="me-birth-date"
						>Date de naissance</label
					>
					<input
						id="me-birth-date"
						type="date"
						class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
						bind:value={birthDate}
						required
					/>
				</div>
			</div>

			<div class="grid gap-4 sm:grid-cols-2">
				<div>
					<label class="text-sm font-medium text-slate-700" for="me-email">Email</label>
					<input
						id="me-email"
						type="email"
						class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
						bind:value={email}
						required
					/>
				</div>
				<div>
					<label class="text-sm font-medium text-slate-700" for="me-password">
						Nouveau mot de passe (optionnel)
					</label>
					<input
						id="me-password"
						type="password"
						class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
						bind:value={password}
					/>
				</div>
			</div>

			{#if $authError && submitted}
				<div class="rounded-md border border-red-200 bg-red-50 p-3 text-sm text-red-700">
					<p>{$authError.message}</p>
					{#if $authError.allowedSpecials}
						<p class="mt-2">Caractères spéciaux autorisés : {$authError.allowedSpecials}</p>
					{/if}
				</div>
			{/if}

			<div class="flex flex-col gap-3 sm:flex-row">
				<button
					type="submit"
					disabled={$authLoading}
					class="rounded-md bg-amber-600 px-4 py-2 text-white hover:bg-amber-700 disabled:opacity-60"
				>
					{#if $authLoading}Enregistrement...{:else}Enregistrer{/if}
				</button>
				<button
					type="button"
					class="rounded-md border border-red-300 px-4 py-2 text-red-700 hover:bg-red-50"
					onclick={handleDelete}
				>
					Supprimer mon compte
				</button>
			</div>
		</form>
	</div>
{:else}
	<p class="text-sm text-slate-600">Vous devez être connecté.</p>
{/if}
