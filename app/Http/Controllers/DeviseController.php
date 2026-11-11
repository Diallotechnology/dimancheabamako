<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreDeviseRequest;
use App\Models\Devise;
use Inertia\Inertia;

final class DeviseController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDeviseRequest $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Devise $devise)
    {
        return Inertia::render('Admin/Devise/Update', compact('devise'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreDeviseRequest $request, Devise $devise)
    {
        $devise->update($request->validated());

        return back();
    }
}
