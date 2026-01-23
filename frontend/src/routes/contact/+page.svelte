<script lang="ts">
    type PersonType = {
    nom: string;
    age: number;
};


    const person: PersonType = {
        nom: "Julien",
        age: 35
    }
</script>

<h1>{person.nom} {person.age}</h1>
<p>Visit <a href="https://svelte.dev/docs/kit">svelte.dev/docs/kit</a> to read the documentation</p>
