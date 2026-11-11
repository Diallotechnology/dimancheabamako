<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreCountryRequest;
use App\Models\Country;
use App\Models\Zone;
use App\Support\Options;
use Countries;
use Inertia\Inertia;

final class CountryController extends Controller
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
        $pays = Options::fromNames(Countries::getList('fr'));
        $zone = Options::from(Zone::all(), fn (Zone $row): string => $row->nom);

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
