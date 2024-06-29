<?php


namespace App\OGC\Helpers;

use Illuminate\Support\Carbon;

class OgcUtil
{
    public static function now(): string
    {
        return Carbon::now('GMT+7');//giờ Hà Nội
    }
}
