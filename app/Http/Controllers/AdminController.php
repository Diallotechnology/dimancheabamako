<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        return Inertia::render('Admin/Dashboard');
    }

    public function product()
    {
        return Inertia::render('Admin/Product/Index');
    }

    public function order()
    {
        return Inertia::render('Admin/Order/Index');
    }

    public function category()
    {
        return Inertia::render('Admin/Category/Index');
    }

    public function image()
    {
        return Inertia::render('Dashboard');
    }

    public function user()
    {
        return Inertia::render('Admin/User/Index');
    }
}
