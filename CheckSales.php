<?php

//TODO: Add info for $json_url
$json_url = '';

$json_data = file_get_contents($json_url);

$data = json_decode($json_data, true);

$conversions = $data['response']['data']['data']['0']['Stat']['conversions'];
$payout = $data['response']['data']['data']['0']['Stat']['payout'];

//TODO: Add info for $link
$link = mysqli_connect("localhost", "", "") or die("Error " . mysqli_error($link));
$query = "SELECT payout, conversions FROM previous_values WHERE id = 1";
$result = $link->query($query);

$row = mysqli_fetch_array($result);

if(($payout > $row["payout"]) && ($conversions > $row["conversions"]))
{
	$sql = 'UPDATE previous_values SET payout = ' . $payout . ' WHERE id = 1';
	$stmt = $link->prepare($sql);
	$stmt->execute(); 
	$stmt->close();

	$sql = 'UPDATE previous_values SET conversions = ' . $conversions . ' WHERE id = 1';
	$stmt = $link->prepare($sql);
	$stmt->execute(); 
	$stmt->close();
	
	$new_payout = $payout - $row["payout"];
	$new_conversions = $conversions - $row["conversions"];
	if($new_conversions == 1)
	{
		$sales_text = "sale";
	}
	else
	{
		$sales_text = "sales";
	}
	$message = $new_conversions . ' new ' . $sales_text . '! $' . number_format($new_payout, 2, '.', ',') . ' made!';
	
	//TODO: Add Pushover info to config file
	curl_setopt_array($ch = curl_init(), array(
	  CURLOPT_URL => "https://api.pushover.net/1/messages.json",
	  CURLOPT_POSTFIELDS => array(
	  "token" => "",
	  "user" => "",
	  "message" => $message,
	)));
	curl_exec($ch);
	curl_close($ch);
}

?>
