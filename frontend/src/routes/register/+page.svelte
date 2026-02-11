<script lang="ts">
	import { authError, authLoading, register } from '$lib/stores/auth';
	import { goto } from '$app/navigation';

	let email = '';
	let password = '';
	let submitted = false;

	const specialPattern = /[!@#$%^&*()_+\-=\[\]{};:'",.<>\/?\\|`~]/;

	$: hasMinLength = password.length >= 12;
	$: hasUppercase = /[A-Z]/.test(password);
	$: hasSpecial = specialPattern.test(password);

	const handleSubmit = async (event: Event) => {
		event.preventDefault();
		submitted = true;
		const success = await register({ email, password });
		if (success) {
			// Après inscription, rediriger vers le profil avec un message d'aide pour le paiement
			await goto('/me?from=register');
		}
	};
</script>

<div class="mx-auto max-w-lg">
	<h1 class="text-2xl font-bold text-slate-800">Inscription</h1>
	<p class="mt-2 text-sm text-slate-600">Créez votre compte pour acheter en ligne.</p>

	<form class="mt-6 space-y-4" onsubmit={handleSubmit}>
		<div class="space-y-4">
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
					class="mt-1 w-full rounded-md border px-3 py-2 text-sm {submitted &&
					(!hasMinLength || !hasUppercase || !hasSpecial)
						? 'border-red-400'
						: 'border-slate-300'}"
					type="password"
					id="register-password"
					bind:value={password}
					required
				/>
				<ul class="mt-2 space-y-1 text-xs">
					<li class="flex items-center gap-1 {hasMinLength ? 'text-green-700' : 'text-red-600'}">
						<span>{hasMinLength ? '✓' : '✗'}</span>
						<span>Au moins 12 caractères</span>
					</li>
					<li class="flex items-center gap-1 {hasUppercase ? 'text-green-700' : 'text-red-600'}">
						<span>{hasUppercase ? '✓' : '✗'}</span>
						<span>Au moins une lettre majuscule</span>
					</li>
					<li class="flex items-center gap-1 {hasSpecial ? 'text-green-700' : 'text-red-600'}">
						<span>{hasSpecial ? '✓' : '✗'}</span>
						<span>Au moins un caractère spécial autorisé</span>
					</li>
				</ul>
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

	<p class="mt-4 text-center text-sm text-slate-600">
		Déjà un compte ?
		<a href="/login" class="font-medium text-amber-700 hover:underline">Se connecter</a>
	</p>
</div>
