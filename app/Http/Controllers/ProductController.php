<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Inertia\Inertia;

class ProductController extends Controller
{
    use DeleteAction;

    /**
     * Show the form for creating a new resource.
     */
    public function favoris(Product $product, $data)
    {
        return $product->update(['favoris' => $data]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $filename = $request->cover->hashName();
            $chemin = $request->cover->storeAs('product/cover', $filename, 'public');
            $data['cover'] = $chemin;
        }
        if ($request->hasFile('video')) {
            $filename = $request->video->hashName();
            $chemin = $request->video->storeAs('product/video', $filename, 'public');
            $data['video'] = $chemin;
        }

        $item = Product::create(data_forget($data, 'image'));
        if ($request->hasFile('image')) {
            $this->file_uplode($request, $item);
        }

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        $product->load('images', 'orders');

        return Inertia::render('Admin/Product/Show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        $category = Category::all()->map(function ($row) {
            return [
                'label' => "$row->nom", 'value' => "$row->id",
            ];
        });

        return Inertia::render('Admin/Product/Update', compact('product', 'category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        if ($request->hasFile('cover')) {
            $this->file_delete($product);
            $filename = $request->cover->hashName();
            $chemin = $request->cover->storeAs('product/cover', $filename, 'public');
            $data['cover'] = $chemin;
        }
        if ($request->hasFile('video')) {
            $this->file_delete($product);
            $filename = $request->video->hashName();
            $chemin = $request->video->storeAs('product/video', $filename, 'public');
            $data['video'] = $chemin;
        }
        if ($request->hasFile('image')) {
            $this->file_uplode($request, $product);
        }
        $product->update(data_forget($data, 'image'));

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->file_delete($product);

        return $this->supp($product);
    }
}
