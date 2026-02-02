<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { authError, authLoading, login } from '$lib/stores/auth';

	let email = '';
	let password = '';
	let submitted = false;

	const handleSubmit = async (event: Event) => {
		event.preventDefault();
		submitted = true;
		const success = await login(email, password);
		if (success) {
			const redirect = $page.url.searchParams.get('redirect') ?? '/';
			await goto(redirect);
		}
	};
</script>

<div class="mx-auto max-w-md">
	<h1 class="text-2xl font-bold text-slate-800">Connexion</h1>
	<p class="mt-2 text-sm text-slate-600">Connectez-vous pour accéder à votre compte.</p>

	<form class="mt-6 space-y-4" onsubmit={handleSubmit}>
		<div>
			<label class="text-sm font-medium text-slate-700" for="login-email">Email</label>
			<input
				class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
				type="email"
				id="login-email"
				bind:value={email}
				required
			/>
		</div>
		<div>
			<label class="text-sm font-medium text-slate-700" for="login-password">Mot de passe</label>
			<input
				class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
				type="password"
				id="login-password"
				bind:value={password}
				required
			/>
		</div>
		{#if $authError && submitted}
			<p class="text-sm text-red-600">{$authError.message}</p>
		{/if}
		<button
			type="submit"
			disabled={$authLoading}
			class="w-full rounded-md bg-amber-600 px-4 py-2 text-white hover:bg-amber-700 disabled:opacity-60"
		>
			{#if $authLoading}Connexion...{:else}Se connecter{/if}
		</button>
	</form>
</div>
