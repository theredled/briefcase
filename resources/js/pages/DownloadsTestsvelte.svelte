<script>
	let name = 'toi';
	let count = 0;

	function increment() {
		count += 1;
	}
</script>

<main>
	<h1 class="testclass">Salut {name} !</h1>
	<p>Tu as cliqué {count} fois.</p>
	<button on:click={increment}>
		Clique-moi
	</button>
</main>

<style>
	main {
		text-align: center;
		padding: 2rem;
		font-family: sans-serif;
	}
	button {
		padding: 0.5rem 1rem;
		font-size: 1rem;
		margin-top: 1rem;
	}
</style>
