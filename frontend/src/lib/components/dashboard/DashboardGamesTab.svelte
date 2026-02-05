<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import { Alert, AlertDescription, AlertTitle } from '$lib/components/ui/alert';
	import GamesPagination from '$lib/components/games/GamesPagination.svelte';
	import type { AdminGame } from '$lib/types/dashboard';

	import editIcon from '$lib/assets/icons/iconEdit.png';
	import trashIcon from '$lib/assets/icons/iconTrash.png';

	type Props = {
		games: AdminGame[];
		paginatedGames: AdminGame[];
		loading: boolean;
		error: string | null;
		currentPage: number;
		totalPages: number;
		onCreate: () => void;
		onEdit: (game: AdminGame) => void;
		onDelete: (game: AdminGame) => void;
		onPrev: () => void;
		onNext: () => void;
	};

	let {
		games,
		paginatedGames,
		loading,
		error,
		currentPage,
		totalPages,
		onCreate,
		onEdit,
		onDelete,
		onPrev,
		onNext
	}: Props = $props();
</script>

<Card class="border-slate-200 bg-white/80 shadow-sm backdrop-blur">
	<CardHeader class="flex flex-row items-center justify-between gap-4 space-y-0 pb-4">
		<div>
			<CardTitle>Jeux</CardTitle>
			<CardDescription>Gérer la liste des jeux (création, modification, suppression).</CardDescription>
		</div>
		<Button
			type="button"
			class="bg-[var(--brand-accent)] hover:bg-[var(--brand-accent-hover)]"
			onclick={onCreate}
		>
			<span class="text-lg leading-none">+</span>
			<span>Créer un jeu</span>
		</Button>
	</CardHeader>
	<CardContent>
		{#if loading}
			<div class="flex items-center justify-center py-10 text-slate-500">Chargement des jeux…</div>
		{:else if error}
			<Alert variant="destructive" class="mb-4">
				<AlertTitle>Erreur</AlertTitle>
				<AlertDescription>{error}</AlertDescription>
			</Alert>
		{:else if games.length === 0}
			<p class="py-10 text-center text-sm text-slate-500">Aucun jeu pour le moment.</p>
		{:else}
			<div class="hidden md:block">
				<div class="overflow-x-auto">
					<table class="min-w-full table-auto text-sm">
						<thead>
							<tr
								class="border-b border-slate-200 bg-slate-50/80 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"
							>
								<th class="px-3 py-2">bggId</th>
								<th class="px-3 py-2">Nom</th>
								<th class="px-3 py-2 text-right">Prix</th>
								<th class="px-3 py-2 text-right">Actions</th>
							</tr>
						</thead>
						<tbody>
							{#each paginatedGames as game}
								<tr class="border-b border-slate-100 hover:bg-slate-50/60">
									<td class="px-3 py-2 align-middle text-slate-600">{game.bggId}</td>
									<td class="px-3 py-2 align-middle font-medium text-slate-800">{game.name}</td>
									<td class="px-3 py-2 text-right align-middle text-slate-700">
										{#if game.priceCents != null}
											{(game.priceCents / 100).toFixed(2)} €
										{:else}
											<span class="text-slate-400">—</span>
										{/if}
									</td>
									<td class="px-3 py-2 text-right align-middle">
										<div class="flex flex-wrap items-center justify-end gap-2">
											<Button
												type="button"
												variant="outline"
												size="icon"
												class="h-9 w-9 rounded-full"
												aria-label="Modifier le jeu"
												onclick={() => onEdit(game)}
											>
												<img src={editIcon} alt="Modifier" class="h-4 w-4" />
											</Button>
											<Button
												type="button"
												variant="destructive"
												size="icon"
												class="h-9 w-9 rounded-full border-red-200 bg-red-50 hover:bg-red-100"
												aria-label="Supprimer le jeu"
												onclick={() => onDelete(game)}
											>
												<img src={trashIcon} alt="Supprimer" class="h-4 w-4" />
											</Button>
										</div>
									</td>
								</tr>
							{/each}
						</tbody>
					</table>
				</div>
			</div>

			<div class="space-y-3 md:hidden">
				{#each paginatedGames as game}
					<Card class="border-slate-200 bg-white/90 p-3 shadow-sm">
						<CardContent class="p-0">
							<div class="flex items-start justify-between gap-2">
								<div>
									<p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
										bggId {game.bggId}
									</p>
									<p class="mt-1 text-sm font-semibold text-slate-900">{game.name}</p>
									{#if game.priceCents != null}
										<p class="mt-0.5 text-xs text-slate-600">
											Prix&nbsp;: {(game.priceCents / 100).toFixed(2)} €
										</p>
									{/if}
								</div>
								<div class="flex flex-shrink-0 items-center gap-2">
									<Button
										type="button"
										variant="outline"
										size="icon"
										class="h-9 w-9 rounded-full"
										aria-label="Modifier le jeu"
										onclick={() => onEdit(game)}
									>
										<img src={editIcon} alt="Modifier" class="h-4 w-4" />
									</Button>
									<Button
										type="button"
										variant="destructive"
										size="icon"
										class="h-9 w-9 rounded-full border-red-200 bg-red-50 hover:bg-red-100"
										aria-label="Supprimer le jeu"
										onclick={() => onDelete(game)}
									>
										<img src={trashIcon} alt="Supprimer" class="h-4 w-4" />
									</Button>
								</div>
							</div>
						</CardContent>
					</Card>
				{/each}
			</div>

			<GamesPagination {currentPage} {totalPages} onPrev={onPrev} onNext={onNext} />
		{/if}
	</CardContent>
</Card>
