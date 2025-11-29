<div x-data="{ hide: false }" x-init="
    $watch('$wire.isDeleting', value => {
        if (value) {
            hide = true;
        }
    });
">
    <template x-if="!hide">
        <button wire:click="deleteProduct" class="btn-small btn-danger text-white" wire:loading.attr="disabled"
            wire:target="deleteProduct" wire:loading.class="opacity-50">
            <i class="fi-rs-trash"></i>
        </button>
    </template>

    <span wire:loading wire:target="deleteProduct" class="text-danger" style="font-size:14px;">
        <i class="spinner-border spinner-border-sm"></i>
    </span>
</div>