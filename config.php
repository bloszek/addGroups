<?php



	return [

		'teamspeak' => [
			'host' => 'localhost',
			'queryPort' => 10011,
			'serverPort' => 9987,
			'queryLogin' => 'serveradmin',
			'queryPass' => 'test',
			'botName' => '@addGroups',
			'channel' => 1,
		],

		'groups' = [

			'all' => [1,5,34,34],
			'limit' => 6,

			'register' => [
				'men' => 67, // Mężczyzna
				'girl' => 43, // Kobieta
			],
		],

		'commands' => [

			'motd' => [
				'Witaj, [user_nickname]. Jestem [assistant_name]! Witaj w aplikacji addGroups',
				'W tej aplikacji możesz nadać sobie grupy takie jak: Województwa, wiekowe, 4fun.',
				'Dostępne komendy: ',
				' > !help - Pomocna komenda',
				' > !add id - Nadaje grupe poprzez podane id',
				' > !dell id - Usuwa grupę poprzez podanie id',
				' > !list - Wyświetla listę dostępnych rang',
			],
		],
	];