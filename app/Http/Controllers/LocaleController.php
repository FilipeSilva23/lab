<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, config('rentify.locales'), true), 404);
        session(['locale' => $locale]);
        return back();
    }
}
