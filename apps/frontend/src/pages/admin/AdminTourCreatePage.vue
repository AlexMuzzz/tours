<script setup lang="ts">
import { reactive, ref } from 'vue';
import { navigate } from 'vike/client/router';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { adminTourService } from '@/services/adminTourService';
import { getErrorMessage, getFieldError } from '@/utils/errors';
import { categoryOptions } from '@/utils/tourMeta';
import type { TourCategory, TourPayload } from '@/types/api';

const toast = useToast();

const form = reactive({
  title: '',
  short_description: '',
  description: '',
  duration_days: '5',
  category: 'nature' as TourCategory,
  is_active: true,
  main_image: '',
});

const submitting = ref(false);
const formErrors = ref<Record<string, string[]>>({});
const pageError = ref('');

function buildPayload(): TourPayload {
  return {
    title: form.title,
    short_description: form.short_description,
    description: form.description,
    duration_days: Number(form.duration_days),
    category: form.category,
    is_active: form.is_active,
    main_image: form.main_image,
  };
}

async function handleSubmit() {
  submitting.value = true;
  formErrors.value = {};
  pageError.value = '';

  try {
    const createdTour = await adminTourService.createTour(buildPayload());
    toast.add({
      severity: 'success',
      summary: 'Тур создан',
      detail: `"${createdTour.title}" готов к дальнейшему редактированию.`,
      life: 2500,
    });
    await navigate(`/admin/tours/${createdTour.id}/edit`);
  } catch (error) {
    const apiErrors = typeof error === 'object' && error && 'errors' in error
      ? ((error as { errors?: Record<string, string[]> }).errors ?? {})
      : {};

    formErrors.value = apiErrors;
    pageError.value = getErrorMessage(error, 'Не удалось создать тур.');
  } finally {
    submitting.value = false;
  }
}
</script>

<template>
  <AdminLayout
    title="Создание тура"
    subtitle="Быстрая форма для создания нового тура. После успешного сохранения можно сразу перейти к датам, изображениям и маршруту."
  >
    <Card class="glass-panel rounded-[2rem] border-0">
      <template #content>
        <form class="space-y-6" @submit.prevent="handleSubmit">
          <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
            <div class="space-y-5">
              <label class="block space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Title</span>
                <InputText v-model="form.title" fluid />
                <small v-if="getFieldError(formErrors, 'title')" class="text-red-600">
                  {{ getFieldError(formErrors, 'title') }}
                </small>
              </label>

              <label class="block space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Short description</span>
                <Textarea v-model="form.short_description" auto-resize rows="4" fluid />
                <small v-if="getFieldError(formErrors, 'short_description')" class="text-red-600">
                  {{ getFieldError(formErrors, 'short_description') }}
                </small>
              </label>

              <label class="block space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Description</span>
                <Textarea v-model="form.description" auto-resize rows="10" fluid />
                <small v-if="getFieldError(formErrors, 'description')" class="text-red-600">
                  {{ getFieldError(formErrors, 'description') }}
                </small>
              </label>
            </div>

            <div class="space-y-5">
              <label class="block space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Duration days</span>
                <InputText v-model="form.duration_days" type="number" min="1" fluid />
                <small v-if="getFieldError(formErrors, 'duration_days')" class="text-red-600">
                  {{ getFieldError(formErrors, 'duration_days') }}
                </small>
              </label>

              <label class="block space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Category</span>
                <Select v-model="form.category" :options="categoryOptions" option-label="label" option-value="value" fluid />
                <small v-if="getFieldError(formErrors, 'category')" class="text-red-600">
                  {{ getFieldError(formErrors, 'category') }}
                </small>
              </label>

              <label class="block space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Main image URL</span>
                <InputText v-model="form.main_image" type="url" fluid />
                <small v-if="getFieldError(formErrors, 'main_image')" class="text-red-600">
                  {{ getFieldError(formErrors, 'main_image') }}
                </small>
              </label>

              <label class="flex items-center gap-3 rounded-[1.5rem] bg-white/70 px-4 py-4">
                <Checkbox v-model="form.is_active" binary input-id="is_active" />
                <span class="text-sm font-medium text-[var(--travel-ink)]">Публиковать тур сразу</span>
              </label>

              <div class="overflow-hidden rounded-[1.8rem] border border-[var(--travel-line)] bg-white/70">
                <img
                  v-if="form.main_image"
                  :src="form.main_image"
                  alt="Preview"
                  class="h-52 w-full object-cover"
                />
                <div
                  v-else
                  class="flex h-52 items-center justify-center text-sm text-[var(--travel-muted)]"
                >
                  Превью изображения появится после вставки URL
                </div>
              </div>
            </div>
          </div>

          <p v-if="pageError" class="rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-600">
            {{ pageError }}
          </p>

          <div class="flex flex-col gap-3 sm:flex-row">
            <Button type="submit" label="Создать тур" icon="pi pi-plus" :loading="submitting" />
            <a href="/admin/tours" class="travel-link travel-link-secondary justify-center">
              Отмена
            </a>
          </div>
        </form>
      </template>
    </Card>
  </AdminLayout>
</template>
