<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Request;
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

    public function shop()
    {
        $rows = Product::with('categorie')->when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%')->orwhere('color', 'like', '%'.$search.'%');
        })->when(Request::input('cat'), function ($query, $cat) {
            $query->where('categorie_id', $cat);
        })->latest('id')->paginate(15)->withQueryString();
        $filter = Request::only('search', 'cat');
        $category = Category::all();

        return Inertia::render('Contact', \compact('category', 'filter'));
    }
}
