<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreShippingPaysRequest;
use App\Http\Requests\UpdateShippingPaysRequest;
use App\Models\ShippingPays;
use App\Models\ShippingZone;
use Inertia\Inertia;

class ShippingPaysController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShippingPaysRequest $request)
    {
        ShippingPays::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(ShippingPays $shippingPays)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ShippingPays $zone)
    {
        $zone = ShippingZone::all();

        return Inertia::render('Admin/Pays/Update', compact('zone', 'pays'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateShippingPaysRequest $request, ShippingPays $shippingPays)
    {
        $shippingPays->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ShippingPays $shippingPays)
    {
        return $this->supp($shippingPays);
    }
}
