<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Locales the app ships translations for. Add more here as you create lang/<code>.json.
     */
    public const SUPPORTED = ['en', 'es'];

    /**
     * Resolve the request locale with this precedence:
     *   1. ?lang= override (persisted to the session)
     *   2. previously chosen locale stored in the session
     *   3. the browser's Accept-Language header
     *   4. the app's configured fallback
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->fromQuery($request)
            ?? $request->session()->get('locale')
            ?? $this->fromBrowser($request)
            ?? config('app.locale');

        $request->session()->put('locale', $locale);
        App::setLocale($locale);

        return $next($request);
    }

    protected function fromQuery(Request $request): ?string
    {
        $lang = $request->query('lang');

        return in_array($lang, self::SUPPORTED, true) ? $lang : null;
    }

    protected function fromBrowser(Request $request): ?string
    {
        // getLanguages() returns the Accept-Language values ordered by quality.
        foreach ($request->getLanguages() as $language) {
            $primary = substr($language, 0, 2);
            if (in_array($primary, self::SUPPORTED, true)) {
                return $primary;
            }
        }

        return null;
    }
}
