<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['es', 'val'], true), 404);

        session(['locale' => $locale]);

        return redirect()->to($this->resolveRedirectTo($request));
    }

    private function resolveRedirectTo(Request $request): string
    {
        $fallback = route('basket.inicio');
        $redirectTo = (string) $request->input('redirect_to', '');

        if ($redirectTo === '') {
            return $fallback;
        }

        $parts = parse_url($redirectTo);

        if ($parts === false) {
            return $fallback;
        }

        if (! isset($parts['scheme'], $parts['host'])) {
            return str_starts_with($redirectTo, '/') ? $redirectTo : $fallback;
        }

        if ($parts['host'] !== $request->getHost()) {
            return $fallback;
        }

        $path = $parts['path'] ?? '/';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return $path.$query.$fragment;
    }
}
