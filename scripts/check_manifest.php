<?php

$m = json_decode(file_get_contents(__DIR__.'/../public/build/manifest.json'), true);
foreach ($m as $k => $v) {
    if (! array_key_exists('src', $v)) {
        echo "MISSING SRC: $k\n";
    } else {
        echo "OK: $k -> src={$v['src']}\n";
    }
}
