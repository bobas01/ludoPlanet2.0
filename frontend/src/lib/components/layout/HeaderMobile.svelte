<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Input } from '$lib/components/ui/input';
	import {
		DropdownMenu,
		DropdownMenuContent,
		DropdownMenuItem,
		DropdownMenuSeparator,
		DropdownMenuTrigger
	} from '$lib/components/ui/dropdown-menu';

	type MenuItem = { label: string; href: string };
	type Props = {
		bgHeaderPhone: string;
		logoLudo: string;
		loupe: string;
		panier: string;
		burger: string;
		menuItems: MenuItem[];
		isAuthenticated: boolean;
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
		cartCount,
		onSearch
	}: Props = $props();
</script>

<header class="text-white lg:hidden">
	<div class="w-full bg-cover bg-center" style={`background-image: url(${bgHeaderPhone});`}>
		<div class="px-4 pt-5 pb-4">
			<div class="flex items-center justify-between">
				<a href="/" class="shrink-0">
					<img class="h-16 w-16" src={logoLudo} alt="Logo" />
				</a>
				<div class="flex items-center gap-4">
					<a class="relative" href="/card">
						<img class="h-[36px] w-[36px]" src={panier} alt="Panier" />
						{#if cartCount > 0}
							<span
								class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center rounded-full bg-[var(--brand-accent)] text-[11px] font-semibold"
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
								<DropdownMenuItem class="cursor-pointer">Mes infos</DropdownMenuItem>
								<DropdownMenuItem class="cursor-pointer">Se déconnecter</DropdownMenuItem>
							{:else}
								<DropdownMenuItem class="cursor-pointer">Connexion</DropdownMenuItem>
								<DropdownMenuItem class="cursor-pointer">Inscription</DropdownMenuItem>
							{/if}
							<DropdownMenuSeparator />
							{#each menuItems as item}
								<DropdownMenuItem class="cursor-pointer">{item.label}</DropdownMenuItem>
							{/each}
						</DropdownMenuContent>
					</DropdownMenu>
				</div>
			</div>

			<form class="relative mt-4" onsubmit={onSearch}>
				<Input
					class="h-11 w-full rounded-[25px] border border-white/20 bg-white/10 pr-12 text-sm text-white backdrop-blur placeholder:text-white/70 focus-visible:ring-white/40"
					placeholder="Rechercher un jeu"
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
		</div>
	</div>
</header>
