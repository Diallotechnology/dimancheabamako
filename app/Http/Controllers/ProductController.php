<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

final class ProductController extends Controller
{
    use DeleteAction;

    public function create()
    {
        $category = Category::select('id', 'nom')->get()->map(function ($row) {
            return [
                'label' => "$row->nom",
                'value' => "$row->id",
            ];
        });

        return Inertia::render('Admin/Product/Create', compact('category'));
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
                'label' => "$row->nom",
                'value' => "$row->id",
            ];
        });
        $product->load('images');

        return Inertia::render('Admin/Product/Update', compact('product', 'category'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function favoris_update(int $data, int $product_id): Response
    {
        Product::findOrFail($product_id)->update(['favoris' => $data]);

        return response()->noContent();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        unset($data['video'], $data['cover'], $data['image']);

        $stockInitial = (int) $data['stock_initial'];
        unset($data['stock_initial']);

        if ($request->hasFile('cover')) {
            $this->deleteStored($product->getRawOriginal('cover'));

            $filename = $request->cover->hashName();
            $path = $request->cover->storeAs('product/cover', $filename, 'public');

            $data['cover'] = $path;
        }

        if ($request->hasFile('video')) {
            $this->deleteStored($product->getRawOriginal('video'));

            $filename = $request->video->hashName();
            $path = $request->video->storeAs('product/video', $filename, 'public');

            $data['video'] = $path;
        }

        if ($request->hasFile('image')) {
            $this->file_uplode($request, $product);
        }

        // Le stock saisi est une valeur absolue vue à l'ouverture du formulaire : on n'applique que
        // l'écart sur le stock réel (verrouillé), pour ne pas écraser les réservations concurrentes.
        DB::transaction(function () use ($product, $data, $stockInitial): void {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $newStock = $locked->stock + ((int) $data['stock'] - $stockInitial);

            if ($newStock < 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Le stock réel ('.$locked->stock.') est inférieur à la réduction demandée : des commandes ont réservé du stock entre-temps.',
                ]);
            }

            $data['stock'] = $newStock;
            $locked->update($data);
        });

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->deleteStored($product->getRawOriginal('cover'));
        $this->deleteStored($product->getRawOriginal('video'));
        $product->images->each(fn ($image) => $this->deleteStored($image->getRawOriginal('chemin')));

        return $this->supp($product);
    }
}
