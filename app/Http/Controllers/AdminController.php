<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\OrderEnum;
use App\Enum\ProductStatus;
use App\Enum\RoleEnum;
use App\Models\Category;
use App\Models\Client;
use App\Models\Country;
use App\Models\Devise;
use App\Models\Order;
use App\Models\PayLink;
use App\Models\Poids;
use App\Models\Product;
use App\Models\Promotion;
use App\Models\Shipping;
use App\Models\Slide;
use App\Models\Transport;
use App\Models\User;
use App\Models\Zone;
use App\Support\Options;
use Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class AdminController extends Controller
{
    public function dashboard()
    {
        // Base query pour Sum sur pivot
        $query = Order::withSum('products as totaux', 'order_product.montant');

        // Base commune des KPI « Revenu du mois » et « Ventes réglées » : commandes réglées
        // (trans_state = PURCHASED) du mois civil courant, année comprise.
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $paidThisMonth = fn () => Order::query()
            ->whereTransState('PURCHASED')
            ->whereBetween('created_at', [$monthStart, $monthEnd]);

        $revenu = $paidThisMonth()->withSum('products as totaux', 'order_product.montant')->get()->sum('totaux');
        $order = $paidThisMonth()->count();
        $product = DB::table('products')->count();
        $categorie = DB::table('categories')->count();

        // Optimisation N+1
        $lastorder = (clone $query)
            ->with([
                'transport:id,nom',
                'client:id,nom,email',
                'country:id,nom',
                'products:id,nom,cover',
            ])
            ->latest('id')
            ->take(10)
            ->get();

        // Graphique : volontairement TOUTES les commandes par jour (payées ou non, toutes périodes),
        // pour suivre l'activité brute ; il n'est donc pas comparable aux KPI ci-dessus (libellé Vue explicite).
        $order_stat = Order::selectRaw('COUNT(id) as total_order, DATE(created_at) as day')
            ->orderBy('day')
            ->groupBy('day')
            ->pluck('total_order', 'day');

        $label = $order_stat->keys();
        $data = $order_stat->values();

        return Inertia::render('Admin/Dashboard', compact(
            'order',
            'product',
            'revenu',
            'categorie',
            'lastorder',
            'data',
            'label'
        ));
    }

    public function product(Request $request)
    {
        $rows = Product::with('categorie:id,nom')->when($request->filled('search'), function ($q) use ($request) {
            $search = $request->search;
            $q->where(function ($qq) use ($search) {
                $qq->whereAny(['nom', 'color', 'taille', 'reference'], 'LIKE', "%$search%")
                    ->orWhere('prix', $search)
                    ->orWhere('poids', $search)
                    ->orWhereHas('categorie', fn ($c) => $c->where('nom', 'LIKE', "%$search%"));
            });
        })
            ->when($request->filled('category'), fn ($query) => $query->where('categorie_id', $request->integer('category')))
            ->when($request->filled('favoris'), fn ($query) => $query->where('favoris', $request->boolean('favoris')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_preorder', $request->boolean('status')))
            ->when($request->enum('statut', ProductStatus::class), fn ($query, ProductStatus $statut) => $query->where('status', $statut))
            ->latest('id')->paginate(10)->withQueryString();

        $category = Category::select('id', 'nom')->get();

        return Inertia::render('Admin/Product/Index', compact('rows', 'category'));
    }

    public function order(Request $request)
    {
        $rows = Order::query()
            ->withSum('products as totaux', 'order_product.montant')
            ->with(['transport:id,nom', 'client:id,nom,prenom,email,contact', 'country:id,nom'])
            ->when($request->string('search')->trim()->toString(), function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereAny(
                        ['reference', 'adresse', 'ville', 'postal', 'trans_state', 'trans_ref', 'etat'],
                        'LIKE',
                        "%{$search}%"
                    )
                        ->orWhereHas(
                            'client',
                            fn ($c) => $c->whereAny(['nom', 'prenom', 'email'], 'LIKE', "%{$search}%")
                        );
                });
            })
            ->when($request->filled('date'), fn ($query) => $query->whereDate('created_at', $request->input('date')))
            ->when($request->filled('client'), fn ($query) => $query->where('client_id', $request->integer('client')))
            ->when($request->filled('status'), fn ($query) => $query->where('etat', $request->input('status')))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $client = Client::select('id', 'nom', 'prenom')->get();
        $order_status = OrderEnum::all();

        return Inertia::render('Admin/Order/Index', compact('rows', 'client', 'order_status'));
    }

    public function slide()
    {
        $rows = Slide::latest('id')->get();

        return Inertia::render('Admin/Slide/Index', compact('rows'));
    }

    public function category(Request $request)
    {
        $rows = Category::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where('nom', 'LIKE', "%$search%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Category/Index', compact('rows'));
    }

    public function paylink(Request $request)
    {
        $rows = PayLink::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($qq) use ($search) {
                    $qq->whereAny(['name', 'contact'], 'LIKE', "%$search%")
                        ->orWhere('montant', $search);
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Paybylink/Index', compact('rows'));
    }

    public function client(Request $request)
    {
        $rows = Client::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->whereAny(['prenom', 'nom', 'email', 'contact'], 'LIKE', "%$search%");
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // `clients.pays` stocke le nom du pays (et non son id) : le nom sert aussi de valeur.
        $country = Options::fromNames(Country::orderBy('nom')->pluck('nom'));

        return Inertia::render('Admin/Client/Index', compact('rows', 'country'));
    }

    public function promotion(Request $request)
    {
        $rows = Promotion::query()
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('nom', 'LIKE', "%{$request->search}%")
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(function ($item) {
                return [
                    ...$item->toArray(),
                    'debut' => $item->debutat(),
                    'fin' => $item->finat(),
                ];
            });

        return Inertia::render('Admin/Promotion/Index', compact('rows'));
    }

    public function zone(Request $request)
    {
        $rows = Zone::with('countries')
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('nom', 'LIKE', "%{$request->search}%")
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        // Liste des pays manquants (uniquement ceux NON enregistrés en DB)
        $allCountries = collect(Countries::getList('fr'));
        $existing = Country::pluck('nom')->all();

        $pays = Options::fromNames($allCountries->reject(fn ($name) => in_array($name, $existing)));

        return Inertia::render('Admin/Zone/Index', compact('rows', 'pays'));
    }

    public function country(Request $request)
    {
        $rows = Country::with('zone')
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('nom', 'LIKE', "%{$request->search}%")
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $countries = Options::fromNames(Countries::getList('fr'));

        $zone = Options::from(Zone::orderBy('nom')->get(), fn (Zone $row): string => $row->nom);

        return Inertia::render('Admin/Pays/Index', compact('rows', 'zone', 'countries'));
    }

    public function shipping(Request $request)
    {
        $rows = Shipping::with(['zone', 'transport', 'poids'])
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(
                    fn ($qq) => $qq->where('temps', 'LIKE', "%$search%")
                        ->orWhere('montant', 'LIKE', "%$search%")
                )
                    ->orWhereHas(
                        'transport',
                        fn ($t) => $t->where('nom', 'LIKE', "%$search%")
                    );
            })
            ->latest('id')
            ->get()
            ->groupBy(fn ($row) => $row->transport?->nom ?? 'Autre');

        $poids = Options::from(Poids::orderBy('min')->get(), fn (Poids $row): string => "{$row->min} à {$row->max} Kg");

        $transport = Transport::orderBy('nom')->get();

        return Inertia::render('Admin/Shipping/Index', compact('rows', 'transport', 'poids'));
    }

    public function poids(Request $request)
    {
        $rows = Poids::when(
            $request->filled('search'),
            fn ($q) => $q->whereAny(['min', 'max'], 'LIKE', "%{$request->search}%")
        )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/Poids/Index', [
            'rows' => $rows,
        ]);
    }

    public function devise()
    {
        $rows = Devise::paginate(10)->withQueryString();

        return Inertia::render('Admin/Devise/Index', compact('rows'));
    }

    public function transport(Request $request)
    {
        $rows = Transport::with('zones')
            ->when(
                $request->filled('search'),
                fn ($q) => $q->where('nom', 'LIKE', "%{$request->search}%")
            )
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $zone = Options::from(Zone::orderBy('nom')->get(), fn (Zone $row): string => $row->nom);

        return Inertia::render('Admin/Transport/Index', [
            'rows' => $rows,
            'zone' => $zone,
        ]);
    }

    public function user(Request $request)
    {
        $rows = User::when(
            $request->filled('search'),
            fn ($q) => $q->whereAny(['name', 'email'], 'LIKE', "%{$request->search}%")
        )
            ->where('role', '!=', RoleEnum::CUSTOMER->value)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        $roles = Options::from(
            [RoleEnum::ADMIN, RoleEnum::SECRTETAIRE],
            fn (RoleEnum $role): string => $role->label(),
            fn (RoleEnum $role): string => $role->value,
        );

        return Inertia::render('Admin/User/Index', [
            'rows' => $rows,
            'roles' => $roles,
        ]);
    }

    public function customer(Request $request)
    {
        $rows = User::when(
            $request->filled('search'),
            fn ($q) => $q->whereAny(['name', 'email'], 'LIKE', "%{$request->search}%")
        )
            ->where('role', RoleEnum::CUSTOMER->value)
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/User/Client', [
            'rows' => $rows,
        ]);
    }

    /**
     * Bascule le mode maintenance. Inertia::location force un rechargement complet :
     * la cible (vitrine ou lien secret de contournement) n'est pas une page Inertia.
     */
    public function maintenance(): SymfonyResponse
    {
        if (App::isDownForMaintenance()) {
            Artisan::call('up');
            Session::put('down_message', 'Le mode maintenance a été désactivé avec succès!');

            return Inertia::location(route('home'));
        }
        $token = Str::random(60);
        Artisan::call("down --secret='$token'");
        Session::put('down_token', $token);
        Session::put('down_message', 'Le mode maintenance a été activer avec succès!');

        return Inertia::location(url('/'.$token));
    }
}
