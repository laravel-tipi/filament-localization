<?php

declare(strict_types=1);

return [
    'navigation' => [
        'group' => 'Localization',
        'label' => 'Locales',
    ],
    'model' => [
        'singular' => 'locale',
        'plural' => 'locales',
    ],
    'fields' => [
        'code' => 'Locale code',
        'name' => 'Name',
        'native_name' => 'Native name',
        'native_abbreviation' => 'Native abbreviation',
        'country_code' => 'Country code',
        'text_direction' => 'Text direction',
        'active' => 'Active',
        'status' => 'Status',
    ],
    'helpers' => [
        'code' => 'For example: en, en-US or zh-Hans. The code cannot be changed later.',
        'country_code' => 'Two uppercase letters, used for the flag (for example: GB).',
        'active' => 'New locales are inactive until activated. The first locale is always active and the default.',
    ],
    'text_directions' => [
        'ltr' => 'Left to right',
        'rtl' => 'Right to left',
    ],
    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'default' => 'Default',
    ],
    'tabs' => [
        'all' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ],
    'actions' => [
        'create' => ['label' => 'Create locale', 'success' => 'Locale created'],
        'edit' => ['label' => 'Edit', 'success' => 'Locale updated'],
        'activate' => ['label' => 'Activate', 'success' => 'Locale activated'],
        'deactivate' => ['label' => 'Deactivate', 'success' => 'Locale deactivated'],
        'make_default' => ['label' => 'Make default', 'success' => 'Default locale updated'],
        'delete' => ['label' => 'Delete', 'success' => 'Locale deleted'],
    ],
    'failure' => 'The operation could not be completed',
];
