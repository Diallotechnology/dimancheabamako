<div>
    <div wire:ignore.self class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true"
        data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content apple-modal p-4">

                <button type="button" class="btn-close apple-close" data-bs-dismiss="modal"></button>

                <div class="modal-body text-center">
                    <div wire:loading class="py-5">
                        <div class="spinner-border text-dark" style="width: 3rem; height: 3rem;"></div>
                    </div>
                    <div wire:loading.remove>
                        <!-- PRODUCT INFO BLOCK -->
                        <div class="d-flex align-items-center justify-content-center mb-4">
                            <!-- Image -->
                            <img src="{{ $addedItem['attributes']['cover'] ?? '' }}" alt="Image produit"
                                class="apple-product-img me-3">

                            <div class="text-start">

                                <!-- Name -->
                                <div class="fw-semibold apple-text-strong">
                                    {{ $addedItem['name'] ?? '' }}
                                </div>

                                <!-- Pricing -->
                                @php
                                $price = $addedItem['price'] ?? 0;
                                $reduction = $addedItem['attributes']['reduction'] ?? 0;
                                $finalPrice = $price - ($price * $reduction / 100);
                                $total = $finalPrice * ($addedItem['quantity'] ?? 1);
                                @endphp

                                <div class="apple-price-block">
                                    @if($reduction > 0)
                                    <span class="old-price">
                                        <x-price-format :value="$price" />
                                    </span>
                                    <span class="new-price">
                                        <x-price-format :value="$finalPrice" />
                                    </span>
                                    <span class="discount-tag">-{{ $reduction }}%</span>
                                    @else
                                    <span class="new-price">
                                        <x-price-format :value="$finalPrice" />
                                    </span>
                                    @endif
                                </div>

                                <!-- Weight -->
                                <div class="">
                                    Poids : {{ $addedItem['poids'] ?? '' }} kg
                                </div>

                            </div>
                        </div>

                        <!-- TOTAL -->
                        <div class="apple-total-value mb-3">
                            Total :
                            <x-price-format :value="$total" />
                        </div>
                    </div>
                    @if (session()->has('warning'))
                    <div class="apple-warning mb-3">
                        {{ session('warning') }}
                    </div>
                    @endif

                    <!-- Quantity controls -->
                    <div class="d-flex justify-content-center align-items-center mb-4 apple-qty-box">


                        <button class="apple-btn-qty" wire:click="decreaseQuick('{{ $addedItem['id'] ?? '' }}')">
                            –
                        </button>

                        <span class="mx-4 fw-semibold apple-qty-value">
                            {{ $addedItem['quantity'] ?? 1 }}
                        </span>

                        <button {{-- @disabled($addedItem['stock']>= $addedItem['quantity']) --}}
                            class="apple-btn-qty"
                            wire:click="increaseQuick('{{ $addedItem['id'] ?? '' }}')">
                            +
                        </button>

                    </div>

                    <!-- Actions -->
                    <div class="mt-4">
                        <a href="{{ route('panier') }}" class="apple-btn-primary w-100 mb-3">
                            Finaliser ma commande
                        </a>

                        <button class="apple-btn-secondary w-100" data-bs-dismiss="modal">
                            Continuer vos achats
                        </button>
                    </div>

                </div>

            </div>
        </div>
    </div>
    <style>
        .apple-warning {
            background: #fff3cd;
            color: #664d03;
            padding: 10px 14px;
            border-radius: 10px;
            font-size: 0.9rem;
            border: 1px solid #ffeeba;
            text-align: center;
        }

        .apple-product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 12px;
        }

        .apple-price-block {
            margin-top: 4px;
        }

        .old-price {
            text-decoration: line-through;
            color: #999;
            margin-right: 6px;
        }

        .new-price {
            font-weight: 600;
            color: #111;
        }

        .discount-tag {
            background: #d79f01;
            color: white;
            padding: 2px 6px;
            border-radius: 6px;
            font-size: 0.75rem;
        }

        .apple-total-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #111;
        }

        /* Apple Modal */
        .apple-modal {
            border-radius: 18px;
            border: none;
            background: #ffffff;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        }

        /* Close button - Apple style */
        .apple-close {
            filter: grayscale(100%) brightness(50%);
            opacity: 0.5;
        }

        .apple-close:hover {
            opacity: 1;
        }

        /* Text color Apple */
        .apple-text-strong {
            font-size: 1.2rem;
            color: #111111;
        }

        /* Quantity controls */
        .apple-qty-box {
            background: #f5f5f7;
            padding: 12px 18px;
            border-radius: 14px;
        }

        .apple-btn-qty {
            background: transparent;
            border: none;
            font-size: 1.7rem;
            padding: 0 10px;
            font-weight: 300;
            color: #111;
            width: 36px;
            height: 36px;
            line-height: 0;
        }

        .apple-btn-qty:hover {
            background: #e5e5e7;
            border-radius: 8px;
        }

        .apple-qty-value {
            font-size: 1.3rem;
            color: #111;
        }

        /* Apple buttons */
        .apple-btn-primary {
            display: block;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            background: #d79f01;
            color: #fff;
            font-weight: 500;
            text-decoration: none;
        }

        .apple-btn-primary:hover {
            background: #d79f01;
        }

        .apple-btn-secondary {
            display: block;
            text-align: center;
            padding: 12px;
            border-radius: 12px;
            background: #f5f5f7;
            color: #111;
            border: none;
            font-weight: 500;
        }

        .apple-btn-secondary:hover {
            background: #e5e5e7;
        }
    </style>
</div>
@script
<script>
    window.showQuick = () => {
        const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();
    }
</script>

@endscript