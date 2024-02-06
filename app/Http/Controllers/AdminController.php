<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Support\Facades\Request;
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
        $rows = Category::when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('search');

        return Inertia::render('Admin/Category/Index', \compact('rows', 'filter'));
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
