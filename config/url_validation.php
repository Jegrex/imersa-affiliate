<?php

return [
    'allowlists' => [
        'marketplace' => array_values(array_filter(array_map('trim', explode(',', env('URL_ALLOWLIST_MARKETPLACE', ''))))),
        'image' => array_values(array_filter(array_map('trim', explode(',', env('URL_ALLOWLIST_IMAGE', ''))))),
        'video' => array_values(array_filter(array_map('trim', explode(',', env('URL_ALLOWLIST_VIDEO', ''))))),
    ],
];
