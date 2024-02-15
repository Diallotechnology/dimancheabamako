<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreTransportRequest;
use App\Http\Requests\UpdateTransportRequest;
use App\Models\Pays;
use App\Models\Transport;
use App\Models\Zone;
use Inertia\Inertia;

class TransportController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTransportRequest $request)
    {
        Transport::create($request->validated());

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
        $zone = Zone::all();
        $pays = Pays::all();

        return Inertia::render('Admin/Transport/Update', compact('zone', 'pays', 'transport'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTransportRequest $request, Transport $transport)
    {
        $transport->update($request->validated());

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
