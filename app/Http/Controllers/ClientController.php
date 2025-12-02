<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\Country;
use Inertia\Inertia;

final class ClientController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreClientRequest $request)
    {
        Client::create($request->validated());

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Client $client)
    {
        $client->load('orders');

        return Inertia::render('Admin/Client/Show', compact('client'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $country = Country::all();

        return Inertia::render('Admin/Client/Update', compact('client', 'country'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        return $this->supp($client);
    }
}
