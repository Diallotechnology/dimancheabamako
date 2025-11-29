<div class="d-flex align-items-center gap-2">
    <button wire:click="decrement" class="btn btn-light qty-btn" wire:loading.attr="disabled"
        wire:target="increment,decrement,applyQuantity">
        −
    </button>

    <input type="number" class="form-control text-center qty-input" min="1" max="{{ $stock }}"
        wire:model.defer="quantity" wire:change="applyQuantity" wire:loading.class="opacity-50" style="max-width: 70px">

    <button wire:click="increment" class="btn btn-light qty-btn" wire:loading.attr="disabled"
        wire:target="increment,decrement,applyQuantity">
        +
    </button>

    <div wire:loading wire:target="increment,decrement,applyQuantity">
        <span class="spinner-border spinner-border-sm text-primary"></span>
    </div>
    <style>
        .qty-btn {
            width: 35px;
            height: 35px;
            font-size: 18px;
            padding: 0;
            border-radius: 6px;
            border: 1px solid #ddd;
            transition: 0.15s;
        }

        .qty-btn:hover:not(:disabled) {
            background: #f5f5f5;
        }

        .qty-input {
            font-weight: 600;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ddd;
        }

        .qty-input:focus {
            border-color: #007bff;
            box-shadow: none;
        }
    </style>
</div>