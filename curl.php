<?php

$curl = curl_init('https://api.ciscospark.com/v1/rooms');

curl_setopt_array($curl, array(
	CURLOPT_POST => true,
	CURLOPT_POSTFIELDS	=> array(
		"title" => "Project Unicorn - Sprint 0",
		"teamId" => "Y2lzY29zcGFyazovL3VzL1JPT00vNjRlNDVhZTAtYzQ2Yi0xMWU1LTlkZjktMGQ0MWUzNDIxOTcz"
	),
));

$transfer = curl_exec($curl);
$info = curl_getinfo($curl);
var_dump($info);
curl_close($curl);