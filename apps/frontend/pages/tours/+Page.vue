<script setup lang="ts">
import { useData } from 'vike-vue/useData';
import type { Data } from './+data';
import ToursCatalogPage from '@/pages/public/ToursCatalogPage.vue';

const data = useData<Data>();
</script>

<template>
  <ToursCatalogPage
    :initial-tours="data.initialTours"
    :initial-error="data.initialError"
    :initial-filters="data.initialFilters"
  />
</template>
