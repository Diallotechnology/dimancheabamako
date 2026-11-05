<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Models\Image;
use App\Service\ImageUploadService;
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
    public function update(Request $request, Image $image, ImageUploadService $uploader)
    {
        $data = $request->validate(['chemin' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120']]);
        if ($request->hasFile('chemin')) {
            $this->deleteStored($image->getRawOriginal('chemin'));
            $data['chemin'] = $uploader->store($request->file('chemin'), 'product/image');
            $image->update($data);
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        $this->deleteStored($image->getRawOriginal('chemin'));

        return $this->supp($image);
    }
}
