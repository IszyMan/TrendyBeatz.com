<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StaticPageController extends Controller
{
    public function privacyPolicy(): View
    {
        return view('pages.privacy');
    }

    public function aboutUs(): View
    {
        return view('pages.aboutus');
    }

    public function termsOfUse(): View
    {
        return view('pages.terms-of-use');
    }

    public function contactUs(): View
    {
        return view('pages.contactus');
    }

    public function advertiseWithUs(): View
    {
        return view('pages.advertise');
    }

    public function promoteMusic(): View
    {
        return view('pages.promote-music');
    }

    public function disclaimer(): View
    {
        return view('pages.disclaimer');
    }

    public function dmca(): View
    {
        return view('pages.dmca');
    }
}