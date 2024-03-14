<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreShippingRequest;
use App\Models\Poids;
use App\Models\Shipping;
use App\Models\Transport;
use Inertia\Inertia;

class ShippingController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreShippingRequest $request)
    {
        Shipping::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Shipping $shipping)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Shipping $shipping)
    {
        $transport = Transport::all();
        $poids = Poids::all()->map(function ($row) {
            return [
                'label' => "$row->min à $row->max Kg", 'value' => "$row->id",
            ];
        });

        return Inertia::render('Admin/Shipping/Update', compact('shipping', 'transport', 'poids'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreShippingRequest $request, Shipping $shipping)
    {
        $shipping->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Shipping $shipping)
    {
        return $this->supp($shipping);
    }
}
