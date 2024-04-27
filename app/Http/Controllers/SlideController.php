<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\UpdateSlideRequest;
use App\Models\Slide;
use Inertia\Inertia;

class SlideController extends Controller
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
    public function update(UpdateSlideRequest $request, Slide $slide)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $this->file_delete($slide);
            $filename = $request->image->hashName();
            $chemin = $request->image->storeAs('slide/image', $filename, 'public');
            $data['image'] = $chemin;
        }
        $slide->update($data);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Slide $slide)
    {
        return $this->supp($slide);
        $this->file_delete($slide);
    }
}
