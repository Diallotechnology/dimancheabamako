<script setup lang="ts">
import AdminLayoutV2 from '@/Layouts/AdminLayoutV2.vue';

// Pages non migrées : elles gardent cet import et reçoivent le nouveau layout. Supprimé au nettoyage final.
</script>

<template>
    <AdminLayoutV2>
        <slot />
    </AdminLayoutV2>
</template>
