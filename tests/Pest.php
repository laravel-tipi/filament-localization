<?php

declare(strict_types=1);

use Tipi\Localization\Filament\Tests\DatabaseTestCase;
use Tipi\Localization\Filament\Tests\TestCase;

uses(TestCase::class)->in('Feature/Panels', 'Feature/Switcher');
uses(DatabaseTestCase::class)->in('Feature/Management');
