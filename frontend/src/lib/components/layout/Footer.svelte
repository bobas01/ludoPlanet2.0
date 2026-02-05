<script>
	import bgHeader from '$lib/assets/icons/bgHeader.png';
	import iconAdress from '$lib/assets/icons/iconAdress.png';
	import iconFb from '$lib/assets/icons/iconFb.png';
	import iconPhone from '$lib/assets/icons/iconPhone.png';
	import iconsInsta from '$lib/assets/icons/iconsInsta.png';
	import iconTwitter from '$lib/assets/icons/iconTwitter.png';

	import mentionsLegales from '$lib/assets/documents/Mentions Légales Ludo Planet.pdf';
	import cgv from '$lib/assets/documents/CGV Ludo Planet.pdf';
	import politiqueConfidentialite from '$lib/assets/documents/Politique Confidentialite.pdf';
	import politiqueCookies from '$lib/assets/documents/Politique Cookies Ludo.pdf';
</script>

<footer class="bg-cover bg-center p-10" style={`background-image: url(${bgHeader});`}>
	<section class="mx-auto flex w-fit flex-col gap-10 text-white md:flex-row md:gap-15 lg:gap-20">
		<section class="flex flex-col">
			<p class="mb-4 text-lg font-bold text-[var(--brand-accent)]">Horaires</p>
			<div class="flex flex-col gap-1">
				<p class="text-balance">Mardi - Samedi: 09h00 - 20h00</p>
				<p class="text-balance">Sauf soirée jeux: Fermeture à minuit</p>
			</div>
		</section>

		<section class="flex flex-col">
			<p class="mb-4 text-lg font-bold text-[var(--brand-accent)]">Contact</p>
			<div class="flex flex-col gap-1">
				<div class="flex items-center gap-4">
					<img src={iconPhone} alt="Phone" class="h-6 w-6" /><span>06.06.06.06.06</span>
				</div>
				<div class="flex items-center gap-4">
					<img src={iconAdress} alt="Address" class="h-6 w-6" /><span
						>75 rue jolie, 01302 dezaim</span
					>
				</div>
			</div>
		</section>

		<section class="flex flex-col">
			<p class="mb-4 text-lg font-bold text-[var(--brand-accent)]">Suivez-nous</p>
			<div class="flex flex-wrap items-center gap-4">
				<a href="https://www.facebook.com/profile.php?id=100089942604311" aria-label="Facebook">
					<img src={iconFb} alt="Facebook" class="h-10 w-10" />
				</a>
				<a href="https://www.instagram.com/jeux_de_societe_dezaim/" aria-label="Instagram">
					<img src={iconsInsta} alt="Instagram" class="h-10 w-10" />
				</a>
				<a href="https://x.com/JeuxDeSocieteDezaim" aria-label="Twitter">
					<img src={iconTwitter} alt="Twitter" class="h-10 w-10" />
				</a>
			</div>
		</section>

		<section class="flex flex-col">
			<p class="mb-4 text-lg font-bold text-[var(--brand-accent)]">Informations légales</p>
			<div class="flex flex-col gap-1 text-sm">
				<a href={mentionsLegales} target="_blank" rel="noopener noreferrer"> Mentions légales </a>
				<a href={cgv} target="_blank" rel="noopener noreferrer">
					Conditions générales de vente (CGV)
				</a>
				<a href={politiqueConfidentialite} target="_blank" rel="noopener noreferrer">
					Politique de confidentialité
				</a>
				<a href={politiqueCookies} target="_blank" rel="noopener noreferrer">
					Politique de cookies
				</a>
			</div>
		</section>
	</section>
</footer>
