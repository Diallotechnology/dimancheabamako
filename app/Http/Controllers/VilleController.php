<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreVilleRequest;
use App\Models\Country;
use App\Models\Ville;
use Inertia\Inertia;

class VilleController extends Controller
{
    use DeleteAction;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVilleRequest $request)
    {
        Ville::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Ville $ville)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Ville $ville)
    {
        $country = Country::all();

        return Inertia::render('Admin/Ville/Update', compact('ville', 'country'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreVilleRequest $request, Ville $ville)
    {
        $ville->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ville $ville)
    {
        return $this->supp($ville);
    }
}
