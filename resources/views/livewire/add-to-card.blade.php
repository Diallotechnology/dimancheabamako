<div>
    <button wire:click='add()' class="button button-add-to-cart">
        Acheté
    </button>
</div>
@script
<script>
    $wire.on('productAdded', () => {
        var modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
        modal.show();
    });
</script>
@endscript