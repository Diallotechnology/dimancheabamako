<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StorePoidsRequest;
use App\Models\Poids;
use Inertia\Inertia;

class PoidsController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePoidsRequest $request)
    {
        Poids::create($request->validated());

        return back();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Poids $poids)
    {
        return Inertia::render('Admin/Poids/Update', compact('poids'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePoidsRequest $request, Poids $poids)
    {
        $poids->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Poids $poids)
    {
        return $this->supp($poids);
    }
}
