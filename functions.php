<?php

function create_table()
{
	$sql = "
	CREATE TABLE IF NOT EXISTS `$GLOBALS[bot_token]` (
		`message_id` int(11) NOT NULL,
		`message_body` text NOT NULL,
		`message_date` int(11) NOT NULL,
		PRIMARY KEY (`message_id`)
	) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
	
	$cr = gpdo();
	
	$cr->exec($sql);
	
	// var_dump($cr->errorInfo());	
}

function get_team_state($team)
{
	$team_sql = "select state from bot_users where team = '$team';";
	$pdost = gpdo()->query($team_sql);
	// var_dump($pdost, $team_sql);
	if ($pdost) {
		return $pdost->fetchAll();
	} else {
		return $pdost;
	}
}

function change_a_team($chat_id, $team)
{	
	// $team = $entry['message']['text'];
	// $chat_id = $entry['message']['from']['id'];
	
	if (strpos($team, 'команда ') !== false) {
		$part1 = explode(' ', $team);
		$part2 = trim($part1[1]);
		$state = get_team_state($part2);
		$state = $state ? $state[0]['state'] : 0;
		// var_dump($state);
		$sql = "update bot_users set team = '$part2', state = '$state'  where chat_id = '$chat_id';";
		
		gpdo()->exec($sql);
	}
}

function get_user_data($whom)
{
	$user_sql = "SELECT * FROM bot_users  WHERE bot_users.chat_id = $whom;";
		
	$user_data = gpdo()->query($user_sql)->fetchAll();
	
	return $user_data;
}

function get_user_friends($team)
{
	$user_sql = "SELECT * FROM bot_users  WHERE team = '$team';";
	
	$user_data = gpdo()->query($user_sql);	
	
	if ($user_data) {
		$fetched = $user_data->fetchAll();
	}
	
	return $fetched;
}

function next_state($whom, $body, $next_state, $start_state = 0, $force = false)
{
	// $whom = $mes['message']['from']['id'];
	$scene = $GLOBALS['scene'];
	$state = $GLOBALS['current_state'];
	$GLOBALS['previus_state'] = $start_state;
	// $body = $mes['message']['text'];
	
	// if ('открыть глаза' === $body && intval($state) === $start_state) {
		// var_dump($body, $state, $start_state, in_array($body, $scene[$state]['answers']), $scene[$state]['answers']);
	// }
	
	var_dump($whom, $start_state, $state);
	if (
		key_exists($state, $scene)
		&& in_array($body, $scene[$state]['answers'])
		&& $start_state === intval($state)
	) {
		send_m($whom, $scene[$state]['text']);
		
		if (key_exists('photo', $scene[$state])) {
			send_p($whom, $scene[$state]['photo']);
		}
		
		update_state($whom, $next_state);
	}	
}

function send_p($whom, $file)
{
	$bot_url = $GLOBALS['bot_id'];
	$photo = "$bot_url/sendPhoto?chat_id=$whom&photo=artem.callkeeper.ru/infinite/ico/";
	
	file_get_contents("$photo$file");
}