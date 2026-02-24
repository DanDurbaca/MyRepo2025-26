<?php
require_once __DIR__ . '/conf.php';

function deepl_translate(string $text, string $targetLang = 'UK', ?string $sourceLang = 'EN'): ?string
{
    $text = trim($text);
    if ($text === '')
        return '';

    $url = 'https://api-free.deepl.com/v2/translate';

    $post = [
        'auth_key' => DEEPL_API_KEY,
        'text' => $text,
        'target_lang' => $targetLang,
    ];
    if ($sourceLang)
        $post['source_lang'] = $sourceLang;

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($post),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);

    $resp = curl_exec($ch);
    if ($resp === false) {
        curl_close($ch);
        return null;
    }
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($resp, true);
    if ($code !== 200 || !isset($json['translations'][0]['text']))
        return null;

    return $json['translations'][0]['text'];
}
