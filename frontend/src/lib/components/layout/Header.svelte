<script lang="ts">
	import { onMount } from 'svelte';
	import { page } from '$app/stores';
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
	import carouselPublicitaire1 from '$lib/assets/images/7wondersDuelbanner.png';
	import carouselPublicitaire2 from '$lib/assets/images/MarvelChampions.png';
	import carouselPublicitaire3 from '$lib/assets/images/TerraformingMars_banner.png';

	const menuItems = [
		{ label: 'Tous les jeux', href: '/games' },
		{ label: 'Enfants', href: '/games?category=enfants' },
		{ label: "Jeux d'ambiance", href: '/games?category=jeux%20d%27ambiance' },
		{ label: 'Jeux de plateau', href: '/games?category=jeux%20de%20plateau' },
		{ label: 'Jeux de cartes', href: '/games?category=jeux%20de%20cartes' },
		{ label: "Jeux d'expert", href: '/games?category=jeux%20d%27expert' }
	];

	const mobileMenuItems = menuItems;

	import { authUser } from '$lib/stores/auth';
	import { cartCount } from '$lib/stores/cart';

	let isAuthenticated = $derived(!!$authUser);
	let isAdmin = $derived(!!$authUser && $authUser.roles?.includes('ROLE_ADMIN'));
	let carouselIndex = $state(0);
	const carouselImages = [carouselPublicitaire1, carouselPublicitaire3, carouselPublicitaire2];
	const showCarousel = $derived($page.url.pathname === '/');

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
	{bgHeader}
	{logoLudo}
	{loupe}
	{logoConnectionBois}
	{panier}
	{menuItems}
	{isAuthenticated}
	{isAdmin}
	cartCount={$cartCount}
	onSearch={handleSearch}
/>

{#if showCarousel}
	<HeaderCarousel class="hidden lg:block" image={carouselImages[carouselIndex]} />
{/if}

<HeaderMobile
	{bgHeaderPhone}
	{logoLudo}
	{loupe}
	{panier}
	{burger}
	menuItems={mobileMenuItems}
	{isAuthenticated}
	{isAdmin}
	cartCount={$cartCount}
	onSearch={handleSearch}
/>

{#if showCarousel}
	<HeaderCarousel class="lg:hidden" image={carouselImages[carouselIndex]} />
{/if}
