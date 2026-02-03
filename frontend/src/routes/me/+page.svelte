<script lang="ts">
	import OrdersList from '$lib/components/profile/OrdersList.svelte';
	import ProfileForm from '$lib/components/profile/ProfileForm.svelte';
	import ProfileTabs from '$lib/components/profile/ProfileTabs.svelte';
	import { authError, authLoading, authUser, deleteMe, loadMe, updateMe } from '$lib/stores/auth';
	import { onMount } from 'svelte';

	onMount(() => {
		loadMe();
	});

	let activeTab: 'profile' | 'orders' = 'profile';

	let firstName = '';
	let lastName = '';
	let address = '';
	let phoneNumber = '';
	let birthDate = '';
	let email = '';
	let password = '';
	let submitted = false;

	$: if ($authUser) {
		firstName = $authUser.firstName;
		lastName = $authUser.lastName;
		address = $authUser.address;
		phoneNumber = $authUser.phoneNumber;
		birthDate = $authUser.birthDate;
		email = $authUser.email;
	}

	const handleUpdate = async (event: Event) => {
		event.preventDefault();
		submitted = true;
		await updateMe({
			firstName,
			lastName,
			address,
			phoneNumber,
			birthDate,
			email,
			password: password || undefined
		});
		password = '';
	};

	const handleDelete = async () => {
		const confirmed = confirm('Confirmer la suppression du compte ? Cette action est définitive.');
		if (!confirmed) return;
		await deleteMe();
	};

	const handleTabSelect = (tab: 'profile' | 'orders') => {
		activeTab = tab;
	};
</script>

{#if $authUser}
	<div class="mx-auto max-w-4xl">
		<h1 class="text-2xl font-bold text-slate-800">Mon profil</h1>
		<ProfileTabs {activeTab} onSelect={handleTabSelect} />

		{#if activeTab === 'profile'}
			<ProfileForm
				bind:firstName
				bind:lastName
				bind:address
				bind:phoneNumber
				bind:birthDate
				bind:email
				bind:password
				loading={$authLoading}
				error={$authError}
				{submitted}
				onSubmit={handleUpdate}
				onDelete={handleDelete}
			/>
		{:else}
			<OrdersList active={activeTab === 'orders'} />
		{/if}
	</div>
{:else}
	<p class="text-sm text-slate-600">Vous devez être connecté.</p>
{/if}
