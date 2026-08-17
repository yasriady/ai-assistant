<?php

namespace App\Http\Middleware;

use App\Services\Theme\UserTheme;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetTheme
{
    public function __construct(private readonly UserTheme $userTheme) {}

    public function handle(Request $request, Closure $next): Response
    {
        $theme = $this->userTheme->current();
        View::share('currentTheme', $theme);

        return $next($request);
    }
}
