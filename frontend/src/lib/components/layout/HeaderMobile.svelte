<script lang="ts">
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import {
		DropdownMenu,
		DropdownMenuContent,
		DropdownMenuItem,
		DropdownMenuSeparator,
		DropdownMenuTrigger
	} from '$lib/components/ui/dropdown-menu';
	import { api } from '$lib/api';
	import type { Game } from '$lib/types/game';
	import { logout } from '$lib/stores/auth';

	type MenuItem = { label: string; href: string };
	type Props = {
		bgHeaderPhone: string;
		logoLudo: string;
		loupe: string;
		panier: string;
		burger: string;
		menuItems: MenuItem[];
		isAuthenticated: boolean;
		isAdmin: boolean;
		cartCount: number;
		onSearch: (event: Event) => void;
	};

	let {
		bgHeaderPhone,
		logoLudo,
		loupe,
		panier,
		burger,
		menuItems,
		isAuthenticated,
		isAdmin,
		cartCount
	}: Props = $props();

	const redirectTarget = $derived(`${$page.url.pathname}${$page.url.search}`);
	const goToLogin = () => goto(`/login?redirect=${encodeURIComponent(redirectTarget)}`);
	const goToRegister = () => goto(`/register?redirect=${encodeURIComponent(redirectTarget)}`);
	const goToProfile = () => goto('/me');
	const handleLogout = async () => {
		await logout();
		await goto('/');
	};

	let searchTerm = $state('');
	let searchResults = $state<Game[]>([]);
	let searchOpen = $state(false);
	let allGames: Game[] = [];
	let gamesLoaded = false;

	const loadGames = async () => {
		if (gamesLoaded) return;
		const response = await api.get<{ games: Game[] }>('/games');
		allGames = response.data.games ?? [];
		gamesLoaded = true;
	};

	const handleSearchInput = async (event: Event) => {
		const target = event.currentTarget as HTMLInputElement;
		searchTerm = target.value;

		if (searchTerm.trim().length < 3) {
			searchResults = [];
			searchOpen = false;
			return;
		}

		if (!gamesLoaded) {
			try {
				await loadGames();
			} catch {
				return;
			}
		}

		const term = searchTerm.toLowerCase();
		searchResults = allGames.filter((g) => g.name.toLowerCase().includes(term)).slice(0, 8);
		searchOpen = searchResults.length > 0;
	};

	const handleSearchSubmit = (event: Event) => {
		event.preventDefault();
	};

	const goToGame = (id: number) => {
		searchOpen = false;
		searchTerm = '';
		goto(`/games/${id}`);
	};
</script>

<header class="text-white lg:hidden">
	<div class="w-full bg-cover bg-center" style={`background-image: url(${bgHeaderPhone});`}>
		<div class="px-4 pt-5 pb-4">
			<div class="flex items-center justify-between">
				<a href="/" class="shrink-0">
					<img class="h-16 w-16" src={logoLudo} alt="Logo" />
				</a>
				<div class="flex items-center gap-4">
					<a class="relative" href="/cart">
						<img class="h-[36px] w-[36px]" src={panier} alt="Panier" />
						{#if cartCount > 0}
							<span
								class="absolute top-[25px] -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-[var(--brand-accent)] text-[11px] font-semibold"
							>
								{cartCount}
							</span>
						{/if}
					</a>
					<DropdownMenu>
						<DropdownMenuTrigger>
							<img class="h-10 w-10" src={burger} alt="Menu" />
						</DropdownMenuTrigger>
						<DropdownMenuContent
							class="w-56 rounded-xl border-white/20 bg-[var(--brand-dark)] text-white"
							align="end"
						>
							{#if isAuthenticated}
								<DropdownMenuItem class="cursor-pointer" onSelect={goToProfile}>
									Mes infos
								</DropdownMenuItem>
								<DropdownMenuItem class="cursor-pointer" onSelect={handleLogout}>
									Se déconnecter
								</DropdownMenuItem>
							{:else}
								<DropdownMenuItem class="cursor-pointer" onSelect={goToLogin}>
									Connexion
								</DropdownMenuItem>
								<DropdownMenuItem class="cursor-pointer" onSelect={goToRegister}>
									Inscription
								</DropdownMenuItem>
							{/if}
							<DropdownMenuSeparator />
							{#if isAdmin}
								<DropdownMenuItem class="cursor-pointer" onSelect={() => goto('/dashboard')}>
									Dashboard
								</DropdownMenuItem>
								<DropdownMenuSeparator />
							{/if}
							{#each menuItems as item}
								<DropdownMenuItem class="cursor-pointer">{item.label}</DropdownMenuItem>
							{/each}
						</DropdownMenuContent>
					</DropdownMenu>
				</div>
			</div>

			<form class="relative mt-4" onsubmit={handleSearchSubmit}>
				<Input
					class="h-11 w-full rounded-[25px] border border-white/20 bg-white/10 pr-12 text-sm text-white backdrop-blur placeholder:text-white/70 focus-visible:ring-white/40"
					placeholder="Rechercher un jeu"
					value={searchTerm}
					oninput={handleSearchInput}
				/>
				<Button
					variant="ghost"
					size="icon"
					type="submit"
					class="absolute top-1/2 right-2 h-8 w-8 -translate-y-1/2 rounded-full hover:bg-white/15"
				>
					<img class="h-5 w-5" src={loupe} alt="Search" />
				</Button>
			</form>
			{#if searchOpen}
				<div class="relative z-40 mt-2" aria-label="Résultats de la recherche">
					<div
						class="w-full rounded-2xl border border-slate-200 bg-white/95 p-2 text-sm text-slate-800 shadow-lg backdrop-blur"
					>
						{#if searchResults.length === 0}
							<p class="px-2 py-1 text-xs text-slate-500">Aucun résultat.</p>
						{:else}
							<ul class="max-h-72 space-y-1 overflow-auto">
								{#each searchResults as game}
									<li>
										<button
											type="button"
											class="flex w-full items-center justify-between rounded-lg px-2 py-1.5 text-left hover:bg-slate-100"
											onclick={() => goToGame(game.bggId)}
										>
											<span class="truncate">{game.name}</span>
											{#if game.ratingAverage}
												<span class="ml-2 shrink-0 text-[11px] text-slate-500">
													⭐ {game.ratingAverage}
												</span>
											{/if}
										</button>
									</li>
								{/each}
							</ul>
						{/if}
					</div>
				</div>
			{/if}
		</div>
	</div>
</header>
