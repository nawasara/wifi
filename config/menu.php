<?php

$prefix = 'nawasara-wifi';

return [
    [
        'label' => 'WiFi Publik',
        'icon' => 'lucide-wifi',
        'url' => '',
        'permission' => 'wifi.point.view',
        'submenu' => [
            [
                'label' => 'Titik WiFi',
                'icon' => 'lucide-router',
                'url' => url($prefix.'/points'),
                'permission' => 'wifi.point.view',
                'navigate' => true,
            ],
        ],
    ],
];
