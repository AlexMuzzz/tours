<script setup lang="ts">
import Tag from 'primevue/tag';
import type { TourCategory } from '@/types/api';
import { formatCategory } from '@/utils/formatters';

defineProps<{
  category: TourCategory;
}>();
</script>

<template>
  <Tag
    :value="formatCategory(category)"
    severity="secondary"
    rounded
    class="border border-white/60 bg-white/80 text-xs font-semibold uppercase tracking-[0.18em] text-[var(--travel-ocean)]"
  />
</template>
