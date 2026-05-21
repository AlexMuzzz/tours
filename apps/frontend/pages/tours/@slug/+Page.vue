<script setup lang="ts">
import { useData } from 'vike-vue/useData';
import type { Data } from './+data';
import TourDetailPage from '@/pages/public/TourDetailPage.vue';

const data = useData<Data>();
</script>

<template>
  <TourDetailPage :tour="data.tour" :error="data.error" />
</template>
