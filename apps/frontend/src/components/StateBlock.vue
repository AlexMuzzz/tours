<script setup lang="ts">
import Card from 'primevue/card';
import ProgressSpinner from 'primevue/progressspinner';

defineProps<{
  title: string;
  description: string;
  loading?: boolean;
}>();
</script>

<template>
  <Card class="glass-panel overflow-hidden rounded-[2rem] border-0">
    <template #content>
      <div class="empty-illustration flex flex-col items-center gap-4 rounded-[1.5rem] px-6 py-12 text-center">
        <ProgressSpinner
          v-if="loading"
          style="width: 2.5rem; height: 2.5rem"
          strokeWidth="4"
          fill="transparent"
        />
        <div v-else class="travel-dot" />
        <div class="space-y-2">
          <h3 class="text-2xl font-semibold text-[var(--travel-ink)]">
            {{ title }}
          </h3>
          <p class="mx-auto max-w-2xl text-sm leading-6 text-[var(--travel-muted)]">
            {{ description }}
          </p>
        </div>
      </div>
    </template>
  </Card>
</template>
