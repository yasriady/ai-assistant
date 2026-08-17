<?php

namespace App\Http\Controllers;

use App\Services\Term\ActiveTerm;
use App\Support\AcademicTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TermController extends Controller
{
    public function __invoke(Request $request, string $term, ActiveTerm $activeTerm): RedirectResponse
    {
        if (! AcademicTerm::isValid($term)) {
            abort(404);
        }

        $activeTerm->set($term, $request->user());

        return back();
    }
}
