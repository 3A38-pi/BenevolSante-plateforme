<?php

// Vérifie si le contrôleur a défini $text
if (!isset($text)) {
    $text = 'Good'; // Valeur par défaut
}

$params = array(
    'text' => $text,
    'lang' => 'en',
    'categories' => 'profanity,personal,link,drug,weapon,spam,content-trade,money-transaction,extremism,violence,self-harm,medical',
    'mode' => 'rules',
    'api_user' => '1608670126',
    'api_secret' => 'Az9aNCSbfTmc56AZNQbnjWzTMcbx9WgN'
);

$ch = curl_init('https://api.sightengine.com/1.0/text/check.json');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
$response = curl_exec($ch);
curl_close($ch);

$output = json_decode($response, true);

// Affichage du résultat
echo "<pre>";
print_r($output);
echo "</pre>";
