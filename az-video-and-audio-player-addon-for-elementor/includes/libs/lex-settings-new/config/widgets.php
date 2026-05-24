<?php
/**
 * Widget Configuration
 * 
 * Define which widgets to render and in what order.
 * 
 * Each widget can have:
 * - enabled: (bool) Whether to show this widget (default: true)
 * - variant: (string) Widget variant/style (if supported by widget)
 * - data: (array) Additional widget-specific data
 * 
 * Widgets are rendered in the order they appear in this array.
 */

return [
    'support' => [
        'enabled' => false,
    ],
    'support-v2' => [
        'enabled' => true,
    ],
    'hire' => [
        'enabled' => false,
    ],
    'upgrade' => [
        'enabled' => false,
        'variant' => 'gradient',
    ],
    'review' => [
        'enabled' => false,
    ],
    'trial' => [
        'enabled' => false,
        'variant' => 'v1',
    ],
    'products' => [
        'enabled' => false,
    ],
];

