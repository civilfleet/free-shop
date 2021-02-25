<?php

namespace App\Http\Controllers;

use App\Facades\CurrentCustomer;

class LanguageSelectController extends Controller
{
    public function index()
    {
        return view('language-select', [
            'languages' => config('app.supported_languages'),
        ]);
    }

    public function change(string $lang)
    {
        if (! isset(config('app.supported_languages')[$lang])) {
            $lang = config('app.fallback_locale');
        }

        session()->put('lang', $lang);

        $customer = CurrentCustomer::get();
        if ($customer != null) {
            $customer->locale = $lang;
            $customer->save();
        }

        if (session()->has('requested-url')) {
            return redirect(session()->pull('requested-url'));
        }

        if (url()->previous() != route('languages')) {
            return redirect(url()->previous());
        }

        return redirect()->route('home');
    }
}
