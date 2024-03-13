<?php

namespace App\Http\Controllers;

use App\Helper\DeleteAction;
use App\Http\Requests\StorePromotionRequest;
use App\Models\Product;
use App\Models\Promotion;
use Inertia\Inertia;

class PromotionController extends Controller
{
    use DeleteAction;

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $product = Product::doesntHave('promotions')->get()->map(function ($row) {
            return [
                'label' => 'Ref '.$row->reference.' '.$row->nom,
                'value' => "$row->id",
            ];
        });

        return Inertia::render('Admin/Promotion/Create', compact('product'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePromotionRequest $request)
    {
        $item = Promotion::create($request->validated());
        $item->products()->attach($request->product_id);

        return back();
    }

    /**
     * Display the specified resource.
     */
    public function show(Promotion $promotion)
    {
        $promotion->load('products');

        return Inertia::render('Admin/Promotion/Show', compact('promotion'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Promotion $promotion)
    {
        $promotion->load('products');
        $product = Product::with('promotions')->get()->map(function ($row) {
            return [
                'label' => 'Ref '.$row->reference.' '.$row->nom,
                'value' => "$row->id",
            ];
        });

        return Inertia::render('Admin/Promotion/Update', compact('promotion', 'product'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePromotionRequest $request, Promotion $promotion)
    {
        $promotion->update($request->validated());
        $promotion->products()->sync($request->product_id);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Promotion $promotion)
    {
        return $this->supp($promotion);
    }
}
