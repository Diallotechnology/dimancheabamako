<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreZoneRequest;
use App\Models\Country;
use App\Models\Zone;
use Countries;
use Illuminate\Support\Collection;
use Inertia\Inertia;

final class ZoneController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreZoneRequest $request)
    {
        $item = Zone::create($request->validated());
        $data = [];
        foreach ($request->pays as $value) {
            $data[] = new Country(['nom' => $value]);
        }
        $item->countries()->saveMany($data);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {
        $zone->load('countries');
        $countryNames = new Collection(Countries::getList('fr'));
        $pays = $countryNames->values()->map(function ($row) {
            return [
                'label' => $row, 'value' => $row,
            ];
        });

        return Inertia::render('Admin/Zone/Update', compact('zone', 'pays'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreZoneRequest $request, Zone $zone)
    {

        // Get the pays data from the request
        $newPays = $request->pays;

        // Get the existing pays associated with the Zone
        $existingPays = $zone->countries()->pluck('nom')->toArray();

        // Check if there are any changes in pays
        if ($newPays !== $existingPays) {
            // Update the related Country models
            $zone->countries()->delete(); // Remove existing related countries
            $data = [];
            foreach ($newPays as $value) {
                $data[] = new Country(['nom' => $value]);
            }
            $zone->countries()->saveMany($data); // Save the updated list of countries
        }

        $zone->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Zone $zone)
    {
        return $this->supp($zone);
    }
}
