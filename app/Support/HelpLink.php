<?php

namespace App\Support;

use App\Filament\Pages\Help\HowItWorks;
use App\Filament\Pages\Help\LeaveAndAttendance;
use App\Filament\Pages\Help\PayrollGuide;
use App\Filament\Pages\Help\RecruitmentGuide;
use Illuminate\Support\HtmlString;

/**
 * Form helper text that ends with a link to the matching Help & Documentation page.
 */
class HelpLink
{
    public static function roles(?string $text = null): HtmlString
    {
        return self::make($text, HowItWorks::getUrl().'#roles', 'What each role can do');
    }

    public static function leave(?string $text = null): HtmlString
    {
        return self::make($text, LeaveAndAttendance::getUrl(), 'Leave rules');
    }

    public static function payroll(?string $text = null): HtmlString
    {
        return self::make($text, PayrollGuide::getUrl(), 'How payroll works');
    }

    public static function recruitment(?string $text = null): HtmlString
    {
        return self::make($text, RecruitmentGuide::getUrl(), 'Recruitment guide');
    }

    private static function make(?string $text, string $url, string $label): HtmlString
    {
        $link = '<a href="'.e($url).'" class="font-medium text-primary-600 underline hover:no-underline dark:text-primary-400">'.e($label).'</a>';

        return new HtmlString(trim(e((string) $text).' '.$link));
    }
}
