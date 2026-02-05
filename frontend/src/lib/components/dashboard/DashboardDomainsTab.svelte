<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Alert, AlertDescription, AlertTitle } from '$lib/components/ui/alert';
	import type { AdminOption } from '$lib/types/dashboard';

	import trashIcon from '$lib/assets/icons/iconTrash.png';

	type Props = {
		items: AdminOption[];
		error: string | null;
		newName?: string;
		onAdd: () => void;
		onDelete: (id: number) => void;
	};

	let { items, error, newName = $bindable(''), onAdd, onDelete }: Props = $props();
</script>

<Card class="border-slate-200 bg-white/80 shadow-sm backdrop-blur">
	<CardHeader>
		<CardTitle>Domaines</CardTitle>
		<CardDescription>Ajouter ou supprimer des domaines utilisés pour les jeux.</CardDescription>
	</CardHeader>
	<CardContent class="space-y-4">
		<div class="flex flex-wrap items-center gap-2">
			<Input
				type="text"
				placeholder="Nouveau domaine"
				class="max-w-xs rounded-full border-slate-200"
				bind:value={newName}
				onkeydown={(e) => e.key === 'Enter' && (e.preventDefault(), onAdd())}
			/>
			<Button
				type="button"
				class="bg-[var(--brand-accent)] hover:bg-[var(--brand-accent-hover)]"
				onclick={onAdd}
			>
				Ajouter
			</Button>
		</div>

		{#if error}
			<Alert variant="destructive">
				<AlertTitle>Erreur</AlertTitle>
				<AlertDescription>{error}</AlertDescription>
			</Alert>
		{/if}

		{#if items.length === 0}
			<p class="text-sm text-slate-500">Aucun domaine pour le moment.</p>
		{:else}
			<ul class="space-y-2 text-sm">
				{#each items as domain}
					<li
						class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2"
					>
						<span>{domain.name}</span>
						<Button
							type="button"
							variant="destructive"
							size="icon"
							class="h-8 w-8 rounded-full border-red-200 bg-red-50 hover:bg-red-100"
							aria-label="Supprimer le domaine"
							onclick={() => onDelete(domain.id)}
						>
							<img src={trashIcon} alt="Supprimer" class="h-4 w-4" />
						</Button>
					</li>
				{/each}
			</ul>
		{/if}
	</CardContent>
</Card>
