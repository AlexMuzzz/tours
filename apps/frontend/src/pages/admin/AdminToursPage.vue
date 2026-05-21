<script setup lang="ts">
import { onMounted, ref } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Tag from 'primevue/tag';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import { navigate } from 'vike/client/router';
import AdminLayout from '@/layouts/AdminLayout.vue';
import StateBlock from '@/components/StateBlock.vue';
import { adminTourService } from '@/services/adminTourService';
import { useAuthStore } from '@/stores/auth';
import { formatCategory, formatDateTime, formatDuration } from '@/utils/formatters';
import { getErrorMessage } from '@/utils/errors';
import type { PaginatedResponse, Tour } from '@/types/api';

const authStore = useAuthStore();
const toast = useToast();
const confirm = useConfirm();

const loading = ref(true);
const error = ref('');
const response = ref<PaginatedResponse<Tour> | null>(null);
const statusUpdatingId = ref<number | null>(null);

async function loadTours(page = 1) {
  loading.value = true;
  error.value = '';

  try {
    await authStore.loadFromStorage();
    response.value = await adminTourService.getTours({ page, per_page: 20 });
  } catch (requestError) {
    error.value = getErrorMessage(requestError, 'Не удалось загрузить список туров.');
  } finally {
    loading.value = false;
  }
}

function confirmDelete(tour: Tour) {
  confirm.require({
    header: 'Удалить тур?',
    message: `Тур "${tour.title}" будет удалён из каталога.`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Удалить',
    rejectLabel: 'Отмена',
    accept: async () => {
      try {
        await adminTourService.deleteTour(tour.id);
        toast.add({
          severity: 'success',
          summary: 'Тур удалён',
          detail: `"${tour.title}" удалён из каталога.`,
          life: 2500,
        });
        await loadTours(response.value?.meta.current_page ?? 1);
      } catch (requestError) {
        toast.add({
          severity: 'error',
          summary: 'Удаление не удалось',
          detail: getErrorMessage(requestError, 'Не удалось удалить тур.'),
          life: 3500,
        });
      }
    },
  });
}

async function toggleActiveState(tour: Tour) {
  statusUpdatingId.value = tour.id;

  try {
    await adminTourService.updateTour(tour.id, {
      is_active: !tour.is_active,
    });

    toast.add({
      severity: 'success',
      summary: tour.is_active ? 'Тур скрыт' : 'Тур опубликован',
      detail: `"${tour.title}" ${tour.is_active ? 'убран из публичного каталога' : 'стал доступен в публичном каталоге'}.`,
      life: 2500,
    });

    await loadTours(response.value?.meta.current_page ?? 1);
  } catch (requestError) {
    toast.add({
      severity: 'error',
      summary: 'Не удалось изменить статус',
      detail: getErrorMessage(requestError, 'Попробуйте снова через пару секунд.'),
      life: 3500,
    });
  } finally {
    statusUpdatingId.value = null;
  }
}

onMounted(() => {
  void loadTours();
});
</script>

<template>
  <AdminLayout
    title="Туры"
    subtitle="Список всех туров, включая скрытые. Отсюда удобно перейти в редактирование, быстро удалить запись или изменить статус публикации."
  >
    <Card class="glass-panel rounded-[2rem] border-0">
      <template #content>
        <div class="mb-5 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          <div class="text-sm text-[var(--travel-muted)]">
            Управляйте публикацией, категориями и длительностью туров.
          </div>
          <a href="/admin/tours/create" class="travel-link travel-link-primary text-sm">
            Создать тур
          </a>
        </div>

        <StateBlock
          v-if="loading"
          title="Загружаем туры"
          description="Подтягиваем данные из admin API."
          loading
        />

        <StateBlock
          v-else-if="error"
          title="Список туров недоступен"
          :description="error"
        />

        <div v-else-if="response" class="space-y-5">
          <DataTable
            :value="response.data"
            data-key="id"
            striped-rows
            responsive-layout="scroll"
            class="overflow-hidden rounded-[1.5rem]"
          >
            <Column field="title" header="Title">
              <template #body="{ data }">
                <div>
                  <div class="font-semibold text-[var(--travel-ink)]">{{ data.title }}</div>
                  <div class="text-xs text-[var(--travel-muted)]">#{{ data.id }}</div>
                </div>
              </template>
            </Column>

            <Column header="Category">
              <template #body="{ data }">
                {{ formatCategory(data.category) }}
              </template>
            </Column>

            <Column header="Duration">
              <template #body="{ data }">
                {{ formatDuration(data.duration_days) }}
              </template>
            </Column>

            <Column header="Status">
              <template #body="{ data }">
                <div class="flex flex-col items-start gap-2">
                  <Tag :severity="data.is_active ? 'success' : 'warn'" :value="data.is_active ? 'Активен' : 'Скрыт'" rounded />
                  <Button
                    :label="data.is_active ? 'Скрыть' : 'Активировать'"
                    size="small"
                    severity="secondary"
                    outlined
                    :loading="statusUpdatingId === data.id"
                    @click="toggleActiveState(data)"
                  />
                </div>
              </template>
            </Column>

            <Column header="Created">
              <template #body="{ data }">
                {{ formatDateTime(data.created_at) }}
              </template>
            </Column>

            <Column header="Actions">
              <template #body="{ data }">
                <div class="flex gap-2">
                  <Button
                    label="Edit"
                    size="small"
                    icon="pi pi-pencil"
                    @click="navigate(`/admin/tours/${data.id}/edit`)"
                  />
                  <Button
                    label="Delete"
                    size="small"
                    severity="danger"
                    outlined
                    icon="pi pi-trash"
                    @click="confirmDelete(data)"
                  />
                </div>
              </template>
            </Column>
          </DataTable>

          <div class="flex flex-wrap items-center justify-between gap-4 rounded-[1.5rem] bg-white/70 px-4 py-3 text-sm">
            <div class="text-[var(--travel-muted)]">
              Страница {{ response.meta.current_page }} из {{ response.meta.last_page }}.
              Всего туров: {{ response.meta.total }}.
            </div>

            <div class="flex gap-2">
              <Button
                label="Назад"
                severity="secondary"
                outlined
                :disabled="!response.links.prev"
                @click="loadTours(response.meta.current_page - 1)"
              />
              <Button
                label="Вперёд"
                severity="secondary"
                outlined
                :disabled="!response.links.next"
                @click="loadTours(response.meta.current_page + 1)"
              />
            </div>
          </div>
        </div>
      </template>
    </Card>
  </AdminLayout>
</template>
