<?php

namespace App\Helpers;

use App\Models\Company;

class GeneralSettingsHelper
{
    public static function getNameAttribute()
    {
        $settings = Company::first();

        return $settings->name ?? 'Test Company';
    }
}
