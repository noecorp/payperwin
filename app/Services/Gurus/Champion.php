<?php namespace App\Services\Gurus;

use App\Contracts\Service\Gurus\Champion as ChampionGuruInterface;

class Champion implements ChampionGuruInterface {

	/**
	 * List of all valid Pledge types.
	 *
	 * @var array
	 */
	protected $types = [
		266 => "Aatrox",
		412 => "Thresh",
		23 => "Tryndamere",
		79 => "Gragas",
		69 => "Cassiopeia",
		13 => "Ryze",
		78 => "Poppy",
		14 => "Sion",
		1 => "Annie",
		111 => "Nautilus",
		43 => "Karma",
		99 => "Lux",
		103 => "Ahri",
		2 => "Olaf",
		112 => "Viktor",
		34 => "Anivia",
		86 => "Garen",
		27 => "Singed",
		127 => "Lissandra",
		57 => "Maokai",
		25 => "Morgana",
		28 => "Evelynn",
		105 => "Fizz",
		74 => "Heimerdinger",
		238 => "Zed",
		68 => "Rumble",
		37 => "Sona",
		82 => "Mordekaiser",
		96 => "Kog'Maw",
		55 => "Katarina",
		117 => "Lulu",
		22 => "Ashe",
		30 => "Karthus",
		12 => "Alistar",
		122 => "Darius",
		67 => "Vayne",
		77 => "Udyr",
		110 => "Varus",
		89 => "Leona",
		126 => "Jayce",
		134 => "Syndra",
		80 => "Pantheon",
		92 => "Riven",
		121 => "Kha'Zix",
		42 => "Corki",
		51 => "Caitlyn",
		268 => "Azir",
		76 => "Nidalee",
		3 => "Galio",
		85 => "Kennen",
		45 => "Veigar",
		432 => "Bard",
		150 => "Gnar",
		104 => "Graves",
		90 => "Malzahar",
		254 => "Vi",
		10 => "Kayle",
		39 => "Irelia",
		64 => "Lee Sin",
		60 => "Elise",
		106 => "Volibear",
		20 => "Nunu",
		4 => "Twisted Fate",
		24 => "Jax",
		102 => "Shyvana",
		429 => "Kalista",
		36 => "Dr. Mundo",
		63 => "Brand",
		131 => "Diana",
		113 => "Sejuani",
		8 => "Vladimir",
		154 => "Zac",
		421 => "Rek'Sai",
		133 => "Quinn",
		84 => "Akali",
		18 => "Tristana",
		120 => "Hecarim",
		15 => "Sivir",
		236 => "Lucian",
		107 => "Rengar",
		19 => "Warwick",
		72 => "Skarner",
		54 => "Malphite",
		157 => "Yasuo",
		101 => "Xerath",
		17 => "Teemo",
		75 => "Nasus",
		58 => "Renekton",
		119 => "Draven",
		35 => "Shaco",
		50 => "Swain",
		115 => "Ziggs",
		40 => "Janna",
		91 => "Talon",
		61 => "Orianna",
		114 => "Fiora",
		9 => "Fiddlesticks",
		33 => "Rammus",
		31 => "Cho'Gath",
		7 => "LeBlanc",
		16 => "Soraka",
		26 => "Zilean",
		56 => "Nocturne",
		222 => "Jinx",
		83 => "Yorick",
		6 => "Urgot",
		21 => "Miss Fortune",
		62 => "Wukong",
		53 => "Blitzcrank",
		98 => "Shen",
		201 => "Braum",
		5 => "Xin Zhao",
		29 => "Twitch",
		11 => "Master Yi",
		44 => "Taric",
		32 => "Amumu",
		41 => "Gangplank",
		48 => "Trundle",
		38 => "Kassadin",
		161 => "Vel'Koz",
		143 => "Zyra",
		267 => "Nami",
		59 => "Jarvan IV",
		81 => "Ezreal",
	];

	/**
	 * {@inheritdoc}
	 */
	public function types()
	{
		return $this->types;
	}

	/**
	 * {@inheritdoc}
	 */
	public function name($id)
	{
		return $this->types[$id];
	}

}
