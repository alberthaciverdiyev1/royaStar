<?php
$cert = file_get_contents('C:/Users/Albert/.cloudflared/cert.pem');
$lines = explode("\n", $cert);
$b64 = '';
foreach ($lines as $line) {
    $line = trim($line);
    if (!str_starts_with($line, '---')) {
        $b64 .= $line;
    }
}
$tokenData = json_decode(base64_decode($b64), true);
echo "Zone ID from cert: " . $tokenData['zoneID'] . "\n";
echo "Account ID: " . $tokenData['accountID'] . "\n";

// Check what zone the cert zoneID belongs to
$ch = curl_init('https://api.cloudflare.com/client/v4/zones/' . $tokenData['zoneID']);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $tokenData['apiToken'],
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$zone = json_decode($response, true);
echo "Cert zone name: " . ($zone['result']['name'] ?? 'unknown') . "\n";
echo "Cert zone full response:\n";
print_r($zone);
