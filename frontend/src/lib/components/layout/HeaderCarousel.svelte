<script lang="ts">
	type Props = {
		image: string;
		class?: string;
	};

	let { image, class: className = '' }: Props = $props();
</script>

<div class={className}>
	<div class="w-full">
		<img class="h-auto w-full object-cover" src={image} alt="Publicité" />
	</div>
</div>
