<div>
    <!-- <button
                                                class="btn btn-small"
                                                @click.prevent="
                                                    decrement(item.id)
                                                "
                                            >
                                                <i class="fi-rs-minus"></i>
                                            </button> -->

    <input type="number" wire:model.live="quantity" value="1" max="{{ $stock }}" min="1" autocomplete="off"
        class="qty-val form-control" style="max-width: 80px" />
    <!-- <button
                                                class="btn btn-small"
                                                @click.prevent="
                                                    increment(item.id)
                                                "
                                            >
                                                <i class="fi-rs-plus"></i>
                                            </button> -->
</div>