<?php

namespace App\Http\Middleware;

use App\Constant\PathName;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        PathName::POST . '/*',
        PathName::PATCH . '/*',
        PathName::DELETE . '/*',
        PathName::BATCH,
        PathName::registerForUser
    ];
}
