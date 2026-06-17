<?php

$json = file_get_contents('https://raw.githubusercontent.com/lukes/ISO-3166-Countries-with-Regional-Codes/master/all/all.json');
$data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

$out = [];

foreach ($data as $country) {
    $code = $country['alpha-3'] ?? null;

    if (! $code) {
        continue;
    }

    $out[] = [
        'code' => $code,
        'name' => $country['name'],
    ];
}

file_put_contents(__DIR__.'/world_countries.json', json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo count($out).' countries written.'.PHP_EOL;
