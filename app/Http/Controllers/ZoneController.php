<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\Pays;
use App\Models\Zone;
use Inertia\Inertia;

class ZoneController extends Controller
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
            $data[] = new Pays(['nom' => $value]);
        }
        $item->pays()->saveMany($data);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Zone $zone)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Zone $zone)
    {

        return Inertia::render('Admin/Zone/Update', compact('zone'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateZoneRequest $request, Zone $zone)
    {
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
