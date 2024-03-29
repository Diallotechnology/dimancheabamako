<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreCountryRequest;
use App\Models\Country;
use App\Models\Zone;
use Countries;
use Illuminate\Support\Collection;
use Inertia\Inertia;

class CountryController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCountryRequest $request)
    {
        Country::create($request->validated());

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Country $country)
    {
        $countryNames = new Collection(Countries::getList('fr'));
        $pays = $countryNames->values()->map(function ($row) {
            return [
                'label' => $row, 'value' => $row,
            ];
        });

        $zone = Zone::all()->map(function ($row) {
            return [
                'label' => "$row->nom", 'value' => "$row->id",
            ];
        });

        return Inertia::render('Admin/Pays/Update', compact('zone', 'country', 'pays'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreCountryRequest $request, Country $country)
    {
        $country->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Country $country)
    {
        return $this->supp($country);
    }
}
