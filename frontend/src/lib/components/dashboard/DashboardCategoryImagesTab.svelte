<script lang="ts">
	import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '$lib/components/ui/card';
	import type { CategoryImage, CategorySlug } from '$lib/types/dashboard';

	type Props = {
		categoryImages: CategoryImage[];
		message: string | null;
		onImageChange: (slug: CategorySlug, event: Event) => void;
	};

	let { categoryImages, message, onImageChange }: Props = $props();
</script>

<Card class="border-slate-200 bg-white/80 shadow-sm backdrop-blur">
	<CardHeader>
		<CardTitle>Images par catégorie</CardTitle>
		<CardDescription>
			Une image par grande catégorie (enfants, ambiance, plateau, cartes, expert).
		</CardDescription>
	</CardHeader>
	<CardContent>
		<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
			{#each categoryImages as cat}
				<Card class="border-slate-200 bg-white/80 shadow-sm">
					<CardHeader class="pb-2">
						<CardTitle class="text-base">{cat.label}</CardTitle>
					</CardHeader>
					<CardContent class="space-y-3">
						<div class="overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
							<img
								src={cat.url}
								alt={cat.label}
								class="h-32 w-full object-cover"
								loading="lazy"
							/>
						</div>
						<label
							class="flex cursor-pointer items-center justify-center rounded-md bg-[var(--brand-accent)] px-3 py-2 text-sm font-semibold text-white shadow hover:bg-[var(--brand-accent-hover)] disabled:cursor-not-allowed disabled:opacity-50"
							aria-disabled={cat.updating}
						>
							<input
								type="file"
								accept="image/png"
								class="hidden"
								onchange={(e) => onImageChange(cat.slug, e)}
								disabled={cat.updating}
							/>
							{#if cat.updating}
								Enregistrement…
							{:else}
								Changer l'image (PNG)
							{/if}
						</label>
					</CardContent>
				</Card>
			{/each}
		</div>
		{#if message}
			<p class="mt-4 text-sm text-slate-600">{message}</p>
		{/if}
	</CardContent>
</Card>
