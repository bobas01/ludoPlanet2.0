<script lang="ts">
	import { api, BASE_URL } from '$lib/api';
	import editIcon from '$lib/assets/icons/iconEdit.png';
	import trashIcon from '$lib/assets/icons/iconTrash.png';
	import type { Game } from '$lib/types/game';
	import GamesPagination from '$lib/components/games/GamesPagination.svelte';
	import { onMount } from 'svelte';

	type AdminGame = Pick<Game, 'bggId' | 'name' | 'priceCents' | 'description'> & {
		domainIds: number[];
		mechanicIds: number[];
	};

	type AdminOption = {
		id: number;
		name: string;
	};

	type CategorySlug = 'enfants' | 'ambiance' | 'plateau' | 'cartes' | 'expert';

	type CategoryImage = {
		slug: CategorySlug;
		label: string;
		url: string;
		updating: boolean;
	};

	type OrderItem = {
		id: number;
		game_id: number | null;
		game_name: string | null;
		game_image_url: string | null;
		quantity: number;
		unit_price_cents: number;
	};

	type Order = {
		id: number;
		user_id: number | null;
		status: string;
		total_cents: number;
		currency: string;
		shipping: {
			full_name: string | null;
			city: string | null;
			postal_code: string | null;
		};
		items: OrderItem[];
		created_at: string;
	};

	const PAGE_SIZE = 10;

	let activeTab = $state<'games' | 'categories' | 'mechanics' | 'domains' | 'orders'>('games');

	// Jeux
	let games = $state<AdminGame[]>([]);
	let loadingGames = $state(true);
	let gamesError = $state<string | null>(null);
	let currentPage = $state(1);

	let domains = $state<AdminOption[]>([]);
	let mechanics = $state<AdminOption[]>([]);

	let showCreateModal = $state(false);
	let showEditModal = $state(false);
	let modalSubmitting = $state(false);
	let selectedGame = $state<AdminGame | null>(null);

	let formBggId = $state<number | null>(null);
	let formName = $state('');
	let formPriceCents = $state<number | null>(null);
	let formDescription = $state('');
	let formDomainIds = $state<number[]>([]);
	let formMechanicIds = $state<number[]>([]);

	let confirmDeleteId = $state<number | null>(null);
	let confirmDeleteName = $state<string | null>(null);
	let deleting = $state(false);

	const totalPages = $derived(Math.max(1, Math.ceil(games.length / PAGE_SIZE)));
	const paginatedGames = $derived(
		games.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE)
	);

	const loadGames = async () => {
		loadingGames = true;
		gamesError = null;
		try {
			const { data } = await api.get<{ games: AdminGame[] }>('/api/admin/games');
			games = data.games ?? [];
		} catch (e) {
			gamesError =
				e instanceof Error ? e.message : "Erreur lors du chargement des jeux d'administration.";
		} finally {
			loadingGames = false;
		}
	};

	const loadDomainAndMechanicOptions = async () => {
		try {
			const [{ data: domainData }, { data: mechanicData }] = await Promise.all([
				api.get<{ domains: AdminOption[] }>('/api/admin/domains'),
				api.get<{ mechanics: AdminOption[] }>('/api/admin/mechanics')
			]);
			domains = domainData.domains ?? [];
			mechanics = mechanicData.mechanics ?? [];
		} catch {
			// On n'empêche pas le reste de fonctionner
		}
	};

	function handlePrev() {
		currentPage = Math.max(1, currentPage - 1);
	}
	function handleNext() {
		currentPage = Math.min(totalPages, currentPage + 1);
	}

	function openCreateModal() {
		selectedGame = null;
		formBggId = null;
		formName = '';
		formPriceCents = null;
		formDescription = '';
		formDomainIds = [];
		formMechanicIds = [];
		showCreateModal = true;
	}

	function openEditModal(game: AdminGame) {
		selectedGame = game;
		formBggId = game.bggId;
		formName = game.name;
		formPriceCents = game.priceCents ?? null;
		formDescription = game.description ?? '';
		formDomainIds = game.domainIds ?? [];
		formMechanicIds = game.mechanicIds ?? [];
		showEditModal = true;
	}

	function closeModals() {
		showCreateModal = false;
		showEditModal = false;
		modalSubmitting = false;
	}

	async function submitCreate() {
		if (formBggId == null || !formName.trim()) return;
		modalSubmitting = true;
		gamesError = null;
		try {
			await api.post('/api/admin/games', {
				bggId: formBggId,
				name: formName.trim(),
				description: formDescription.trim() || null,
				priceCents: formPriceCents,
				domainIds: formDomainIds,
				mechanicIds: formMechanicIds
			});
			closeModals();
			await loadGames();
		} catch (e) {
			gamesError = e instanceof Error ? e.message : 'Erreur lors de la création du jeu.';
		} finally {
			modalSubmitting = false;
		}
	}

	async function submitEdit() {
		if (!selectedGame) return;
		modalSubmitting = true;
		gamesError = null;
		try {
			await api.put(`/api/admin/games/${selectedGame.bggId}`, {
				name: formName.trim(),
				description: formDescription.trim() || null,
				priceCents: formPriceCents,
				domainIds: formDomainIds,
				mechanicIds: formMechanicIds
			});
			closeModals();
			await loadGames();
		} catch (e) {
			gamesError = e instanceof Error ? e.message : 'Erreur lors de la modification du jeu.';
		} finally {
			modalSubmitting = false;
		}
	}

	function openDeleteConfirm(game: AdminGame) {
		confirmDeleteId = game.bggId;
		confirmDeleteName = game.name;
	}

	function closeDeleteConfirm() {
		confirmDeleteId = null;
		confirmDeleteName = null;
		deleting = false;
	}

	async function confirmDelete() {
		if (confirmDeleteId == null) return;
		deleting = true;
		gamesError = null;
		try {
			await api.delete(`/api/admin/games/${confirmDeleteId}`);
			closeDeleteConfirm();
			await loadGames();
		} catch (e) {
			gamesError = e instanceof Error ? e.message : 'Erreur lors de la suppression du jeu.';
		} finally {
			deleting = false;
		}
	}

	// Images de catégories
	let categoryImages = $state<CategoryImage[]>([
		{
			slug: 'enfants',
			label: 'Jeux enfants',
			url: `${BASE_URL}/images/categories/enfants.png`,
			updating: false
		},
		{
			slug: 'ambiance',
			label: "Jeux d'ambiance",
			url: `${BASE_URL}/images/categories/ambiance.png`,
			updating: false
		},
		{
			slug: 'plateau',
			label: 'Jeux de plateau',
			url: `${BASE_URL}/images/categories/plateau.png`,
			updating: false
		},
		{
			slug: 'cartes',
			label: 'Jeux de cartes',
			url: `${BASE_URL}/images/categories/cartes.png`,
			updating: false
		},
		{
			slug: 'expert',
			label: "Jeux d'expert",
			url: `${BASE_URL}/images/categories/expert.png`,
			updating: false
		}
	]);

	let categoryImageMessage = $state<string | null>(null);

	async function handleCategoryImageChange(slug: CategorySlug, event: Event) {
		const input = event.currentTarget as HTMLInputElement;
		const file = input.files?.[0];
		if (!file) return;

		const index = categoryImages.findIndex((c) => c.slug === slug);
		if (index === -1) return;

		categoryImages[index].updating = true;
		categoryImageMessage = null;

		const formData = new FormData();
		formData.append('image', file);

		try {
			await api.postForm<{ ok: boolean; path: string }>(
				`/api/admin/category-images/${slug}`,
				formData
			);
			categoryImages[index].url = `${BASE_URL}/images/categories/${slug}.png?ts=${Date.now()}`;
			categoryImageMessage = 'Image de catégorie mise à jour.';
		} catch (e) {
			categoryImageMessage =
				e instanceof Error ? e.message : "Erreur lors de la mise à jour de l'image de catégorie.";
		} finally {
			categoryImages[index].updating = false;
			input.value = '';
		}
	}

	// Domaines / mécaniques (onglets simples)
	let domainsList = $state<AdminOption[]>([]);
	let mechanicsList = $state<AdminOption[]>([]);
	let domainsError = $state<string | null>(null);
	let mechanicsError = $state<string | null>(null);
	let newDomainName = $state('');
	let newMechanicName = $state('');

	async function loadDomainsList() {
		domainsError = null;
		try {
			const { data } = await api.get<{ domains: AdminOption[] }>('/api/admin/domains');
			domainsList = data.domains ?? [];
		} catch (e) {
			domainsError = e instanceof Error ? e.message : 'Erreur lors du chargement des domaines.';
		}
	}

	async function loadMechanicsList() {
		mechanicsError = null;
		try {
			const { data } = await api.get<{ mechanics: AdminOption[] }>('/api/admin/mechanics');
			mechanicsList = data.mechanics ?? [];
		} catch (e) {
			mechanicsError = e instanceof Error ? e.message : 'Erreur lors du chargement des mécaniques.';
		}
	}

	async function createDomain() {
		if (!newDomainName.trim()) return;
		domainsError = null;
		try {
			await api.post('/api/admin/domains', { name: newDomainName.trim() });
			newDomainName = '';
			await loadDomainsList();
		} catch (e) {
			domainsError = e instanceof Error ? e.message : 'Erreur lors de la création.';
		}
	}

	async function deleteDomain(id: number) {
		domainsError = null;
		try {
			await api.delete(`/api/admin/domains/${id}`);
			await loadDomainsList();
		} catch (e) {
			domainsError = e instanceof Error ? e.message : 'Erreur lors de la suppression.';
		}
	}

	async function createMechanic() {
		if (!newMechanicName.trim()) return;
		mechanicsError = null;
		try {
			await api.post('/api/admin/mechanics', { name: newMechanicName.trim() });
			newMechanicName = '';
			await loadMechanicsList();
		} catch (e) {
			mechanicsError = e instanceof Error ? e.message : 'Erreur lors de la création.';
		}
	}

	async function deleteMechanic(id: number) {
		mechanicsError = null;
		try {
			await api.delete(`/api/admin/mechanics/${id}`);
			await loadMechanicsList();
		} catch (e) {
			mechanicsError = e instanceof Error ? e.message : 'Erreur lors de la suppression.';
		}
	}

	// Commandes
	let orders = $state<Order[]>([]);
	let ordersLoading = $state(false);
	let ordersError = $state<string | null>(null);

	async function loadOrders() {
		ordersLoading = true;
		ordersError = null;
		try {
			const { data } = await api.get<{ orders: Order[]; pagination: unknown }>(
				'/api/orders?limit=50&sort=desc'
			);
			orders = data.orders ?? [];
		} catch (e) {
			ordersError = e instanceof Error ? e.message : 'Erreur lors du chargement des commandes.';
		} finally {
			ordersLoading = false;
		}
	}

	onMount(async () => {
		await Promise.all([loadGames(), loadDomainAndMechanicOptions()]);
	});

	$effect(() => {
		if (activeTab === 'domains') {
			loadDomainsList();
		}
		if (activeTab === 'mechanics') {
			loadMechanicsList();
		}
		if (activeTab === 'orders') {
			loadOrders();
		}
	});
