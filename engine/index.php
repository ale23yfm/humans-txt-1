<?php

declare(strict_types=1);

// === CORS Headers ===
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

const DEFAULT_DOMAIN = 'humanstxt.org';
const SERVER_ID = '{{SERVER_ID}}';
const CHANNEL_ID = '{{CHANNEL_ID}}';

function sendToDiscordWebhook(string $msg): void
{
    $url = 'https://discord.com/api/webhooks/' . SERVER_ID . '/' . CHANNEL_ID;
    $data = ['content' => $msg];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_exec($ch);
    curl_close($ch);
}

function checkHumansTxtExistence(string $domain): string|bool
{
    $urlsToTry = [
        "https://$domain/humans.txt",
        "https://www.$domain/humans.txt"
    ];

    foreach ($urlsToTry as $url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        // Detect InfinityFree “redirect to index.html”
        if (strpos($response, '<!DOCTYPE html>') !== false && $httpCode === 200) {
            return false; // Treat as not found
        }

        if ($httpCode === 200 && !empty($response)) {
            return $response;
        }
    }

    return false;
}

// === Main ===
$domain = filter_input(INPUT_POST, 'domain', FILTER_SANITIZE_URL)
    ?? filter_input(INPUT_GET, 'domain', FILTER_SANITIZE_URL)
    ?? DEFAULT_DOMAIN;

$result = checkHumansTxtExistence($domain);

if ($result) {
    sendToDiscordWebhook("Found humans.txt at $domain on " . date('l d-m-Y H:i:s'));
    header('Content-Type: text/plain; charset=utf-8');
    echo $result;
    exit;
}

// Not found
header('Content-Type: application/json; charset=utf-8');
http_response_code(404);
echo json_encode(["error" => "humans.txt not found"]);
