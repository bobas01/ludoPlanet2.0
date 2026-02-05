<script lang="ts">
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import type { DashboardTab } from '$lib/types/dashboard';

	type Props = {
		activeTab: DashboardTab;
		onTabChange: (tab: DashboardTab) => void;
	};

	let { activeTab, onTabChange }: Props = $props();

	const tabs: { id: DashboardTab; label: string }[] = [
		{ id: 'games', label: 'Jeux' },
		{ id: 'categories', label: 'Images par catégorie' },
		{ id: 'domains', label: 'Domaines' },
		{ id: 'mechanics', label: 'Mécaniques' },
		{ id: 'orders', label: 'Commandes' }
	];
</script>

<Card class="border-slate-200 bg-white/80 shadow-sm backdrop-blur">
	<CardHeader class="pb-2">
		<CardTitle class="text-lg">Dashboard</CardTitle>
		<CardDescription>Gérer les jeux, catégories et commandes.</CardDescription>
	</CardHeader>
	<CardContent class="pt-0">
		<nav class="flex flex-col gap-1">
			{#each tabs as tab}
				<Button
					type="button"
					variant={activeTab === tab.id ? 'default' : 'ghost'}
					class="w-full justify-start rounded-xl {activeTab === tab.id ? 'bg-[var(--brand-accent)] hover:bg-[var(--brand-accent-hover)]' : ''}"
					onclick={() => onTabChange(tab.id)}
				>
					{tab.label}
				</Button>
			{/each}
		</nav>
	</CardContent>
</Card>
