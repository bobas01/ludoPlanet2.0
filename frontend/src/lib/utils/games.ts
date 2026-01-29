export function formatPrice(cents: number | null): string {
	if (cents == null) return '—';
	return (cents / 100).toFixed(2) + ' €';
}

export function labelCategory(name: string): string {
	const labels: Record<string, string> = {
		enfants: 'Enfants',
		"jeux d'ambiance": "Jeux d'ambiance",
		'jeux de cartes': 'Jeux de cartes',
		"jeux d'expert": "Jeux d'expert",
		'jeux de plateau': 'Jeux de plateau'
	};
	return labels[name] ?? name;
}
