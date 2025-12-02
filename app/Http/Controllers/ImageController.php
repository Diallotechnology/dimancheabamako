<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Models\Image;
use Illuminate\Http\Request;
use Inertia\Inertia;

final class ImageController extends Controller
{
    use DeleteAction;

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Image $image)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Image $image)
    {
        return Inertia::render('Admin/Image/Update', compact('image'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Image $image)
    {
        $data = $request->validate(['chemin' => 'required|file']);
        if ($request->hasFile('chemin')) {
            $this->file_delete($image);
            $filename = $request->chemin->hashName();
            $chemin = $request->chemin->storeAs('product/image', $filename, 'public');
            $data['chemin'] = $chemin;
            $image->update($data);
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        $this->file_delete($image);

        return $this->supp($image);
    }
}
