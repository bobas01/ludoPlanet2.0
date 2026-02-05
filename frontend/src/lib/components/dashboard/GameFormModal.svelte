<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Input } from '$lib/components/ui/input';
	import { Label } from '$lib/components/ui/label';
	import type { AdminOption } from '$lib/types/dashboard';

	type Props = {
		open: boolean;
		isCreate: boolean;
		submitting: boolean;
		formBggId: number | null;
		formName: string;
		formPriceCents: number | null;
		formDescription: string;
		formDomainIds: number[];
		formMechanicIds: number[];
		domains: AdminOption[];
		mechanics: AdminOption[];
		onClose: () => void;
		onSubmit: () => void;
		onBggIdChange: (v: number | null) => void;
		onNameChange: (v: string) => void;
		onPriceChange: (v: number | null) => void;
		onDescriptionChange: (v: string) => void;
		onDomainIdsChange: (ids: number[]) => void;
		onMechanicIdsChange: (ids: number[]) => void;
	};

	let {
		open,
		isCreate,
		submitting,
		formBggId,
		formName,
		formPriceCents,
		formDescription,
		formDomainIds,
		formMechanicIds,
		domains,
		mechanics,
		onClose,
		onSubmit,
		onBggIdChange,
		onNameChange,
		onPriceChange,
		onDescriptionChange,
		onDomainIdsChange,
		onMechanicIdsChange
	}: Props = $props();

	function toggleDomain(id: number) {
		if (formDomainIds.includes(id)) {
			onDomainIdsChange(formDomainIds.filter((i) => i !== id));
		} else {
			onDomainIdsChange([...formDomainIds, id]);
		}
	}

	function toggleMechanic(id: number) {
		if (formMechanicIds.includes(id)) {
			onMechanicIdsChange(formMechanicIds.filter((i) => i !== id));
		} else {
			onMechanicIdsChange([...formMechanicIds, id]);
		}
	}
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
		aria-labelledby="game-form-title"
	>
		<div
			class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-xl backdrop-blur pointer-events-auto"
			role="document"
		>
			<Card class="border-0 shadow-none">
				<CardHeader class="p-0 pb-4">
					<CardTitle id="game-form-title">
						{isCreate ? 'Créer un jeu' : 'Modifier le jeu'}
					</CardTitle>
				</CardHeader>
				<CardContent class="space-y-4 p-0">
					{#if isCreate}
						<div class="space-y-2">
							<Label for="admin-bgg-id">bggId</Label>
							<Input
								id="admin-bgg-id"
								type="number"
								min="1"
								value={formBggId ?? ''}
								oninput={(e) => {
									const v = (e.currentTarget as HTMLInputElement).value;
									onBggIdChange(v === '' ? null : parseInt(v, 10) || null);
								}}
								class="rounded-xl border-slate-200"
							/>
						</div>
					{/if}

					<div class="space-y-2">
						<Label for="admin-name">Nom</Label>
						<Input
							id="admin-name"
							type="text"
							value={formName}
							oninput={(e) => onNameChange((e.currentTarget as HTMLInputElement).value)}
							class="rounded-xl border-slate-200"
						/>
					</div>

					<div class="space-y-2">
						<Label for="admin-price">Prix (en euros)</Label>
						<Input
							id="admin-price"
							type="number"
							step="0.01"
							min="0"
							value={formPriceCents != null ? (formPriceCents / 100).toFixed(2) : ''}
							onchange={(e) => {
								const v = parseFloat((e.currentTarget as HTMLInputElement).value);
								onPriceChange(Number.isNaN(v) ? null : Math.round(v * 100));
							}}
							class="rounded-xl border-slate-200"
						/>
					</div>

					<div class="space-y-2">
						<Label for="admin-description">Description</Label>
						<textarea
							id="admin-description"
							rows="4"
							class="min-h-16 w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm outline-none focus:ring-2 focus:ring-[var(--brand-accent)]"
							value={formDescription}
							oninput={(e) => onDescriptionChange((e.currentTarget as HTMLTextAreaElement).value)}
						></textarea>
					</div>

					<div class="grid gap-4 md:grid-cols-2">
						<div class="space-y-2">
							<Label>Domaine(s)</Label>
							<div
								class="max-h-40 space-y-2 overflow-auto rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
							>
								{#if domains.length === 0}
									<p class="text-xs text-slate-400">Aucun domaine disponible.</p>
								{:else}
									{#each domains as domain}
										<label class="flex cursor-pointer items-center gap-2 text-slate-700">
											<input
												type="checkbox"
												checked={formDomainIds.includes(domain.id)}
												onchange={() => toggleDomain(domain.id)}
												class="h-4 w-4 rounded border-slate-300"
											/>
											<span>{domain.name}</span>
										</label>
									{/each}
								{/if}
							</div>
						</div>
						<div class="space-y-2">
							<Label>Mécanique(s)</Label>
							<div
								class="max-h-40 space-y-2 overflow-auto rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
							>
								{#if mechanics.length === 0}
									<p class="text-xs text-slate-400">Aucune mécanique disponible.</p>
								{:else}
									{#each mechanics as mechanic}
										<label class="flex cursor-pointer items-center gap-2 text-slate-700">
											<input
												type="checkbox"
												checked={formMechanicIds.includes(mechanic.id)}
												onchange={() => toggleMechanic(mechanic.id)}
												class="h-4 w-4 rounded border-slate-300"
											/>
											<span>{mechanic.name}</span>
										</label>
									{/each}
								{/if}
							</div>
						</div>
					</div>
				</CardContent>
			</Card>

			<div class="mt-6 flex justify-end gap-3">
				<Button type="button" variant="outline" onclick={onClose} disabled={submitting}>
					Annuler
				</Button>
				<Button
					type="button"
					class="bg-[var(--brand-accent)] hover:bg-[var(--brand-accent-hover)]"
					onclick={onSubmit}
					disabled={submitting}
				>
					{#if submitting}
						Enregistrement…
					{:else if isCreate}
						Créer
					{:else}
						Enregistrer
					{/if}
				</Button>
			</div>
		</div>
	</div>
{/if}
