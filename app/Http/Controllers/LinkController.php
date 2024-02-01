<?php

namespace App\Http\Controllers;

class LinkController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function home()
    {
        return inertia('Home');
    }

    public function contact()
    {
        return inertia('Contact');
    }
}
