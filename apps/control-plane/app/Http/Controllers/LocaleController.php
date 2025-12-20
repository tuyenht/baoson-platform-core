<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
    public function store(Request $request): RedirectResponse
    {
        $locale = $request->input('locale', 'en');

        if (! in_array($locale, $this->supportedLocales, true)) {
            $locale = 'en';
        }

        App::setLocale($locale);
        Session::put('locale', $locale);

        return redirect()->back();
    }
}

