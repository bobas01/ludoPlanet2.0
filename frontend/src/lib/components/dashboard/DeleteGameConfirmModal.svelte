<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardHeader, CardTitle } from '$lib/components/ui/card';

	type Props = {
		open: boolean;
		gameName: string;
		deleting: boolean;
		onClose: () => void;
		onConfirm: () => void;
	};

	let { open, gameName, deleting, onClose, onConfirm }: Props = $props();
</script>

{#if open}
	<button
		type="button"
		class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm border-0 cursor-default"
		aria-label="Fermer"
		onclick={onClose}
	></button>
	<div
		class="fixed inset-0 z-50 flex items-center justify-center px-4 pointer-events-none"
		aria-modal="true"
		role="dialog"
		aria-labelledby="delete-game-title"
	>
		<div
			class="w-full max-w-md rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-xl backdrop-blur pointer-events-auto"
			role="document"
		>
			<Card class="border-0 shadow-none">
				<CardHeader class="p-0 pb-2">
					<CardTitle id="delete-game-title">Supprimer le jeu</CardTitle>
				</CardHeader>
				<CardContent class="p-0 pb-4">
					<p class="text-sm text-slate-600">
						Es-tu sûr de vouloir supprimer&nbsp;: <strong class="text-slate-900">{gameName}</strong> ?
					</p>
				</CardContent>
			</Card>
			<div class="flex justify-end gap-3">
				<Button type="button" variant="outline" onclick={onClose} disabled={deleting}>
					Annuler
				</Button>
				<Button type="button" variant="destructive" onclick={onConfirm} disabled={deleting}>
					{#if deleting}
						Suppression…
					{:else}
						Supprimer
					{/if}
				</Button>
			</div>
		</div>
	</div>
{/if}
