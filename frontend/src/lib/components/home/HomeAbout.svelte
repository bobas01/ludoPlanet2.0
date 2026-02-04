<script lang="ts">
	import boardGameStore from '$lib/assets/images/boardGameStore.png';
</script>

<section class="relative right-1/2 left-1/2 -mr-[50vw] -ml-[50vw] w-screen bg-[#303047]">
	<div class="mx-auto grid max-w-6xl items-center gap-20 px-4 py-20 lg:grid-cols-2">
		<div class="order-1 space-y-4 text-white lg:order-1">
			<h2 class="mb-10 text-2xl font-bold text-[var(--brand-accent)] sm:text-3xl">
				Qui sommes-nous ?
			</h2>
			<p class="text-balance-auto text-sm leading-relaxed text-white/90 sm:text-base">
				Ludo Planet est une boutique en ligne spécialisée dans la vente de jeux de société. Créée en
				2023 par Maxime Dupont, « passionné de jeux vidéo depuis toujours, Ludo Planet souhaite
				faire découvrir au plus grand nombre les plaisirs du jeu ».
			</p>
			<p class="text-balance-auto text-sm leading-relaxed text-white/90 sm:text-base">
				Qu'il s'agisse de grands classiques ou de nouvelles pépites ludiques, Ludo Planet propose
				une sélection.
			</p>
			<p class="text-balance-auto text-sm leading-relaxed text-white/90 sm:text-base">
				Dans une ambiance conviviale et décontractée, la boutique en ligne, comme les événements
				organisés tout au long de l'année, vise à réunir les passionnés autour d'un moment de
				partage et de détente en famille ou entre amis.
			</p>
			<p class="text-balance-auto text-sm leading-relaxed text-white/90 sm:text-base">
				Bien plus qu'un simple espace de vente, Ludo Planet veut être le lieu idéal pour tous les
				curieux du jeu.
			</p>
		</div>
		<div class="order-2 lg:order-2">
			<img
				src={boardGameStore}
				alt="Jeux de société en boutique"
				class="w-full rounded-2xl object-cover shadow-lg"
			/>
		</div>
	</div>
</section>
