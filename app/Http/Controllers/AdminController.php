<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Shipping;
use App\Models\Slide;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $query = Order::withSum('products as totaux', 'order_product.montant');
        $revenu = $query->whereMonth('created_at', date('m'))->get('totaux')->sum('totaux');

        $order = Order::count();
        $product = Product::count();
        $categorie = Category::count();
        $lastorder = $query->take(10)->latest('id')->get();
        $order_stat = Order::selectRaw('COUNT(id) as total_order, DATE(created_at) as day')
            ->orderBy('day')->groupBy('day')->pluck('total_order', 'day');

        return Inertia::render('Admin/Dashboard', compact('order', 'product', 'revenu', 'categorie', 'lastorder', 'order_stat'));
    }

    public function product()
    {
        $rows = Product::when(Request::input('filters.search'), function ($query, $search) {

            $query->where('nom', 'like', '%'.$search.'%')->orwhere('color', 'like', '%'.$search.'%');
        })->when(Request::input('filters.cat'), function ($query, $cat) {
            $query->where('categorie_id', $cat);
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('filters.search', 'filters.cat');
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

    public function slide()
    {
        $rows = Slide::latest('id')->get();

        return Inertia::render('Admin/Slide/Index', compact('rows'));
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
        $country = Country::all();

        return Inertia::render('Admin/Client/Index', compact('rows', 'filter', 'country'));
    }

    public function promotion()
    {
        $rows = Promotion::when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('search');

        return Inertia::render('Admin/Promotion/Index', \compact('filter', 'rows'));
    }

    public function zone()
    {
        $rows = Zone::with('countries')->when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10);
        $filter = Request::only('search');
        $pays = countries();

        return Inertia::render('Admin/Zone/Index', compact('filter', 'rows', 'pays'));
    }

    public function country()
    {
        $rows = Country::with('zone')->when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10);
        $filter = Request::only('search');
        $countries = countries();
        $zone = Zone::all();

        return Inertia::render('Admin/Pays/Index', compact('filter', 'rows', 'zone', 'countries'));
    }

    public function shipping()
    {
        $rows = Shipping::with('country', 'transport')->when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10)->withQueryString();
        $filter = Request::only('search');

        $transport = Transport::all();

        return Inertia::render('Admin/Shipping/Index', compact('filter', 'rows', 'transport'));
    }

    public function transport()
    {
        $rows = Transport::with('zones')->when(Request::input('search'), function ($query, $search) {
            $query->where('nom', 'like', '%'.$search.'%');
        })->latest('id')->paginate(10)->withQueryString();
        $zone = Zone::all();
        $filter = Request::only('search');

        return Inertia::render('Admin/Transport/Index', compact('filter', 'rows', 'zone'));
    }

    public function maintenance()
    {
        return Artisan::call('down --with-secret');
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