</script>

<svelte:head>
	<title>Dashboard</title>
</svelte:head>

<main class="mx-auto max-w-6xl px-4 py-8">
	<div class="grid gap-6 md:grid-cols-[220px,1fr]">
		<!-- Sidebar -->
		<aside
			class="space-y-4 rounded-2xl border border-slate-200 bg-white/80 p-4 shadow-sm backdrop-blur"
		>
			<h1 class="mb-1 text-lg font-semibold text-slate-900">Dashboard</h1>
			<p class="text-xs text-slate-500">Gérer les jeux, catégories et commandes.</p>

			<nav class="mt-4 space-y-1 text-sm">
				<button
					type="button"
					class={`flex w-full items-center justify-between rounded-xl px-3 py-2 text-left ${
						activeTab === 'games'
							? 'bg-[var(--brand-accent)] text-white'
							: 'text-slate-700 hover:bg-slate-100'
					}`}
					onclick={() => (activeTab = 'games')}
				>
					<span>Jeux</span>
				</button>
				<button
					type="button"
					class={`flex w-full items-center justify-between rounded-xl px-3 py-2 text-left ${
						activeTab === 'categories'
							? 'bg-[var(--brand-accent)] text-white'
							: 'text-slate-700 hover:bg-slate-100'
					}`}
					onclick={() => (activeTab = 'categories')}
				>
					<span>Images par catégorie</span>
				</button>
				<button
					type="button"
					class={`flex w-full items-center justify-between rounded-xl px-3 py-2 text-left ${
						activeTab === 'domains'
							? 'bg-[var(--brand-accent)] text-white'
							: 'text-slate-700 hover:bg-slate-100'
					}`}
					onclick={() => (activeTab = 'domains')}
				>
					<span>Domaines</span>
				</button>
				<button
					type="button"
					class={`flex w-full items-center justify-between rounded-xl px-3 py-2 text-left ${
						activeTab === 'mechanics'
							? 'bg-[var(--brand-accent)] text-white'
							: 'text-slate-700 hover:bg-slate-100'
					}`}
					onclick={() => (activeTab = 'mechanics')}
				>
					<span>Mécaniques</span>
				</button>
				<button
					type="button"
					class={`flex w-full items-center justify-between rounded-xl px-3 py-2 text-left ${
						activeTab === 'orders'
							? 'bg-[var(--brand-accent)] text-white'
							: 'text-slate-700 hover:bg-slate-100'
					}`}
					onclick={() => (activeTab = 'orders')}
				>
					<span>Commandes</span>
				</button>
			</nav>
		</aside>

		<!-- Contenu -->
		<section class="space-y-8">
			{#if activeTab === 'games'}
				<section
					class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur"
				>
					<div class="mb-4 flex items-center justify-between gap-4">
						<div>
							<h2 class="text-xl font-semibold text-slate-900">Jeux</h2>
							<p class="text-sm text-slate-500">
								Gérer la liste des jeux (création, modification, suppression).
							</p>
						</div>
						<button
							type="button"
							onclick={openCreateModal}
							class="inline-flex items-center gap-2 rounded-full bg-[var(--brand-accent)] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[var(--brand-accent-hover)]"
						>
							<span class="text-lg leading-none">+</span>
							<span>Créer un jeu</span>
						</button>
					</div>

					{#if loadingGames}
						<div class="flex items-center justify-center py-10 text-slate-500">
							Chargement des jeux…
						</div>
					{:else if gamesError}
						<div
							class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
						>
							{gamesError}
						</div>
					{:else if games.length === 0}
						<p class="py-10 text-center text-sm text-slate-500">Aucun jeu pour le moment.</p>
					{:else}
						<!-- Desktop : tableau -->
						<div class="hidden md:block">
							<div class="overflow-x-auto">
								<table class="min-w-full table-auto text-sm">
									<thead>
										<tr
											class="border-b border-slate-200 bg-slate-50/80 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
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
												<td class="px-3 py-2 align-middle text-slate-600">
													{game.bggId}
												</td>
												<td class="px-3 py-2 align-middle font-medium text-slate-800">
													{game.name}
												</td>
												<td class="px-3 py-2 text-right align-middle text-slate-700">
													{#if game.priceCents != null}
														{(game.priceCents / 100).toFixed(2)} €
													{:else}
														<span class="text-slate-400">—</span>
													{/if}
												</td>
												<td class="px-3 py-2 text-right align-middle">
													<div class="flex flex-wrap items-center justify-end gap-2">
														<button
															type="button"
															onclick={() => openEditModal(game)}
															class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-[var(--brand-accent)] hover:bg-slate-50 hover:text-[var(--brand-accent)]"
															aria-label="Modifier le jeu"
														>
															<img src={editIcon} alt="Modifier" class="h-4 w-4" />
														</button>
														<button
															type="button"
															onclick={() => openDeleteConfirm(game)}
															class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-700 shadow-sm hover:bg-red-100"
															aria-label="Supprimer le jeu"
														>
															<img src={trashIcon} alt="Supprimer" class="h-4 w-4" />
														</button>
													</div>
												</td>
											</tr>
										{/each}
									</tbody>
								</table>
							</div>
						</div>

						<!-- Mobile : cartes empilées -->
						<div class="space-y-3 md:hidden">
							{#each paginatedGames as game}
								<div class="rounded-2xl border border-slate-200 bg-white/90 p-3 text-sm shadow-sm">
									<div class="flex items-start justify-between gap-2">
										<div>
											<p class="text-xs font-semibold tracking-wide text-slate-400 uppercase">
												bggId {game.bggId}
											</p>
											<p class="mt-1 text-sm font-semibold text-slate-900">
												{game.name}
											</p>
											{#if game.priceCents != null}
												<p class="mt-0.5 text-xs text-slate-600">
													Prix&nbsp;: {(game.priceCents / 100).toFixed(2)} €
												</p>
											{/if}
										</div>
										<div class="flex flex-shrink-0 items-center gap-2">
											<button
												type="button"
												onclick={() => openEditModal(game)}
												class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-white text-slate-700 shadow-sm hover:border-[var(--brand-accent)] hover:bg-slate-50 hover:text-[var(--brand-accent)]"
												aria-label="Modifier le jeu"
											>
												<img src={editIcon} alt="Modifier" class="h-4 w-4" />
											</button>
											<button
												type="button"
												onclick={() => openDeleteConfirm(game)}
												class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-700 shadow-sm hover:bg-red-100"
												aria-label="Supprimer le jeu"
											>
												<img src={trashIcon} alt="Supprimer" class="h-4 w-4" />
											</button>
										</div>
									</div>
								</div>
							{/each}
						</div>

						<GamesPagination {currentPage} {totalPages} onPrev={handlePrev} onNext={handleNext} />
					{/if}
				</section>
			{:else if activeTab === 'categories'}
				<section
					class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur"
				>
					<div class="mb-4 flex items-center justify-between gap-4">
						<div>
							<h2 class="text-xl font-semibold text-slate-900">Images par catégorie</h2>
							<p class="text-sm text-slate-500">
								Une image par grande catégorie (enfants, ambiance, plateau, cartes, expert).
							</p>
						</div>
					</div>

					<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
						{#each categoryImages as cat}
							<div
								class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white/80 p-4 text-sm shadow-sm"
							>
								<div class="flex items-center justify-between gap-2">
									<p class="font-semibold text-slate-900">{cat.label}</p>
								</div>
								<div class="overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
									<img
										src={cat.url}
										alt={cat.label}
										class="h-32 w-full object-cover"
										loading="lazy"
									/>
								</div>
								<div class="flex items-center justify-between gap-2">
									<label
										class="inline-flex cursor-pointer items-center justify-center rounded-full bg-[var(--brand-accent)] px-3 py-1.5 text-xs font-semibold text-white shadow hover:bg-[var(--brand-accent-hover)]"
									>
										{#if cat.updating}
											<span>Enregistrement…</span>
										{:else}
											<span>Changer l'image (PNG)</span>
										{/if}
										<input
											type="file"
											accept="image/png"
											class="hidden"
											onchange={(event) => handleCategoryImageChange(cat.slug, event)}
											disabled={cat.updating}
										/>
									</label>
								</div>
							</div>
						{/each}
					</div>

					{#if categoryImageMessage}
						<p class="mt-4 text-sm text-slate-600">{categoryImageMessage}</p>
					{/if}
				</section>
			{:else if activeTab === 'domains'}
				<section
					class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur"
				>
					<h2 class="mb-3 text-xl font-semibold text-slate-900">Domaines</h2>
					<p class="mb-4 text-sm text-slate-500">
						Ajouter ou supprimer des domaines utilisés pour les jeux.
					</p>

					<div class="mb-4 flex flex-wrap items-center gap-2">
						<input
							type="text"
							placeholder="Nouveau domaine"
							class="w-full max-w-xs rounded-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-[var(--brand-accent)] focus:ring-1 focus:ring-[var(--brand-accent)]"
							bind:value={newDomainName}
						/>
						<button
							type="button"
							onclick={createDomain}
							class="inline-flex items-center justify-center rounded-full bg-[var(--brand-accent)] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[var(--brand-accent-hover)]"
						>
							Ajouter
						</button>
					</div>

					{#if domainsError}
						<div
							class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
						>
							{domainsError}
						</div>
					{/if}

					{#if domainsList.length === 0}
						<p class="text-sm text-slate-500">Aucun domaine pour le moment.</p>
					{:else}
						<ul class="space-y-2 text-sm">
							{#each domainsList as domain}
								<li
									class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2"
								>
									<span>{domain.name}</span>
									<button
										type="button"
										onclick={() => deleteDomain(domain.id)}
										class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-700 shadow-sm hover:bg-red-100"
										aria-label="Supprimer le domaine"
									>
										<img src={trashIcon} alt="Supprimer" class="h-4 w-4" />
									</button>
								</li>
							{/each}
						</ul>
					{/if}
				</section>
			{:else if activeTab === 'mechanics'}
				<section
					class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur"
				>
					<h2 class="mb-3 text-xl font-semibold text-slate-900">Mécaniques</h2>
					<p class="mb-4 text-sm text-slate-500">Ajouter ou supprimer des mécaniques de jeu.</p>

					<div class="mb-4 flex flex-wrap items-center gap-2">
						<input
							type="text"
							placeholder="Nouvelle mécanique"
							class="w-full max-w-xs rounded-full border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-[var(--brand-accent)] focus:ring-1 focus:ring-[var(--brand-accent)]"
							bind:value={newMechanicName}
						/>
						<button
							type="button"
							onclick={createMechanic}
							class="inline-flex items-center justify-center rounded-full bg-[var(--brand-accent)] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[var(--brand-accent-hover)]"
						>
							Ajouter
						</button>
					</div>

					{#if mechanicsError}
						<div
							class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
						>
							{mechanicsError}
						</div>
					{/if}

					{#if mechanicsList.length === 0}
						<p class="text-sm text-slate-500">Aucune mécanique pour le moment.</p>
					{:else}
						<ul class="space-y-2 text-sm">
							{#each mechanicsList as mechanic}
								<li
									class="flex items-center justify-between rounded-xl border border-slate-200 bg-white px-3 py-2"
								>
									<span>{mechanic.name}</span>
									<button
										type="button"
										onclick={() => deleteMechanic(mechanic.id)}
										class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-red-200 bg-red-50 text-red-700 shadow-sm hover:bg-red-100"
										aria-label="Supprimer la mécanique"
									>
										<img src={trashIcon} alt="Supprimer" class="h-4 w-4" />
									</button>
								</li>
							{/each}
						</ul>
					{/if}
				</section>
			{:else if activeTab === 'orders'}
				<section
					class="rounded-2xl border border-slate-200 bg-white/80 p-6 shadow-sm backdrop-blur"
				>
					<div class="mb-4 flex items-center justify-between gap-4">
						<div>
							<h2 class="text-xl font-semibold text-slate-900">Commandes</h2>
							<p class="text-sm text-slate-500">Voir les commandes de tous les utilisateurs.</p>
						</div>
					</div>

					{#if ordersLoading}
						<div class="flex items-center justify-center py-10 text-slate-500">
							Chargement des commandes…
						</div>
					{:else if ordersError}
						<div
							class="mb-4 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700"
						>
							{ordersError}
						</div>
					{:else if orders.length === 0}
						<p class="text-sm text-slate-500">Aucune commande pour le moment.</p>
					{:else}
						<div class="hidden md:block">
							<div class="overflow-x-auto">
								<table class="min-w-full table-auto text-sm">
									<thead>
										<tr
											class="border-b border-slate-200 bg-slate-50/80 text-left text-xs font-semibold tracking-wide text-slate-500 uppercase"
										>
											<th class="px-3 py-2">ID</th>
											<th class="px-3 py-2">Client</th>
											<th class="px-3 py-2">Statut</th>
											<th class="px-3 py-2 text-right">Total</th>
											<th class="px-3 py-2">Date</th>
										</tr>
									</thead>
									<tbody>
										{#each orders as order}
											<tr class="border-b border-slate-100 hover:bg-slate-50/60">
												<td class="px-3 py-2 align-middle text-slate-700">
													#{order.id}
												</td>
												<td class="px-3 py-2 align-middle text-slate-700">
													{order.shipping.full_name ?? '—'}
												</td>
												<td class="px-3 py-2 align-middle text-slate-700">
													{order.status}
												</td>
												<td class="px-3 py-2 text-right align-middle text-slate-700">
													{(order.total_cents / 100).toFixed(2)} €
												</td>
												<td class="px-3 py-2 align-middle text-slate-600">
													{order.created_at}
												</td>
											</tr>
										{/each}
									</tbody>
								</table>
							</div>
						</div>

						<div class="space-y-3 md:hidden">
							{#each orders as order}
								<div class="rounded-2xl border border-slate-200 bg-white/90 p-3 text-sm shadow-sm">
									<p class="text-xs font-semibold tracking-wide text-slate-400 uppercase">
										Commande #{order.id}
									</p>
									<p class="mt-1 text-sm font-semibold text-slate-900">
										{order.shipping.full_name ?? '—'}
									</p>
									<p class="mt-0.5 text-xs text-slate-600">
										Statut&nbsp;: {order.status}
									</p>
									<p class="mt-0.5 text-xs text-slate-600">
										Total&nbsp;: {(order.total_cents / 100).toFixed(2)} €
									</p>
									<p class="mt-0.5 text-xs text-slate-500">
										{order.created_at}
									</p>
								</div>
							{/each}
						</div>
					{/if}
				</section>
			{/if}
		</section>
	</div>

	{#if showCreateModal || showEditModal}
		<div
			class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm"
			aria-hidden="true"
			onclick={closeModals}
		></div>
		<div
			class="fixed inset-0 z-50 flex items-center justify-center px-4"
			aria-modal="true"
			role="dialog"
		>
			<div
				class="w-full max-w-lg rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-xl backdrop-blur"
			>
				<h2 class="mb-4 text-lg font-semibold text-slate-900">
					{#if showCreateModal}
						Créer un jeu
					{:else}
						Modifier le jeu
					{/if}
				</h2>

				<div class="space-y-4">
					{#if showCreateModal}
						<div class="space-y-1.5">
							<label
								for="admin-bgg-id"
								class="block text-xs font-medium tracking-wide text-slate-500 uppercase"
								>bggId</label
							>
							<input
								id="admin-bgg-id"
								type="number"
								class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-[var(--brand-accent)] focus:ring-1 focus:ring-[var(--brand-accent)]"
								bind:value={formBggId}
								min="1"
							/>
						</div>
					{/if}

					<div class="space-y-1.5">
						<label
							for="admin-name"
							class="block text-xs font-medium tracking-wide text-slate-500 uppercase">Nom</label
						>
						<input
							id="admin-name"
							type="text"
							class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-[var(--brand-accent)] focus:ring-1 focus:ring-[var(--brand-accent)]"
							bind:value={formName}
						/>
					</div>

					<div class="space-y-1.5">
						<label
							for="admin-price"
							class="block text-xs font-medium tracking-wide text-slate-500 uppercase"
							>Prix (en euros)</label
						>
						<input
							id="admin-price"
							type="number"
							step="0.01"
							min="0"
							class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-[var(--brand-accent)] focus:ring-1 focus:ring-[var(--brand-accent)]"
							onchange={(event) => {
								const target = event.currentTarget as HTMLInputElement;
								const value = parseFloat(target.value);
								formPriceCents = Number.isNaN(value) ? null : Math.round(value * 100);
							}}
							value={formPriceCents != null ? (formPriceCents / 100).toFixed(2) : ''}
						/>
					</div>

					<div class="space-y-1.5">
						<label
							for="admin-description"
							class="block text-xs font-medium tracking-wide text-slate-500 uppercase"
							>Description</label
						>
						<textarea
							id="admin-description"
							rows="4"
							class="w-full rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 outline-none focus:border-[var(--brand-accent)] focus:ring-1 focus:ring-[var(--brand-accent)]"
							bind:value={formDescription}
						></textarea>
					</div>

					<div class="grid gap-4 md:grid-cols-2">
						<div class="space-y-1.5">
							<p class="text-xs font-medium tracking-wide text-slate-500 uppercase">Domaine(s)</p>
							<div
								class="max-h-40 space-y-1 overflow-auto rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
							>
								{#if domains.length === 0}
									<p class="text-xs text-slate-400">Aucun domaine disponible.</p>
								{:else}
									{#each domains as domain}
										<label class="flex items-center gap-2 text-sm text-slate-700">
											<input
												type="checkbox"
												checked={formDomainIds.includes(domain.id)}
												onchange={(event) => {
													const checked = (event.currentTarget as HTMLInputElement).checked;
													if (checked) {
														formDomainIds = [...formDomainIds, domain.id];
													} else {
														formDomainIds = formDomainIds.filter((id) => id !== domain.id);
													}
												}}
											/>
											<span>{domain.name}</span>
										</label>
									{/each}
								{/if}
							</div>
						</div>

						<div class="space-y-1.5">
							<p class="text-xs font-medium tracking-wide text-slate-500 uppercase">Mécanique(s)</p>
							<div
								class="max-h-40 space-y-1 overflow-auto rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-sm"
							>
								{#if mechanics.length === 0}
									<p class="text-xs text-slate-400">Aucune mécanique disponible.</p>
								{:else}
									{#each mechanics as mechanic}
										<label class="flex items-center gap-2 text-sm text-slate-700">
											<input
												type="checkbox"
												checked={formMechanicIds.includes(mechanic.id)}
												onchange={(event) => {
													const checked = (event.currentTarget as HTMLInputElement).checked;
													if (checked) {
														formMechanicIds = [...formMechanicIds, mechanic.id];
													} else {
														formMechanicIds = formMechanicIds.filter((id) => id !== mechanic.id);
													}
												}}
											/>
											<span>{mechanic.name}</span>
										</label>
									{/each}
								{/if}
							</div>
						</div>
					</div>
				</div>

				<div class="mt-6 flex justify-end gap-3">
					<button
						type="button"
						onclick={closeModals}
						class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
						disabled={modalSubmitting}
					>
						Annuler
					</button>
					<button
						type="button"
						onclick={showCreateModal ? submitCreate : submitEdit}
						class="inline-flex items-center justify-center rounded-full bg-[var(--brand-accent)] px-4 py-2 text-sm font-semibold text-white shadow hover:bg-[var(--brand-accent-hover)] disabled:opacity-60"
						disabled={modalSubmitting}
					>
						{#if modalSubmitting}
							<span>Enregistrement…</span>
						{:else if showCreateModal}
							Créer
						{:else}
							Enregistrer
						{/if}
					</button>
				</div>
			</div>
		</div>
	{/if}

	{#if confirmDeleteId != null}
		<div class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm" aria-hidden="true"></div>
		<div
			class="fixed inset-0 z-50 flex items-center justify-center px-4"
			aria-modal="true"
			role="dialog"
		>
			<div
				class="w-full max-w-md rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-xl backdrop-blur"
			>
				<h2 class="mb-2 text-lg font-semibold text-slate-900">Supprimer le jeu</h2>
				<p class="mb-4 text-sm text-slate-600">
					Es-tu sûr de vouloir supprimer&nbsp;:
					<span class="font-semibold text-slate-900">{confirmDeleteName}</span> ?
				</p>
				<div class="flex justify-end gap-3">
					<button
						type="button"
						onclick={closeDeleteConfirm}
						class="inline-flex items-center justify-center rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
						disabled={deleting}
					>
						Annuler
					</button>
					<button
						type="button"
						onclick={confirmDelete}
						class="inline-flex items-center justify-center rounded-full bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700 disabled:opacity-60"
						disabled={deleting}
					>
						{#if deleting}
							Suppression…
						{:else}
							Supprimer
						{/if}
					</button>
				</div>
			</div>
		</div>
	{/if}
</main>
