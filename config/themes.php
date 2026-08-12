<?php

// Preset accent colors an admin can pick from Settings > General > Theme
// Color (App\Http\Controllers\Admin\SettingsController::general/updateGeneral).
// Each entry's 'light'/'dark' hex sets are written straight into the
// --color-primary-* CSS custom properties by
// resources/views/layouts/partials/theme-style.blade.php, which is what
// `bg-blue-600`, `text-blue-600`, `ring-blue-500` etc. actually resolve to
// everywhere in the app (see tailwind.config.js's `blue: cssVarColor('primary')`).
// 'dark' values are the 'light' values with shades swapped 50<->900,
// 100<->800, 200<->700, 300<->600, 400<->500 - matching the same rule used
// for gray/green/red/etc. in resources/css/app.css, so a preset looks
// exactly as considered in dark mode as it does in light.

return [
    'default' => 'blue',

    'presets' => [
        'blue' => [
            'label' => 'Blue',
            'swatch' => '#2563eb',
            'light' => [
                50 => '#eff6ff', 100 => '#dbeafe', 200 => '#bfdbfe', 300 => '#93c5fd', 400 => '#60a5fa',
                500 => '#3b82f6', 600 => '#2563eb', 700 => '#1d4ed8', 800 => '#1e40af', 900 => '#1e3a8a',
            ],
            'dark' => [
                50 => '#1e3a8a', 100 => '#1e40af', 200 => '#1d4ed8', 300 => '#2563eb', 400 => '#3b82f6',
                500 => '#60a5fa', 600 => '#93c5fd', 700 => '#bfdbfe', 800 => '#dbeafe', 900 => '#eff6ff',
            ],
        ],
        'indigo' => [
            'label' => 'Indigo',
            'swatch' => '#4f46e5',
            'light' => [
                50 => '#eef2ff', 100 => '#e0e7ff', 200 => '#c7d2fe', 300 => '#a5b4fc', 400 => '#818cf8',
                500 => '#6366f1', 600 => '#4f46e5', 700 => '#4338ca', 800 => '#3730a3', 900 => '#312e81',
            ],
            'dark' => [
                50 => '#312e81', 100 => '#3730a3', 200 => '#4338ca', 300 => '#4f46e5', 400 => '#6366f1',
                500 => '#818cf8', 600 => '#a5b4fc', 700 => '#c7d2fe', 800 => '#e0e7ff', 900 => '#eef2ff',
            ],
        ],
        'purple' => [
            'label' => 'Purple',
            'swatch' => '#9333ea',
            'light' => [
                50 => '#faf5ff', 100 => '#f3e8ff', 200 => '#e9d5ff', 300 => '#d8b4fe', 400 => '#c084fc',
                500 => '#a855f7', 600 => '#9333ea', 700 => '#7e22ce', 800 => '#6b21a8', 900 => '#581c87',
            ],
            'dark' => [
                50 => '#581c87', 100 => '#6b21a8', 200 => '#7e22ce', 300 => '#9333ea', 400 => '#a855f7',
                500 => '#c084fc', 600 => '#d8b4fe', 700 => '#e9d5ff', 800 => '#f3e8ff', 900 => '#faf5ff',
            ],
        ],
        'green' => [
            'label' => 'Green',
            'swatch' => '#16a34a',
            'light' => [
                50 => '#f0fdf4', 100 => '#dcfce7', 200 => '#bbf7d0', 300 => '#86efac', 400 => '#4ade80',
                500 => '#22c55e', 600 => '#16a34a', 700 => '#15803d', 800 => '#166534', 900 => '#14532d',
            ],
            'dark' => [
                50 => '#14532d', 100 => '#166534', 200 => '#15803d', 300 => '#16a34a', 400 => '#22c55e',
                500 => '#4ade80', 600 => '#86efac', 700 => '#bbf7d0', 800 => '#dcfce7', 900 => '#f0fdf4',
            ],
        ],
        'red' => [
            'label' => 'Red',
            'swatch' => '#dc2626',
            'light' => [
                50 => '#fef2f2', 100 => '#fee2e2', 200 => '#fecaca', 300 => '#fca5a5', 400 => '#f87171',
                500 => '#ef4444', 600 => '#dc2626', 700 => '#b91c1c', 800 => '#991b1b', 900 => '#7f1d1d',
            ],
            'dark' => [
                50 => '#7f1d1d', 100 => '#991b1b', 200 => '#b91c1c', 300 => '#dc2626', 400 => '#ef4444',
                500 => '#f87171', 600 => '#fca5a5', 700 => '#fecaca', 800 => '#fee2e2', 900 => '#fef2f2',
            ],
        ],
        'orange' => [
            'label' => 'Orange',
            'swatch' => '#ea580c',
            'light' => [
                50 => '#fff7ed', 100 => '#ffedd5', 200 => '#fed7aa', 300 => '#fdba74', 400 => '#fb923c',
                500 => '#f97316', 600 => '#ea580c', 700 => '#c2410c', 800 => '#9a3412', 900 => '#7c2d12',
            ],
            'dark' => [
                50 => '#7c2d12', 100 => '#9a3412', 200 => '#c2410c', 300 => '#ea580c', 400 => '#f97316',
                500 => '#fb923c', 600 => '#fdba74', 700 => '#fed7aa', 800 => '#ffedd5', 900 => '#fff7ed',
            ],
        ],
        'amber' => [
            'label' => 'Amber',
            'swatch' => '#d97706',
            'light' => [
                50 => '#fffbeb', 100 => '#fef3c7', 200 => '#fde68a', 300 => '#fcd34d', 400 => '#fbbf24',
                500 => '#f59e0b', 600 => '#d97706', 700 => '#b45309', 800 => '#92400e', 900 => '#78350f',
            ],
            'dark' => [
                50 => '#78350f', 100 => '#92400e', 200 => '#b45309', 300 => '#d97706', 400 => '#f59e0b',
                500 => '#fbbf24', 600 => '#fcd34d', 700 => '#fde68a', 800 => '#fef3c7', 900 => '#fffbeb',
            ],
        ],
    ],
];
