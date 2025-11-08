<?php

namespace App\Traits;

use App\Helpers\GeneralSettingsHelper;

trait HasSettingsAttributes
{
    public function getMoreConfigNameAttribute()
    {
        return GeneralSettingsHelper::getNameAttribute();
    }
}
