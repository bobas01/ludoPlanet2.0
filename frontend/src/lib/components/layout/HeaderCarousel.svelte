<script lang="ts">
	type Props = {
		image: string;
		class?: string;
	};

	let { image, class: className = '' }: Props = $props();
</script>

<div class={className}>
	<div class="w-full">
		<img class="h-56 w-full sm:h-64 lg:h-72" src={image} alt="Publicité" />
	</div>
</div>
