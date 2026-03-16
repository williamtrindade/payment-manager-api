<?php

return [
    'g1' => [
        'url'    => env('G1_URL', 'http://betalent-gateways:3001'),
        'client' => env('G1_CLIENT_ID'),
        'secret' => env('G1_CLIENT_SECRET'),
    ],
    'g2' => [
        'url'    => env('G2_URL', 'http://betalent-gateways:3002'),
        'client' => env('G2_CLIENT_ID'),
        'secret' => env('G2_CLIENT_SECRET'),
    ],
];
