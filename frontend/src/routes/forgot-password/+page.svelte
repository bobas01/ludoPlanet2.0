<script lang="ts">
	import { goto } from '$app/navigation';
	import { authError, authLoading, forgotPassword } from '$lib/stores/auth';

	let email = '';
	let submitted = false;
	let success = false;

	const handleSubmit = async (event: Event) => {
		event.preventDefault();
		submitted = true;
		const ok = await forgotPassword(email);
		if (ok) success = true;
	};
</script>

<div class="mx-auto max-w-md">
	<h1 class="text-2xl font-bold text-slate-800">Mot de passe oublié</h1>
	<p class="mt-2 text-sm text-slate-600">
		Indiquez votre adresse e-mail. Si un compte existe, vous recevrez un lien pour réinitialiser votre mot de passe.
	</p>

	{#if success}
		<p class="mt-6 rounded-md border border-green-200 bg-green-50 px-3 py-3 text-sm text-green-800">
			Si cet e-mail est associé à un compte, un lien de réinitialisation vous a été envoyé. Vérifiez votre boîte de réception.
		</p>
		<p class="mt-4">
			<a href="/login" class="font-medium text-amber-700 hover:underline">Retour à la connexion</a>
		</p>
	{:else}
		<form class="mt-6 space-y-4" onsubmit={handleSubmit}>
			<div>
				<label class="text-sm font-medium text-slate-700" for="forgot-email">Email</label>
				<input
					class="mt-1 w-full rounded-md border border-slate-300 px-3 py-2 text-sm"
					type="email"
					id="forgot-email"
					bind:value={email}
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
				{#if $authLoading}Envoi...{:else}Envoyer le lien{/if}
			</button>
		</form>

		<p class="mt-4 text-center text-sm text-slate-600">
			<a href="/login" class="font-medium text-amber-700 hover:underline">Retour à la connexion</a>
		</p>
	{/if}
</div>
