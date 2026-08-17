<?php

namespace App\Http\Middleware;

use App\Services\Term\ActiveTerm;
use App\Support\AcademicTerm;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetActiveTerm
{
    public function __construct(private ActiveTerm $activeTerm) {}

    public function handle(Request $request, Closure $next): Response
    {
        $term = $this->activeTerm->current();

        if (! AcademicTerm::isValid($term)) {
            $this->activeTerm->set(AcademicTerm::current());
            $term = $this->activeTerm->current();
        }

        view()->share('activeTermCode', $term);
        view()->share('activeTermLabel', AcademicTerm::label($term));
        view()->share('termOptions', $this->activeTerm->selectableOptions());

        return $next($request);
    }
}
