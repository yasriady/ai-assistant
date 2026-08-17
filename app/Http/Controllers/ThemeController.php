<?php

namespace App\Http\Controllers;

use App\Services\Theme\UserTheme;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function __invoke(Request $request, string $theme, UserTheme $userTheme): RedirectResponse
    {
        $userTheme->set($userTheme->assertValid($theme), $request->user());

        return back();
    }
}
