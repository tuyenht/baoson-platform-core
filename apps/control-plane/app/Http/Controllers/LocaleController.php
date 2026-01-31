<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class LocaleController extends Controller
{
    /**
     * Supported locales
     *
     * @var array<int, string>
     */
    protected array $supportedLocales = ['en', 'vi', 'ja', 'zh'];

    /**
     * Switch application locale
     */
    public function store(Request $request)
    {
        $locale = $request->input('locale', 'en');

        if (! in_array($locale, $this->supportedLocales, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        // Persist also as cookie for immediate UX on the next request (login submit, hard reload, etc)
        $cookie = cookie('locale', $locale, 60 * 24 * 365, '/');

        if ($request->expectsJson() || $request->ajax()) {
            return response()->noContent()->withCookie($cookie);
        }

        return redirect()->back()->withCookie($cookie);
    }
}

