<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StorePaysRequest;
use App\Http\Requests\UpdatePaysRequest;
use App\Models\Pays;
use App\Models\Zone;
use Inertia\Inertia;

class PaysController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaysRequest $request)
    {
        Pays::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Pays $pays)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Pays $pays)
    {
        $zone = Zone::all();

        return Inertia::render('Admin/Pays/Update', compact('zone', 'pays'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaysRequest $request, Pays $pays)
    {
        $pays->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Pays $pays)
    {
        return $this->supp($pays);
    }
}
