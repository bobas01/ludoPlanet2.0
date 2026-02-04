<script lang="ts">
	import lorcana from '$lib/assets/images/lorcana.png';
</script>

<section class="mx-auto max-w-6xl px-4">
	<h2 class="text-center text-2xl font-bold text-[var(--brand-accent)] sm:text-3xl">
		Prochain événement
	</h2>
	<div class="mt-8 flex flex-col items-center justify-center gap-10 md:flex-row">
		<div class="w-1/3"></div>
		<div class="flex w-1/3 justify-center">
			<img
				src={lorcana}
				alt="Tournoi Lorcana"
				class="w-full max-w-sm rounded-2xl object-cover shadow-lg"
			/>
		</div>
		<div class="w-1/3 space-y-3">
			<h3 class="text-xl font-bold text-[#303047] sm:text-2xl">Tournoi L'Orcana 128 joueurs</h3>
			<p class="text-balance-auto text-sm font-semibold text-slate-600">
				Thierry Ducis, Convention Octogone, L'Orkana
			</p>
			<p class="text-balance-auto text-sm leading-relaxed text-slate-600 sm:text-base">
				Le tournoi aura lieu le samedi 30 septembre 2023, rendez-vous à 8:00 pour l'enregistrement
				et la présentation.
			</p>
		</div>
	</div>
</section>
