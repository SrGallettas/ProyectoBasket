<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if (in_array($locale, ['es', 'val'], true)) {
            App::setLocale($locale);
            Carbon::setLocale($locale === 'val' ? 'ca' : $locale);
        }

        return $next($request);
    }
}
