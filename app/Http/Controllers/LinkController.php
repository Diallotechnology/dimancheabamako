<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class LinkController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function home()
    {
        return Inertia::render('Home');
    }

    public function contact()
    {
        return Inertia::render('Contact');
    }
}
