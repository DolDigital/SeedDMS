<?php
/* Determine all languages keys used in the php files */
$output = array();
if(exec('sgrep -o "%r\n" \'"tMLText(\"" __ "\""\' */*.php views/bootstrap/*.php|sort|uniq -c', $output)) {
	$allkeys = array();
	foreach($output as $line) {
		$data = explode(' ', trim($line));
		$allkeys[$data[1]] = $data[0];
	}
}

$languages = array('ar_EG', 'bg_BG', 'ca_ES', 'cs_CZ', 'de_DE', 'en_GB', 'es_ES', 'fr_FR', 'hu_HU', 'it_IT', 'nl_NL', 'pl_PL', 'pt_BR', 'ro_RO', 'ru_RU', 'sk_SK', 'sv_SE', 'tr_TR', 'zh_CN', 'zh_TW');
/* Reading languages */
foreach($languages as $lang) {
	include('languages/'.$lang.'/lang.inc');
	ksort($text);
	$langarr[$lang] = $text;
}

/* Check for missing phrases in each language */
echo "List of missing keys\n";
echo "-----------------------------\n";
foreach(array_keys($allkeys) as $key) {
	foreach($languages as $lang) {
		if(!isset($langarr[$lang][$key])) {
			echo "Missing key '".$key."' in language ".$lang."\n";
		}
	}
}
echo "\n";

/* Check for phrases not used anymore */
echo "List of superflous keys\n";
echo "-----------------------------\n";
foreach($languages as $lang) {
	$n = 0;
	foreach($langarr[$lang] as $key=>$value) {
		if(!isset($allkeys[$key])) {
			echo "Unused key '".$key."' in language ".$lang."\n";
			$n++;
		}
	}
	echo $n." superflous keys found\n";
}

exit;

$fpout = fopen('php://stdout', 'w');
foreach(array_keys($langarr['en_GB']) as $key) {
	$data = array($key, $langarr['en_GB'][$key], $langarr['de_DE'][$key]);
	fputcsv($fpout, $data);
}
?>
