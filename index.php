<?php

require_once('data.php');
require_once('functions.php');

function user_registration($m)
{
	$scene = $GLOBALS['scene'];
	$next_step = $scene[0]['text'];
	
	if (in_array($m['message']['text'], $scene[0]['answers'])) {
		$whom = $m['message']['from']['id'];
		$name = $m['message']['from']['first_name'];
		$GLOBALS['current_state'] = 0;
		
		$command = "insert ignore bot_users set first_name = '$name', chat_id = '$whom';";
		
		$is_add = gpdo()->exec($command);
	
		if (key_exists('photo', $scene[0])) {
			send_p($whom, $scene[0]['photo']);
		}
	
		send_m($whom, $next_step);
		update_state($whom, 1, 0);
	}
}


function gpdo()
{
	return new PDO('mysql:dbname=test;host=localhost;', 'object_b', 'W2o4C6a5');
}

function vd($what)
{
	var_dump($what);
	exit;
}

function send_m($whom, $new_text)
{
	$bot_url = $GLOBALS['bot_id'];
	
	$any_mes = "$bot_url/sendMessage?chat_id=$whom&text=$new_text";
	
	file_get_contents($any_mes);
}



function reset_state($whom, $name, $body)
{
	if (strpos($body, 'начать заново') !== false) {
		// $whom = $m['message']['from']['id'];
		// $name = $m['message']['from']['first_name'];

		$GLOBALS['current_state'] = 0;
	
		if (key_exists('photo', $GLOBALS['scene'][0])) {
			send_p($whom, $GLOBALS['scene'][0]['photo']);
		}
	
		send_m($whom,  $GLOBALS['scene'][0]['text']);
		update_state($whom, 1, 0);
	}
}

function update_state($whom, $new_state)
{	
	$GLOBALS['current_state'] = $new_state;
	// $whom = $entry['message']['from']['id'];
	$upd_state = "update bot_users set state = $new_state where chat_id = $whom;";
	
	gpdo()->exec($upd_state);
}

create_table();

for ($i = 1; $i--;) {
	
	$the_p = gpdo();
	$quer_select_limit = "SELECT `message_id` from `$GLOBALS[bot_token]` order by `message_id` desc limit 1;";
	$last_id = $the_p->query($quer_select_limit)->fetchAll();
	$error = $the_p->errorInfo();
	


	$offset = $GLOBALS['offset'] = $last_id[0][0];
	
	$bot_url = $GLOBALS['bot_id'];
	$to_bot_rec = "$bot_url/getUpdates";
	
	$messages = json_decode(file_get_contents($to_bot_rec), true);
	// var_dump(count($messages['result']), $to_bot_rec); exit;	
	$messages['result'] = array_filter($messages['result'], function($entry) {
		$id = $entry['message']['message_id'];
		// echo $id . PHP_EOL;
		if ($GLOBALS['offset'] < $id) {
			return true;
		} else {
			return false;
		}
	});
	
	// var_dump(__LINE__, $messages, $last_id);
	
	$texts = array_map(function($entry) {	
		$options = array();
	
		$id = $entry['message']['message_id'];
		$body = $entry['message']['text'];
		$stamp = $entry['message']['date'];
		$command = "insert `$GLOBALS[bot_token]` set message_body = '$body', message_id = '$id';";
		$name = $entry['message']['from']['first_name'];
		
		$upd = gpdo();
		
		$upd->exec($command);
		
		// var_dump($upd->errorInfo());
		
		$whom = $entry['message']['from']['id'];
		
		// var_dump($user_data);  exit;		
		
		user_registration($entry);		
		
		$user_data = get_user_data($whom);
		$friends = get_user_friends($user_data[0]['team']);		
		$GLOBALS['current_state'] = intval($user_data[0][3]);
		
		change_a_team($whom, $body);

		// var_dump($friends); exit;
			
		if ($friends) {
			foreach ($friends as $friend) {
				$tmp_state = $GLOBALS['current_state'];
				reset_state($friend['chat_id'], $name, $body);
				next_state($friend['chat_id'], $body, $GLOBALS['current_state'] + 1, $GLOBALS['current_state']);
				$GLOBALS['current_state'] = $tmp_state;
			}
		} else {
			reset_state($whom, $name, $body);
			
			for ($myi = 20; $myi--;) {
				next_state($whom, $body, $myi + 1, $myi);
			}
		}
		
		
		return $body;
	}, $messages['result']);

	sleep(1);
}


// var_dump($texts);




// $json = serialize(json_encode(file_get_contents('php://input'), true));
// file_put_contents('this.txt', $messages, FILE_APPEND);
// echo file_get_contents('php://input');
/* echo file_get_contents('https://api.telegram.org/bot292009027:AAHUM-Rcs-FKZOC16J-SrFrFrpw8A9Rwr-o/getMe'); */
/* echo file_get_contents('https://api.telegram.org/bot292009027:AAHUM-Rcs-FKZOC16J-SrFrFrpw8A9Rwr-o/sendMessage'); */
/* var_dump($messages); */

 /* $message = file_get_contents("$bot_url/sendMessage?chat_id=3421325&text=First message"); */
 