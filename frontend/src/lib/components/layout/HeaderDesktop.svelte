<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import {
		DropdownMenu,
		DropdownMenuContent,
		DropdownMenuItem,
		DropdownMenuTrigger
	} from '$lib/components/ui/dropdown-menu';

	type MenuItem = { label: string; href: string };
	type Props = {
		bgHeader: string;
		logoLudo: string;
		loupe: string;
		logoConnectionBois: string;
		panier: string;
		menuItems: MenuItem[];
		isAuthenticated: boolean;
		cartCount: number;
		onSearch: (event: Event) => void;
	};

	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { logout } from '$lib/stores/auth';

	let {
		bgHeader,
		logoLudo,
		loupe,
		logoConnectionBois,
		panier,
		menuItems,
		isAuthenticated,
		cartCount,
		onSearch
	}: Props = $props();

	const redirectTarget = $derived(`${$page.url.pathname}${$page.url.search}`);
	const goToLogin = () => goto(`/login?redirect=${encodeURIComponent(redirectTarget)}`);
	const goToRegister = () => goto(`/register?redirect=${encodeURIComponent(redirectTarget)}`);
	const goToProfile = () => goto('/me');
	const handleLogout = async () => {
		await logout();
		await goto('/');
	};
</script>

<header class="hidden text-white lg:block">
	<div class="w-full bg-cover bg-center" style={`background-image: url(${bgHeader});`}>
		<div class="w-full px-8 py-6">
			<div class="flex items-center justify-between gap-6">
				<div class="flex items-center gap-3">
					<a href="/" class="shrink-0">
						<img class="h-[100px] w-[100px]" src={logoLudo} alt="Logo" />
					</a>
					<div class="leading-tight">
						<p class="font-body text-sm text-white/90">pour s'évader le temps d'une partie...</p>
					</div>
				</div>

				<div class="flex items-center gap-6">
					<form class="relative" onsubmit={onSearch}>
						<Input
							class="h-12 w-[360px] rounded-[25px] border border-white/20 bg-white/10 pr-12 text-sm text-white backdrop-blur placeholder:text-white/70 focus-visible:ring-white/40"
							placeholder="Rechercher un jeu"
						/>
						<Button
							variant="ghost"
							size="icon"
							type="submit"
							class="absolute top-1/2 right-2 h-9 w-9 -translate-y-1/2 rounded-full hover:bg-white/15"
						>
							<img class="h-6 w-6" src={loupe} alt="Search" />
						</Button>
					</form>

					<DropdownMenu>
						<DropdownMenuTrigger class="relative">
							<img class="h-[100px] w-[100px]" src={logoConnectionBois} alt="Compte" />
							{#if isAuthenticated}
								<span class="absolute right-1 bottom-1 h-3 w-3 rounded-full bg-green-500"></span>
							{/if}
						</DropdownMenuTrigger>
						<DropdownMenuContent
							class="w-44 rounded-xl border-white/20 bg-[var(--brand-dark)] text-white"
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
						</DropdownMenuContent>
					</DropdownMenu>

					<a class="relative" href="/card">
						<img class="mb-[13px] h-[40px] w-[40px]" src={panier} alt="Panier" />
						{#if cartCount > 0}
							<span
								class="absolute top-[25px] -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-[var(--brand-accent)] text-[11px] font-semibold"
							>
								{cartCount}
							</span>
						{/if}
					</a>
				</div>
			</div>
		</div>
	</div>
	<div class="w-full bg-[var(--brand-dark)]">
		<nav class="grid grid-cols-6 border-t border-[var(--brand-accent)]">
			{#each menuItems as item, index}
				<a
					class={`font-title flex items-center justify-center py-3 text-sm font-semibold tracking-wide uppercase hover:bg-white/10 ${
						index === 0 ? '' : 'border-l border-[var(--brand-accent)]'
					}`}
					href={item.href}
				>
					{item.label}
				</a>
			{/each}
		</nav>
	</div>
</header>

<style>
	.bottom-1 {
		bottom: 35px;
	}
	.right-1 {
		right: 25px;
	}
</style>
