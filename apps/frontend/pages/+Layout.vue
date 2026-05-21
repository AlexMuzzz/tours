<script setup lang="ts">
import Toast from 'primevue/toast';
import ConfirmDialog from 'primevue/confirmdialog';
</script>

<template>
  <slot />
  <Toast position="top-right" />
  <ConfirmDialog />
</template>
