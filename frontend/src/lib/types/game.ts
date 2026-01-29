export type Game = {
	bggId: number;
	name: string;
	yearPublished: number | null;
	minPlayers: number | null;
	maxPlayers: number | null;
	playTime: number | null;
	minAge: number | null;
	description: string | null;
	priceCents: number | null;
	usersRated: number | null;
	ratingAverage: string | null;
	bggRank: number | null;
	complexityAverage: string | null;
	ownedUsers: number | null;
	categories?: string[];
};
