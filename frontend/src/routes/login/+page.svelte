<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { authError, authLoading, login } from '$lib/stores/auth';

	let email = '';
	let password = '';
	let submitted = false;
	let showPassword = false;

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
			<div class="relative mt-1">
				<input
					class="w-full rounded-md border border-slate-300 px-3 py-2 pr-10 text-sm"
					type={showPassword ? 'text' : 'password'}
					id="login-password"
					bind:value={password}
					required
				/>
				<button
					type="button"
					class="absolute right-2 top-1/2 -translate-y-1/2 rounded p-1 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
					aria-label={showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'}
					onclick={() => (showPassword = !showPassword)}
				>
					{#if showPassword}
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
					{:else}
						<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
					{/if}
				</button>
			</div>
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
