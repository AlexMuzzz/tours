<script setup lang="ts">
import { usePageContext } from 'vike-vue/usePageContext';
import AdminTourEditPage from '@/pages/admin/AdminTourEditPage.vue';

const pageContext = usePageContext();
</script>

<template>
  <AdminTourEditPage :id="String(pageContext.routeParams.id)" />
</template>
