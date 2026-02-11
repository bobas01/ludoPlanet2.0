<script lang="ts">
	import { onMount } from 'svelte';
	import { goto } from '$app/navigation';
	import { page } from '$app/stores';
	import { BASE_URL, api } from '$lib/api';
	import iconCharriot from '$lib/assets/icons/iconCharriot.png';
	import iconTrash from '$lib/assets/icons/iconTrash.png';
	import { Button } from '$lib/components/ui/button';
	import { Card, CardContent, CardHeader, CardTitle } from '$lib/components/ui/card';
	import {
		cartItems,
		cartTotalCents,
		removeFromCart,
		updateQuantity,
		clearCart
	} from '$lib/stores/cart';
	import { authUser } from '$lib/stores/auth';
	import { formatPrice } from '$lib/utils/games';

	const resolveImageUrl = (url: string | null) => {
		if (!url) return null;
		const normalized = url.startsWith('/images/categories/') ? url.replace(/\.svg$/, '.png') : url;
		return normalized.startsWith('http') ? normalized : `${BASE_URL}${normalized}`;
	};

	const handleQuantityChange = (bggId: number, value: string) => {
		const next = Number.parseInt(value, 10);
		if (Number.isNaN(next)) return;
		updateQuantity(bggId, next);
	};

	const PAYMENT_CANCELLED_KEY = 'cart_payment_cancelled';

	let checkoutLoading = false;
	let checkoutError = '';
	$: showPaymentCancelledFromUrl =
		typeof window !== 'undefined' && $page.url.searchParams.get('payment') === 'cancelled';

	$: if (showPaymentCancelledFromUrl) {
		try {
			sessionStorage.setItem(PAYMENT_CANCELLED_KEY, '1');
		} catch {}
	}

	$: showPaymentFailedMessage =
		showPaymentCancelledFromUrl ||
		(typeof window !== 'undefined' && sessionStorage.getItem(PAYMENT_CANCELLED_KEY) === '1');

	onMount(() => {
		if ($page.url.searchParams.get('payment') === 'cancelled') {
			try {
				sessionStorage.setItem(PAYMENT_CANCELLED_KEY, '1');
			} catch {}
			if (typeof history !== 'undefined' && history.replaceState) {
				history.replaceState(history.state ?? null, '', $page.url.pathname);
			}
		}
	});

	async function handleCheckout() {
		if ($cartItems.length === 0) return;
		if (!$authUser) {
			goto('/login?redirect=/cart');
			return;
		}
		const missingProfile =
			!$authUser.firstName ||
			!$authUser.lastName ||
			!$authUser.address ||
			!$authUser.phoneNumber ||
			!$authUser.birthDate;
		if (missingProfile) {
		goto('/me?from=checkout');
			return;
		}
		checkoutError = '';
		checkoutLoading = true;
		try {
			const payload = {
				items: $cartItems.map((item) => ({
					game_id: item.game.bggId,
					quantity: item.quantity
				}))
			};
			const { data } = await api.post<{ url: string }>('/api/checkout/session', payload);
			if (data?.url) {
				window.location.href = data.url;
				return;
			}
			checkoutError = 'Réponse serveur invalide.';
		} catch (err: unknown) {
			const msg =
				err &&
				typeof err === 'object' &&
				'data' in err &&
				typeof (err as { data?: unknown }).data === 'object' &&
				(err as { data?: { error?: string } }).data?.error
					? (err as { data: { error: string } }).data.error
					: 'Impossible de créer la session de paiement.';
			checkoutError = msg;
		} finally {
			checkoutLoading = false;
		}
	}
</script>

<div class="mx-auto max-w-4xl">
	<h1 class="text-2xl font-bold text-slate-800">Mon panier</h1>

	{#if showPaymentFailedMessage}
		<div
			class="mt-4 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800"
			role="alert"
		>
			Paiement échoué ou annulé. Réessayez de payer.
		</div>
	{/if}

	{#if $cartItems.length === 0}
		<Card class="mt-6">
			<CardContent class="py-10 text-center text-slate-500">Votre panier est vide.</CardContent>
		</Card>
	{:else}
		<div class="mt-6 space-y-4">
			{#each $cartItems as item (item.game.bggId)}
				<Card>
					<CardContent class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
						<div class="flex items-center gap-4">
							{#if resolveImageUrl(item.game.primaryImageUrl ?? item.game.images?.[0]?.url ?? null)}
								<img
									src={resolveImageUrl(
										item.game.primaryImageUrl ?? item.game.images?.[0]?.url ?? null
									) as string}
									alt={item.game.name}
									class="h-16 w-16 rounded-md object-cover"
								/>
							{:else}
								<div
									class="flex h-16 w-16 items-center justify-center rounded-md bg-slate-100 text-sm text-slate-400"
								>
									N/A
								</div>
							{/if}
							<div>
								<p class="text-sm font-semibold text-slate-800">{item.game.name}</p>
								<p class="text-xs text-slate-500">
									Prix unitaire: {formatPrice(item.game.priceCents ?? 0)}
								</p>
							</div>
						</div>

						<div class="flex items-center gap-3">
							<div class="flex items-center rounded-md border border-slate-200">
								<button
									type="button"
									class="px-2 py-1 text-slate-600 hover:text-[var(--brand-accent)]"
									aria-label="Diminuer la quantité"
									onclick={() => updateQuantity(item.game.bggId, item.quantity - 1)}
								>
									−
								</button>
								<span class="w-8 text-center text-sm font-semibold text-slate-700">
									{item.quantity}
								</span>
								<button
									type="button"
									class="px-2 py-1 text-slate-600 hover:text-[var(--brand-accent)]"
									aria-label="Augmenter la quantité"
									onclick={() => updateQuantity(item.game.bggId, item.quantity + 1)}
								>
									+
								</button>
							</div>
							<p class="w-24 text-right text-sm font-semibold text-slate-800">
								{formatPrice((item.game.priceCents ?? 0) * item.quantity)}
							</p>
							<Button
								variant="outline"
								class="border-slate-200 px-2"
								onclick={() => removeFromCart(item.game.bggId)}
								aria-label="Supprimer l'article"
							>
								<img src={iconTrash} alt="" class="h-4 w-4" aria-hidden="true" />
							</Button>
						</div>
					</CardContent>
				</Card>
			{/each}
		</div>

		<Card class="mt-6">
			<CardHeader>
				<CardTitle>Récapitulatif</CardTitle>
			</CardHeader>
			<CardContent class="flex flex-col gap-4">
				<div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
					<div class="text-sm text-slate-600">
						Total: <span class="font-semibold text-slate-800">{formatPrice($cartTotalCents)}</span>
					</div>
					<div class="flex flex-wrap gap-3">
						<Button variant="outline" onclick={clearCart}>Vider le panier</Button>
						<Button
							class="bg-[var(--brand-accent)] text-white hover:bg-[var(--brand-accent)]/90"
							onclick={handleCheckout}
							disabled={checkoutLoading}
						>
							{#if checkoutLoading}
								Paiement en cours…
							{:else}
								<img src={iconCharriot} alt="" class="h-4 w-4" aria-hidden="true" />
								Payer avec Stripe
							{/if}
						</Button>
					</div>
				</div>
				{#if checkoutError}
					<p class="text-sm text-red-600" role="alert">{checkoutError}</p>
				{/if}
			</CardContent>
		</Card>
	{/if}
</div>
