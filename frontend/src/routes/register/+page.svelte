<script lang="ts">
	import { authError, authLoading, register } from '$lib/stores/auth';

	let email = '';
	let password = '';
	let firstName = '';
	let lastName = '';
	let address = '';
	let phoneNumber = '';
	let birthDate = '';
	let submitted = false;

	const handleSubmit = async (event: Event) => {
		event.preventDefault();
		submitted = true;
		await register({ email, password, firstName, lastName, address, phoneNumber, birthDate });
	};
</script>

<div class="mx-auto max-w-lg">
	<h1 class="text-2xl font-bold text-slate-800">Inscription</h1>
	<p class="mt-2 text-sm text-slate-600">Créez votre compte pour acheter en ligne.</p>

	<form class="mt-6 space-y-4" onsubmit={handleSubmit}>
		<div class="grid gap-4 sm:grid-cols-2">
			<div>
				<label class="text-sm font-medium text-slate-700" for="register-first-name">Prénom</label>
				<input
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					id="register-first-name"
					bind:value={firstName}
					required
				/>
			</div>
			<div>
				<label class="text-sm font-medium text-slate-700" for="register-last-name">Nom</label>
				<input
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					id="register-last-name"
					bind:value={lastName}
					required
				/>
			</div>
		</div>

		<div>
			<label class="text-sm font-medium text-slate-700" for="register-address">Adresse</label>
			<input
				class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
				id="register-address"
				bind:value={address}
				required
			/>
		</div>

		<div class="grid gap-4 sm:grid-cols-2">
			<div>
				<label class="text-sm font-medium text-slate-700" for="register-phone">Téléphone</label>
				<input
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					id="register-phone"
					bind:value={phoneNumber}
					required
				/>
			</div>
			<div>
				<label class="text-sm font-medium text-slate-700" for="register-birth-date">
					Date de naissance
				</label>
				<input
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					type="date"
					id="register-birth-date"
					bind:value={birthDate}
					required
				/>
			</div>
		</div>

		<div class="grid gap-4 sm:grid-cols-2">
			<div>
				<label class="text-sm font-medium text-slate-700" for="register-email">Email</label>
				<input
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					type="email"
					id="register-email"
					bind:value={email}
					required
				/>
			</div>
			<div>
				<label class="text-sm font-medium text-slate-700" for="register-password"
					>Mot de passe</label
				>
				<input
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					type="password"
					id="register-password"
					bind:value={password}
					required
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

		<button
			type="submit"
			disabled={$authLoading}
			class="w-full rounded-md bg-amber-600 px-4 py-2 text-white hover:bg-amber-700 disabled:opacity-60"
		>
			{#if $authLoading}Création...{:else}Créer un compte{/if}
		</button>
	</form>
</div>
