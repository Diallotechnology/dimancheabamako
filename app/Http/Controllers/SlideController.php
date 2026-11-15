<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\UpdateSlideRequest;
use App\Models\Slide;
use App\Service\ImageUploadService;
use Inertia\Inertia;
use Throwable;

final class SlideController extends Controller
{
    use DeleteAction;

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Slide $slide)
    {
        return Inertia::render('Admin/Slide/Update', compact('slide'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSlideRequest $request, Slide $slide, ImageUploadService $uploader)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $old = $slide->getRawOriginal('image');
            // Nouveau fichier d'abord : un échec ne doit jamais faire perdre l'ancien.
            $data['image'] = $uploader->store($request->file('image'), 'slide/image');

            try {
                $slide->update($data);
            } catch (Throwable $e) {
                $this->deleteStored($data['image']);

                throw $e;
            }

            $this->deleteStored($old);
        } else {
            $slide->update(data_forget($data, 'image'));
        }

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slide $slide)
    {
        $this->deleteStored($slide->getRawOriginal('image'));

        return $this->supp($slide);
    }
}
