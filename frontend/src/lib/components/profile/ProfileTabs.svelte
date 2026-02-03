<script lang="ts">
	import { Button } from '$lib/components/ui/button';

	type ProfileTab = 'profile' | 'orders';

	export let activeTab: ProfileTab = 'profile';
	export let onSelect: (tab: ProfileTab) => void = () => {};
</script>

<div class="mt-6 flex gap-2 border-b border-slate-200">
	<Button
		variant="ghost"
		class={`px-4 py-2 text-sm font-medium ${
			activeTab === 'profile'
				? 'border-b-2 border-amber-600 text-amber-700'
				: 'text-slate-600 hover:text-slate-800'
		}`}
		onclick={() => onSelect('profile')}
	>
		Mes infos
	</Button>
	<Button
		variant="ghost"
		class={`px-4 py-2 text-sm font-medium ${
			activeTab === 'orders'
				? 'border-b-2 border-amber-600 text-amber-700'
				: 'text-slate-600 hover:text-slate-800'
		}`}
		onclick={() => onSelect('orders')}
	>
		Mes commandes
	</Button>
</div>
