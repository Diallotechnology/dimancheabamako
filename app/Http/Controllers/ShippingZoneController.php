<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Models\ShippingPays;
use App\Models\ShippingZone;
use Inertia\Inertia;

class ShippingZoneController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreZoneRequest $request)
    {
        $item = ShippingZone::create($request->validated());
        $data = [];
        foreach ($request->pays as $value) {
            $data[] = new ShippingPays(['nom' => $value]);
        }
        $item->ShippingPays()->saveMany($data);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingZone $zone)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShippingZone $zone)
    {
        dd($zone);

        return Inertia::render('Admin/Zone/Update', compact('zone'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateZoneRequest $request, ShippingZone $zone)
    {
        $zone->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingZone $zone)
    {
        return $this->supp($zone);
    }
}
