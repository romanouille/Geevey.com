<?php
set_time_limit(300);
$host = 'localhost';
$db   = 'jva';
$user = 'postgres';
$pass = 'postgres';
$charset = 'utf8';

$dsn = "pgsql:host=$host;dbname=$db;options='--client_encoding=$charset'";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
     $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("Erreur de connexion : " . $e->getMessage());
}

$conn->exec("SET TIMEZONE TO 'UTC'");

$smilies = [
	":)" => "1.gif",
	":snif:" => "20.gif",
	":gba:" => "17.gif",
	":g)" => "3.gif",
	":-)" => "46.gif",
	":snif2:" => "13.gif",
	":bravo:" => "69.gif",
	":d)" => "4.gif",
	":hap:" => "18.gif",
	":ouch:" => "22.gif",
	":pacg:" => "9.gif",
	":cd:" => "5.gif",
	":-)))" => "23.gif",
	":ouch2:" => "57.gif",
	":pacd:" => "10.gif",
	":cute:" => "nyu.gif",
	":content:" => "24.gif",
	":p)" => "7.gif",
	":-p" => "31.gif",
	":noel:" => "11.gif",
	":oui:" => "37.gif",
	":(" => "45.gif",
	":peur:" => "47.gif",
	":question:" => "2.gif",
	":cool:" => "26.gif",
	":-(" => "14.gif",
	":coeur:" => "54.gif",
	":mort:" => "21.gif",
	":rire:" => "39.gif",
	":-((" => "15.gif",
	":fou:" => "50.gif",
	":sleep:" => "27.gif",
	":-D" => "40.gif",
	":nonnon:" => "25.gif",
	":fier:" => "53.gif",
	":honte:" => "30.gif",
	":rire2:" => "41.gif",
	":non2:" => "33.gif",
	":sarcastic:" => "43.gif",
	":monoeil:" => "34.gif",
	":o))" => "12.gif",
	":nah:" => "19.gif",
	":doute:" => "28.gif",
	":rouge:" => "55.gif",
	":ok:" => "36.gif",
	":non:" => "35.gif",
	":malade:" => "8.gif",
	":fete:" => "66.gif",
	":sournois:" => "67.gif",
	":hum:" => "68.gif",
	":ange:" => "60.gif",
	":diable:" => "61.gif",
	":gni:" => "62.gif",
	":play:" => "play.gif",
	":desole:" => "65.gif",
	":spoiler:" => "63.gif",
	":merci:" => "58.gif",
	":svp:" => "59.gif",
	":sors:" => "56.gif",
	":salut:" => "42.gif",
	":rechercher:" => "38.gif",
	":hello:" => "29.gif",
	":up:" => "44.gif",
	":bye:" => "48.gif",
	":gne:" => "51.gif",
	":lol:" => "32.gif",
	":dpdr:" => "49.gif",
	":dehors:" => "52.gif",
	":hs:" => "64.gif",
	":banzai:" => "70.gif",
	":bave:" => "71.gif",
	":pf:" => "pf.gif",
	":cimer:" => "cimer.gif",
	":ddb:" => "ddb.gif",
	":pave:" => "pave.gif",
	":objection:" => "objection.gif",
	":siffle:" => "siffle.gif"
];

uksort($smilies, function($a, $b) { return strlen($b) - strlen($a); });

function richText(string $text, bool $allowReturns = true) : string {
	global $smilies;
	
	$bb1 = [
		"/\[b\](.*?)\[\/b\]/is",
		"#https://([a-z]{0,3})\.?youtube\.com/embed/(.{11})#Ui",
		"#https://([a-z]{0,3})\.?youtube\.com/watch\?v=(.{11})#Ui"
	];

	$bb2 = [
		"<b>\\1</b>",
		"<div class=\"embed-container\"><iframe src=\"https://www.youtube.com/embed/\\2\" frameborder=\"0\" allowfullscreen></iframe></div>",
		"<div class=\"embed-container\"><iframe src=\"https://www.youtube.com/embed/\\2\" frameborder=\"0\" allowfullscreen></iframe></div>"
	];

	//$text = htmlspecialchars($text);
	
	if ($allowReturns) {
		$text = str_replace("<br />", "<br>", nl2br($text));
	}

	foreach ($smilies as $smilie=>$file) {
		$text = str_replace($smilie, "<img src=\"/img/smileys/$file\" alt=\"$smilie\">", $text);
	}

	$text = preg_replace($bb1, $bb2, $text);

	return $text;
}