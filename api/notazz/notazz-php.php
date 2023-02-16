<?php
header("Content-type: text/html; charset=utf-8");
// Linguagem: PHP
// Versão: 1.0.0

function send_data($fields)
{
	$fields = array("fields"=>$fields);
	$fields_string = '';
	
	//url-ify the data for the POST
	foreach($fields as $key=>$value)
	{
		$fields_string .= $key.'='.$value.'&';
	}
	
	rtrim($fields_string, '&');
	
	//open connection
	$ch = curl_init();
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch,CURLOPT_URL, 'https://app.notazz.com/api');
	curl_setopt($ch,CURLOPT_POST, count($fields));
	curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch,CURLOPT_SSL_VERIFYPEER, FALSE);
	
	//execute post
	$response = curl_exec($ch);
	
	//close connection
	curl_close($ch);
	
	//Convertendo json para array
	$pos = strpos($response, '{');
	
	return(json_decode(substr($response, $pos), true));
}
?>