<script lang="ts">
	import { onMount } from 'svelte';
	import HeaderDesktop from '$lib/components/layout/HeaderDesktop.svelte';
	import HeaderMobile from '$lib/components/layout/HeaderMobile.svelte';
	import HeaderCarousel from '$lib/components/layout/HeaderCarousel.svelte';
	import bgHeader from '$lib/assets/icons/bgHeader.png';
	import bgHeaderPhone from '$lib/assets/icons/bgHeaderPhone.png';
	import logoLudo from '$lib/assets/icons/logoLudo.png';
	import loupe from '$lib/assets/icons/loupe.png';
	import logoConnectionBois from '$lib/assets/icons/logoConnectionBois.png';
	import panier from '$lib/assets/icons/panier.png';
	import burger from '$lib/assets/icons/burger.png';
	import carouselPublicitaire1 from '$lib/assets/images/dixitPub.png';
	import carouselPublicitaire2 from '$lib/assets/images/knarrPub.png';
	import carouselPublicitaire3 from '$lib/assets/images/starWarsPub.png';

	const menuItems = [
		{ label: 'Tous les jeux', href: '/games' },
		{ label: 'Enfants', href: '/children' },
		{ label: "Jeux d'ambiance", href: '/ambiance' },
		{ label: 'Jeux de plateau', href: '/plateau' },
		{ label: 'Jeux de cartes', href: '/cards' },
		{ label: "Jeux d'expert", href: '/expert' }
	];

	const mobileMenuItems = menuItems;

	let isAuthenticated = $state(false);
	let cartCount = $state(0);
	let carouselIndex = $state(0);
	const carouselImages = [carouselPublicitaire1, carouselPublicitaire3, carouselPublicitaire2];

	onMount(() => {
		const interval = setInterval(() => {
			carouselIndex = (carouselIndex + 1) % carouselImages.length;
		}, 4500);
		return () => clearInterval(interval);
	});

	const handleSearch = (event: Event) => {
		event.preventDefault();
	};
</script>

<HeaderDesktop
	bgHeader={bgHeader}
	logoLudo={logoLudo}
	loupe={loupe}
	logoConnectionBois={logoConnectionBois}
	panier={panier}
	menuItems={menuItems}
	isAuthenticated={isAuthenticated}
	cartCount={cartCount}
	onSearch={handleSearch}
/>

<HeaderCarousel class="hidden lg:block" image={carouselImages[carouselIndex]} />

<HeaderMobile
	bgHeaderPhone={bgHeaderPhone}
	logoLudo={logoLudo}
	loupe={loupe}
	panier={panier}
	burger={burger}
	menuItems={mobileMenuItems}
	isAuthenticated={isAuthenticated}
	cartCount={cartCount}
	onSearch={handleSearch}
/>

<HeaderCarousel class="lg:hidden" image={carouselImages[carouselIndex]} />
