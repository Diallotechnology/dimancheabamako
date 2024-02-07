<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
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
        $rows = Product::with('categorie')->when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%')->orwhere('color', 'like', '%'.$search.'%');
        })->when(Request::input('cat'), function ($query, $cat) {
            $query->where('categorie_id', $cat);
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('search', 'cat');
        $category = Category::all();
        // dd($rows);

        return Inertia::render('Admin/Product/Index', compact('rows', 'filter', 'category'));
    }

    public function order()
    {
        return Inertia::render('Admin/Order/Index');
    }

    public function category()
    {
        $rows = Category::when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10)
            ->withQueryString();
        $filter = Request::only('search');

        return Inertia::render('Admin/Category/Index', compact('rows', 'filter'));
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
