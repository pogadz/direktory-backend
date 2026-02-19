<?php

return [
    App\Providers\AppServiceProvider::class,
    ...(class_exists(\Knuckles\Scribe\ScribeServiceProvider::class)
        ? [\Knuckles\Scribe\ScribeServiceProvider::class]
        : []),
];
