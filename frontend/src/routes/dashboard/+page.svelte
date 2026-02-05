<script lang="ts">
	import { api, BASE_URL } from '$lib/api';
	import { onMount } from 'svelte';
	import type {
		AdminGame,
		AdminOption,
		CategoryImage,
		CategorySlug,
		DashboardTab,
		Order
	} from '$lib/types/dashboard';
	import DashboardSidebar from '$lib/components/dashboard/DashboardSidebar.svelte';
	import DashboardGamesTab from '$lib/components/dashboard/DashboardGamesTab.svelte';
	import DashboardCategoryImagesTab from '$lib/components/dashboard/DashboardCategoryImagesTab.svelte';
	import DashboardDomainsTab from '$lib/components/dashboard/DashboardDomainsTab.svelte';
	import DashboardMechanicsTab from '$lib/components/dashboard/DashboardMechanicsTab.svelte';
	import DashboardOrdersTab from '$lib/components/dashboard/DashboardOrdersTab.svelte';
	import GameFormModal from '$lib/components/dashboard/GameFormModal.svelte';
	import DeleteGameConfirmModal from '$lib/components/dashboard/DeleteGameConfirmModal.svelte';

	const PAGE_SIZE = 10;
	const CATEGORY_IMAGES_INIT: CategoryImage[] = [
		{ slug: 'enfants', label: 'Jeux enfants', url: `${BASE_URL}/images/categories/enfants.png`, updating: false },
		{ slug: 'ambiance', label: "Jeux d'ambiance", url: `${BASE_URL}/images/categories/ambiance.png`, updating: false },
		{ slug: 'plateau', label: 'Jeux de plateau', url: `${BASE_URL}/images/categories/plateau.png`, updating: false },
		{ slug: 'cartes', label: 'Jeux de cartes', url: `${BASE_URL}/images/categories/cartes.png`, updating: false },
		{ slug: 'expert', label: "Jeux d'expert", url: `${BASE_URL}/images/categories/expert.png`, updating: false }
	];

	let activeTab = $state<DashboardTab>('games');

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
			// ne pas bloquer
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

	// Images catégories
	let categoryImages = $state<CategoryImage[]>([...CATEGORY_IMAGES_INIT]);
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

	// Domaines / Mécaniques
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
			ordersError =
				e instanceof Error ? e.message : 'Erreur lors du chargement des commandes.';
		} finally {
			ordersLoading = false;
		}
	}

	onMount(async () => {
		await Promise.all([loadGames(), loadDomainAndMechanicOptions()]);
	});

	$effect(() => {
		if (activeTab === 'domains') loadDomainsList();
		if (activeTab === 'mechanics') loadMechanicsList();
		if (activeTab === 'orders') loadOrders();
	});
</script>

<svelte:head>
	<title>Dashboard</title>
</svelte:head>

<main class="mx-auto max-w-6xl px-4 py-8">
	<div class="grid gap-6 md:grid-cols-[220px,1fr]">
		<DashboardSidebar {activeTab} onTabChange={(tab) => (activeTab = tab)} />

		<section class="space-y-8">
			{#if activeTab === 'games'}
				<DashboardGamesTab
					{games}
					{paginatedGames}
					loading={loadingGames}
					error={gamesError}
					{currentPage}
					{totalPages}
					onCreate={openCreateModal}
					onEdit={openEditModal}
					onDelete={openDeleteConfirm}
					onPrev={handlePrev}
					onNext={handleNext}
				/>
			{:else if activeTab === 'categories'}
				<DashboardCategoryImagesTab
					{categoryImages}
					message={categoryImageMessage}
					onImageChange={handleCategoryImageChange}
				/>
			{:else if activeTab === 'domains'}
				<DashboardDomainsTab
					items={domainsList}
					error={domainsError}
					bind:newName={newDomainName}
					onAdd={createDomain}
					onDelete={deleteDomain}
				/>
			{:else if activeTab === 'mechanics'}
				<DashboardMechanicsTab
					items={mechanicsList}
					error={mechanicsError}
					bind:newName={newMechanicName}
					onAdd={createMechanic}
					onDelete={deleteMechanic}
				/>
			{:else if activeTab === 'orders'}
				<DashboardOrdersTab
					{orders}
					loading={ordersLoading}
					error={ordersError}
				/>
			{/if}
		</section>
	</div>

	<GameFormModal
		open={showCreateModal || showEditModal}
		isCreate={showCreateModal}
		submitting={modalSubmitting}
		{formBggId}
		{formName}
		{formPriceCents}
		{formDescription}
		{formDomainIds}
		{formMechanicIds}
		{domains}
		{mechanics}
		onClose={closeModals}
		onSubmit={showCreateModal ? submitCreate : submitEdit}
		onBggIdChange={(v) => (formBggId = v)}
		onNameChange={(v) => (formName = v)}
		onPriceChange={(v) => (formPriceCents = v)}
		onDescriptionChange={(v) => (formDescription = v)}
		onDomainIdsChange={(ids) => (formDomainIds = ids)}
		onMechanicIdsChange={(ids) => (formMechanicIds = ids)}
	/>

	<DeleteGameConfirmModal
		open={confirmDeleteId != null}
		gameName={confirmDeleteName ?? ''}
		{deleting}
		onClose={closeDeleteConfirm}
		onConfirm={confirmDelete}
	/>
</main>
