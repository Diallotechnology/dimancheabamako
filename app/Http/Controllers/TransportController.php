<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreTransportRequest;
use App\Models\Transport;
use App\Models\Zone;
use Inertia\Inertia;

final class TransportController extends Controller
{
    use DeleteAction;

    public function get_trans_zone(Transport $transport)
    {
        return $transport->zones()->get()->map(function ($row) {
            return [
                'label' => "$row->nom", 'value' => "$row->id",
            ];
        });
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransportRequest $request)
    {
        $item = Transport::create($request->validated());
        $item->zones()->attach($request->zone_id);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Transport $transport)
    {
        return Inertia::render('Admin/Transport/Show', compact('transport'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transport $transport)
    {
        $transport->load('zones');
        $zone = Zone::all()->map(function ($row) {
            return [
                'label' => "$row->nom", 'value' => "$row->id",
            ];
        });

        return Inertia::render('Admin/Transport/Update', compact('zone', 'transport'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreTransportRequest $request, Transport $transport)
    {
        $transport->update($request->validated());
        $transport->zones()->sync($request->zone_id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transport $transport)
    {
        return $this->supp($transport);
    }
}
