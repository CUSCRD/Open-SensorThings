<?php


namespace App\API\Helpers;

use Illuminate\Support\Carbon;

class ApiUtil
{
    public static function now(): string
    {
        return Carbon::now('GMT+7'); //giờ Hà Nội
    }
}
