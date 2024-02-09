<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $revenu = Order::withSum('products as totaux', 'order_product.montant')->get('totaux')->sum('totaux');
        $order = Order::count();
        $product = Product::count();
        $categorie = Category::count();

        return Inertia::render('Admin/Dashboard', compact('order', 'product', 'revenu', 'categorie'));
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

        return Inertia::render('Admin/Product/Index', compact('rows', 'filter', 'category'));
    }

    public function order()
    {
        $rows = Order::withSum('products as totaux', 'order_product.montant')->when(Request::input('search'), function ($query, $search) {
            $query->where('reference', 'like', '%'.$search.'%')
                ->orwhere('adresse', 'like', '%'.$search.'%')
                ->orwhere('ville', 'like', '%'.$search.'%')
                ->orwhere('pays', 'like', '%'.$search.'%')
                ->orwhere('payment', 'like', '%'.$search.'%')
                ->orwhere('postal', 'like', '%'.$search.'%');
        })->when(Request::input('etat'), function ($query, $etat) {
            $query->where('etat', $etat);
        })->when(Request::input('client_id'), function ($query, $client_id) {
            $query->where('client_id', $client_id);
        })->when(Request::input('date'), function ($query, $date) {
            $query->whereDate('created_at', '=', $date);
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('search', 'etat', 'client_id', 'date');
        $client = Client::all();

        return Inertia::render('Admin/Order/Index', compact('rows', 'filter', 'client'));
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

    public function client()
    {
        $rows = Client::when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%')
                ->orwhere('prenom', 'like', '%'.$search.'%')
                ->orwhere('email', 'like', '%'.$search.'%')
                ->orwhere('contact', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('search');

        return Inertia::render('Admin/Client/Index', compact('rows', 'filter'));
    }

    public function image()
    {
        return Inertia::render('Dashboard');
    }

    public function user()
    {
        $rows = User::when(Request::input('search'), function ($query, $search) {
            $query->where('name', 'like', '%'.$search.'%')
                ->orwhere('email', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('search');

        return Inertia::render('Admin/User/Index', compact('rows', 'filter'));
    }
}
