<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Checkbox from 'primevue/checkbox';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Select from 'primevue/select';
import Tag from 'primevue/tag';
import Textarea from 'primevue/textarea';
import { useToast } from 'primevue/usetoast';
import { useConfirm } from 'primevue/useconfirm';
import AdminLayout from '@/layouts/AdminLayout.vue';
import StateBlock from '@/components/StateBlock.vue';
import { adminTourService } from '@/services/adminTourService';
import { categoryOptions, currencyOptions } from '@/utils/tourMeta';
import { formatCategory, formatDateRange, formatPrice } from '@/utils/formatters';
import { getErrorMessage, getFieldError } from '@/utils/errors';
import { resolveMediaUrl } from '@/utils/media';
import type {
  CurrencyCode,
  Tour,
  TourCategory,
  TourDate,
  TourPayload,
  TourRoutePoint,
} from '@/types/api';

const props = defineProps<{
  id: string;
}>();

const toast = useToast();
const confirm = useConfirm();

const loading = ref(true);
const error = ref('');
const tour = ref<Tour | null>(null);

const savingMain = ref(false);

const imageSubmitting = ref(false);
const dateSubmitting = ref(false);
const routeSubmitting = ref(false);
const dateDialogVisible = ref(false);
const routeDialogVisible = ref(false);

const formErrors = ref<Record<string, string[]>>({});
const imageErrors = ref<Record<string, string[]>>({});
const dateErrors = ref<Record<string, string[]>>({});
const routeErrors = ref<Record<string, string[]>>({});
const mainImageInput = ref<HTMLInputElement | null>(null);
const mainImageFile = ref<File | null>(null);
const mainImagePreviewUrl = ref('');
const mainImagePreviewFailed = ref(false);
const removeMainImage = ref(false);
const galleryImageInput = ref<HTMLInputElement | null>(null);
const galleryImageFile = ref<File | null>(null);
const galleryImagePreviewUrl = ref('');
const galleryImagePreviewFailed = ref(false);

const mainForm = reactive({
  title: '',
  short_description: '',
  description: '',
  duration_days: '1',
  category: 'nature' as TourCategory,
  is_active: true,
  main_image: '',
});

const imageForm = reactive({
  image_url: '',
  alt_text: '',
  sort_order: '1',
});

const dateCreateForm = reactive({
  start_date: '',
  end_date: '',
  price: '',
  currency: 'RUB' as CurrencyCode,
  available_seats: '10',
});

const dateEditForm = reactive({
  id: 0,
  start_date: '',
  end_date: '',
  price: '',
  currency: 'RUB' as CurrencyCode,
  available_seats: '10',
});

const routeCreateForm = reactive({
  title: '',
  description: '',
  latitude: '',
  longitude: '',
  sort_order: '1',
});

const routeEditForm = reactive({
  id: 0,
  title: '',
  description: '',
  latitude: '',
  longitude: '',
  sort_order: '1',
});

const images = computed(() => tour.value?.images ?? []);
const dates = computed(() => tour.value?.dates ?? []);
const routePoints = computed(() =>
  [...(tour.value?.route_points ?? [])].sort((left, right) => left.sort_order - right.sort_order),
);
const mainImagePreviewSource = computed(() => {
  if (mainImagePreviewUrl.value) {
    return mainImagePreviewUrl.value;
  }

  if (removeMainImage.value) {
    return '';
  }

  return resolveMediaUrl(mainForm.main_image);
});
const galleryPreviewSource = computed(() => galleryImagePreviewUrl.value || resolveMediaUrl(imageForm.image_url));

function replaceObjectUrl(target: typeof mainImagePreviewUrl, file: File | null) {
  if (target.value) {
    URL.revokeObjectURL(target.value);
    target.value = '';
  }

  if (file) {
    target.value = URL.createObjectURL(file);
  }
}

function setMainImageFile(file: File | null) {
  mainImageFile.value = file;
  replaceObjectUrl(mainImagePreviewUrl, file);

  if (file) {
    removeMainImage.value = false;
  }
}

function setGalleryImageFile(file: File | null) {
  galleryImageFile.value = file;
  replaceObjectUrl(galleryImagePreviewUrl, file);
}

function handleMainImageFileChange(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0] ?? null;

  setMainImageFile(file);
}

function handleGalleryImageFileChange(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0] ?? null;

  setGalleryImageFile(file);
}

function handleMainImageUrlUpdate(value: string | number | undefined) {
  mainForm.main_image = String(value ?? '');

  if (mainForm.main_image !== '') {
    removeMainImage.value = false;
  }
}

function markMainImageForRemoval() {
  removeMainImage.value = true;
  mainForm.main_image = '';
  setMainImageFile(null);

  if (mainImageInput.value) {
    mainImageInput.value.value = '';
  }
}

function clearSelectedMainImageFile() {
  setMainImageFile(null);

  if (mainImageInput.value) {
    mainImageInput.value.value = '';
  }
}

function restoreMainImage() {
  removeMainImage.value = false;
}

function clearGalleryImageSelection() {
  imageForm.image_url = '';
  setGalleryImageFile(null);

  if (galleryImageInput.value) {
    galleryImageInput.value.value = '';
  }
}

function syncMainForm(source: Tour) {
  mainForm.title = source.title;
  mainForm.short_description = source.short_description ?? '';
  mainForm.description = source.description ?? '';
  mainForm.duration_days = String(source.duration_days);
  mainForm.category = source.category;
  mainForm.is_active = source.is_active;
  mainForm.main_image = source.main_image ?? '';
  removeMainImage.value = false;
  setMainImageFile(null);

  if (mainImageInput.value) {
    mainImageInput.value.value = '';
  }
}

function resetImageForm() {
  imageForm.image_url = '';
  imageForm.alt_text = '';
  imageForm.sort_order = String((images.value.at(-1)?.sort_order ?? 0) + 1);
  setGalleryImageFile(null);

  if (galleryImageInput.value) {
    galleryImageInput.value.value = '';
  }
}

function resetDateCreateForm() {
  dateCreateForm.start_date = '';
  dateCreateForm.end_date = '';
  dateCreateForm.price = '';
  dateCreateForm.currency = 'RUB';
  dateCreateForm.available_seats = '10';
}

function resetRouteCreateForm() {
  routeCreateForm.title = '';
  routeCreateForm.description = '';
  routeCreateForm.latitude = '';
  routeCreateForm.longitude = '';
  routeCreateForm.sort_order = String((routePoints.value.at(-1)?.sort_order ?? 0) + 1);
}

async function loadTour(showSpinner = true) {
  if (showSpinner) {
    loading.value = true;
  }

  error.value = '';

  try {
    const response = await adminTourService.getTour(props.id);
    tour.value = response;
    syncMainForm(response);
    resetImageForm();
    resetRouteCreateForm();
  } catch (requestError) {
    error.value = getErrorMessage(requestError, 'Не удалось загрузить тур.');
  } finally {
    if (showSpinner) {
      loading.value = false;
    }
  }
}

function buildMainPayload(): TourPayload {
  return {
    title: mainForm.title,
    short_description: mainForm.short_description,
    description: mainForm.description,
    duration_days: Number(mainForm.duration_days),
    category: mainForm.category,
    is_active: mainForm.is_active,
    main_image: mainForm.main_image || null,
    main_image_file: mainImageFile.value,
    remove_main_image: removeMainImage.value,
  };
}

async function saveMainSection() {
  savingMain.value = true;
  formErrors.value = {};

  try {
    const updatedTour = await adminTourService.updateTour(props.id, buildMainPayload());
    tour.value = updatedTour;
    syncMainForm(updatedTour);
    toast.add({
      severity: 'success',
      summary: 'Основная информация сохранена',
      detail: 'Slug и embedding stub обновлены на backend.',
      life: 2500,
    });
  } catch (requestError) {
    formErrors.value = typeof requestError === 'object' && requestError && 'errors' in requestError
      ? ((requestError as { errors?: Record<string, string[]> }).errors ?? {})
      : {};
    toast.add({
      severity: 'error',
      summary: 'Сохранение не удалось',
      detail: getErrorMessage(requestError, 'Проверьте поля формы и попробуйте снова.'),
      life: 3500,
    });
  } finally {
    savingMain.value = false;
  }
}

async function addImage() {
  imageSubmitting.value = true;
  imageErrors.value = {};

  try {
    await adminTourService.addImage(props.id, {
      image_url: imageForm.image_url || null,
      image_file: galleryImageFile.value,
      alt_text: imageForm.alt_text,
      sort_order: Number(imageForm.sort_order || 0),
    });
    resetImageForm();
    await loadTour(false);
    toast.add({
      severity: 'success',
      summary: 'Изображение добавлено',
      detail: 'Изображение сохранено в галерее тура.',
      life: 2500,
    });
  } catch (requestError) {
    imageErrors.value = typeof requestError === 'object' && requestError && 'errors' in requestError
      ? ((requestError as { errors?: Record<string, string[]> }).errors ?? {})
      : {};
    toast.add({
      severity: 'error',
      summary: 'Не удалось добавить изображение',
      detail: getErrorMessage(requestError, 'Проверьте файл изображения или URL.'),
      life: 3500,
    });
  } finally {
    imageSubmitting.value = false;
  }
}

function confirmDeleteImage(imageId: number) {
  confirm.require({
    header: 'Удалить изображение?',
    message: 'Запись будет удалена из галереи тура.',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Удалить',
    rejectLabel: 'Отмена',
    accept: async () => {
      await adminTourService.deleteImage(imageId);
      await loadTour(false);
      toast.add({
        severity: 'success',
        summary: 'Изображение удалено',
        detail: 'Галерея обновлена.',
        life: 2500,
      });
    },
  });
}

async function addDate() {
  dateSubmitting.value = true;
  dateErrors.value = {};

  try {
    await adminTourService.addDate(props.id, {
      start_date: dateCreateForm.start_date,
      end_date: dateCreateForm.end_date,
      price: Number(dateCreateForm.price),
      currency: dateCreateForm.currency,
      available_seats: Number(dateCreateForm.available_seats),
    });
    resetDateCreateForm();
    await loadTour(false);
    toast.add({
      severity: 'success',
      summary: 'Дата добавлена',
      detail: 'Новый выезд появился в расписании тура.',
      life: 2500,
    });
  } catch (requestError) {
    dateErrors.value = typeof requestError === 'object' && requestError && 'errors' in requestError
      ? ((requestError as { errors?: Record<string, string[]> }).errors ?? {})
      : {};
    toast.add({
      severity: 'error',
      summary: 'Не удалось добавить дату',
      detail: getErrorMessage(requestError, 'Проверьте даты, цену и валюту.'),
      life: 3500,
    });
  } finally {
    dateSubmitting.value = false;
  }
}

function openDateDialog(date: TourDate) {
  dateEditForm.id = date.id;
  dateEditForm.start_date = date.start_date ?? '';
  dateEditForm.end_date = date.end_date ?? '';
  dateEditForm.price = String(date.price);
  dateEditForm.currency = date.currency;
  dateEditForm.available_seats = String(date.available_seats);
  dateDialogVisible.value = true;
}

async function saveDateEdit() {
  dateSubmitting.value = true;
  dateErrors.value = {};

  try {
    await adminTourService.updateDate(dateEditForm.id, {
      start_date: dateEditForm.start_date,
      end_date: dateEditForm.end_date,
      price: Number(dateEditForm.price),
      currency: dateEditForm.currency,
      available_seats: Number(dateEditForm.available_seats),
    });
    dateDialogVisible.value = false;
    await loadTour(false);
    toast.add({
      severity: 'success',
      summary: 'Дата обновлена',
      detail: 'Расписание тура синхронизировано.',
      life: 2500,
    });
  } catch (requestError) {
    dateErrors.value = typeof requestError === 'object' && requestError && 'errors' in requestError
      ? ((requestError as { errors?: Record<string, string[]> }).errors ?? {})
      : {};
  } finally {
    dateSubmitting.value = false;
  }
}

function confirmDeleteDate(dateId: number) {
  confirm.require({
    header: 'Удалить дату?',
    message: 'Этот вариант выезда будет удалён.',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Удалить',
    rejectLabel: 'Отмена',
    accept: async () => {
      await adminTourService.deleteDate(dateId);
      await loadTour(false);
      toast.add({
        severity: 'success',
        summary: 'Дата удалена',
        detail: 'Расписание обновлено.',
        life: 2500,
      });
    },
  });
}

async function addRoutePoint() {
  routeSubmitting.value = true;
  routeErrors.value = {};

  try {
    await adminTourService.addRoutePoint(props.id, {
      title: routeCreateForm.title,
      description: routeCreateForm.description,
      latitude: Number(routeCreateForm.latitude),
      longitude: Number(routeCreateForm.longitude),
      sort_order: Number(routeCreateForm.sort_order),
    });
    resetRouteCreateForm();
    await loadTour(false);
    toast.add({
      severity: 'success',
      summary: 'Точка маршрута добавлена',
      detail: 'Frontend-карта сможет отрисовать новый маршрут.',
      life: 2500,
    });
  } catch (requestError) {
    routeErrors.value = typeof requestError === 'object' && requestError && 'errors' in requestError
      ? ((requestError as { errors?: Record<string, string[]> }).errors ?? {})
      : {};
    toast.add({
      severity: 'error',
      summary: 'Не удалось добавить точку',
      detail: getErrorMessage(requestError, 'Проверьте координаты и sort_order.'),
      life: 3500,
    });
  } finally {
    routeSubmitting.value = false;
  }
}

function openRouteDialog(point: TourRoutePoint) {
  routeEditForm.id = point.id;
  routeEditForm.title = point.title;
  routeEditForm.description = point.description ?? '';
  routeEditForm.latitude = String(point.latitude);
  routeEditForm.longitude = String(point.longitude);
  routeEditForm.sort_order = String(point.sort_order);
  routeDialogVisible.value = true;
}

async function saveRouteEdit() {
  routeSubmitting.value = true;
  routeErrors.value = {};

  try {
    await adminTourService.updateRoutePoint(routeEditForm.id, {
      title: routeEditForm.title,
      description: routeEditForm.description,
      latitude: Number(routeEditForm.latitude),
      longitude: Number(routeEditForm.longitude),
      sort_order: Number(routeEditForm.sort_order),
    });
    routeDialogVisible.value = false;
    await loadTour(false);
    toast.add({
      severity: 'success',
      summary: 'Точка маршрута обновлена',
      detail: 'Секция itinerary обновлена.',
      life: 2500,
    });
  } catch (requestError) {
    routeErrors.value = typeof requestError === 'object' && requestError && 'errors' in requestError
      ? ((requestError as { errors?: Record<string, string[]> }).errors ?? {})
      : {};
  } finally {
    routeSubmitting.value = false;
  }
}

function confirmDeleteRoutePoint(routePointId: number) {
  confirm.require({
    header: 'Удалить точку маршрута?',
    message: 'Остановка исчезнет из itinerary и будущей карты.',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Удалить',
    rejectLabel: 'Отмена',
    accept: async () => {
      await adminTourService.deleteRoutePoint(routePointId);
      await loadTour(false);
      toast.add({
        severity: 'success',
        summary: 'Точка маршрута удалена',
        detail: 'Маршрут тура обновлён.',
        life: 2500,
      });
    },
  });
}

watch(mainImagePreviewSource, () => {
  mainImagePreviewFailed.value = false;
});

watch(galleryPreviewSource, () => {
  galleryImagePreviewFailed.value = false;
});

onMounted(() => {
  void loadTour();
});

onBeforeUnmount(() => {
  replaceObjectUrl(mainImagePreviewUrl, null);
  replaceObjectUrl(galleryImagePreviewUrl, null);
});
</script>

<template>
  <AdminLayout
    title="Редактирование тура"
    subtitle="Полная форма редактирования тура: основная информация, gallery URLs, даты и route points."
  >
    <StateBlock
      v-if="loading"
      title="Загружаем тур"
      description="Собираем основную информацию, галерею, даты и маршрут."
      loading
    />

    <StateBlock
      v-else-if="error || !tour"
      title="Тур не загрузился"
      :description="error || 'Не удалось найти тур по id.'"
    />

    <div v-else class="space-y-6">
      <Card class="glass-panel rounded-[2rem] border-0">
        <template #content>
          <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
            <div>
              <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Основная информация</div>
              <h2 class="mt-2 text-3xl font-semibold text-[var(--travel-ink)]">
                {{ tour.title }}
              </h2>
              <p class="mt-2 text-sm text-[var(--travel-muted)]">
                Slug: {{ tour.slug }} · Категория: {{ formatCategory(tour.category) }}
              </p>
            </div>
            <Tag :severity="tour.is_active ? 'success' : 'warn'" :value="tour.is_active ? 'Активен' : 'Скрыт'" rounded />
          </div>

          <form class="space-y-6" @submit.prevent="saveMainSection">
            <div class="grid gap-6 xl:grid-cols-[1fr_360px]">
              <div class="space-y-5">
                <label class="block space-y-2">
                  <span class="text-sm font-medium text-[var(--travel-ink)]">Название</span>
                  <InputText v-model="mainForm.title" fluid />
                  <small v-if="getFieldError(formErrors, 'title')" class="text-red-600">
                    {{ getFieldError(formErrors, 'title') }}
                  </small>
                </label>

                <label class="block space-y-2">
                  <span class="text-sm font-medium text-[var(--travel-ink)]">Краткое описание</span>
                  <Textarea v-model="mainForm.short_description" auto-resize rows="4" fluid />
                  <small v-if="getFieldError(formErrors, 'short_description')" class="text-red-600">
                    {{ getFieldError(formErrors, 'short_description') }}
                  </small>
                </label>

                <label class="block space-y-2">
                  <span class="text-sm font-medium text-[var(--travel-ink)]">Описание</span>
                  <Textarea v-model="mainForm.description" auto-resize rows="10" fluid />
                  <small v-if="getFieldError(formErrors, 'description')" class="text-red-600">
                    {{ getFieldError(formErrors, 'description') }}
                  </small>
                </label>
              </div>

              <div class="space-y-5">
                <label class="block space-y-2">
                  <span class="text-sm font-medium text-[var(--travel-ink)]">Длительность (дней)</span>
                  <InputText v-model="mainForm.duration_days" type="number" min="1" fluid />
                  <small v-if="getFieldError(formErrors, 'duration_days')" class="text-red-600">
                    {{ getFieldError(formErrors, 'duration_days') }}
                  </small>
                </label>

                <label class="block space-y-2">
                  <span class="text-sm font-medium text-[var(--travel-ink)]">Категория</span>
                  <Select v-model="mainForm.category" :options="categoryOptions" option-label="label" option-value="value" fluid />
                  <small v-if="getFieldError(formErrors, 'category')" class="text-red-600">
                    {{ getFieldError(formErrors, 'category') }}
                  </small>
                </label>

                <label class="block space-y-2">
                  <span class="text-sm font-medium text-[var(--travel-ink)]">Файл главного изображения</span>
                  <input
                    ref="mainImageInput"
                    class="block w-full rounded-[1rem] border border-[var(--travel-line)] bg-white px-4 py-3 text-sm text-[var(--travel-ink)]"
                    type="file"
                    accept="image/*"
                    @change="handleMainImageFileChange"
                  >
                  <small class="text-[var(--travel-muted)]">
                    Новый файл заменит текущее изображение после сохранения.
                  </small>
                  <small v-if="getFieldError(formErrors, 'main_image_file')" class="text-red-600">
                    {{ getFieldError(formErrors, 'main_image_file') }}
                  </small>
                </label>

                <label class="block space-y-2">
                  <span class="text-sm font-medium text-[var(--travel-ink)]">URL главного изображения</span>
                  <InputText
                    v-model="mainForm.main_image"
                    type="url"
                    fluid
                    @update:model-value="handleMainImageUrlUpdate"
                  />
                  <small v-if="getFieldError(formErrors, 'main_image')" class="text-red-600">
                    {{ getFieldError(formErrors, 'main_image') }}
                  </small>
                </label>

                <div class="flex flex-wrap gap-3">
                  <Button
                    type="button"
                    label="Убрать текущее изображение"
                    severity="secondary"
                    outlined
                    @click="markMainImageForRemoval"
                  />
                  <Button
                    v-if="mainImageFile"
                    type="button"
                    label="Сбросить выбранный файл"
                    severity="secondary"
                    text
                    @click="clearSelectedMainImageFile"
                  />
                  <Button
                    v-if="removeMainImage"
                    type="button"
                    label="Вернуть изображение"
                    severity="secondary"
                    text
                    @click="restoreMainImage"
                  />
                  <span v-if="removeMainImage" class="self-center text-sm text-[var(--travel-muted)]">
                    Изображение будет удалено после сохранения.
                  </span>
                </div>

                <label class="flex items-center gap-3 rounded-[1.5rem] bg-white/70 px-4 py-4">
                  <Checkbox v-model="mainForm.is_active" binary input-id="edit_is_active" />
                  <span class="text-sm font-medium text-[var(--travel-ink)]">Тур активен в публичном каталоге</span>
                </label>

                <div class="overflow-hidden rounded-[1.8rem] border border-[var(--travel-line)] bg-white/70">
                  <img
                    v-if="mainImagePreviewSource && !mainImagePreviewFailed"
                    data-testid="main-image-preview"
                    :src="mainImagePreviewSource"
                    alt="Превью главного изображения"
                    class="h-52 w-full object-cover"
                    @error="mainImagePreviewFailed = true"
                  />
                  <div
                    v-else-if="mainImagePreviewSource"
                    class="flex h-52 items-center justify-center px-5 text-center text-sm text-[var(--travel-muted)]"
                  >
                    Файл может быть сохранён, но сервер не отдал его для превью. Проверьте адрес или доступность файла.
                  </div>
                  <div
                    v-else
                    class="flex h-52 items-center justify-center text-sm text-[var(--travel-muted)]"
                  >
                    Превью main_image появится здесь
                  </div>
                </div>

                <div v-if="mainImagePreviewSource" class="flex flex-wrap gap-3">
                  <a
                    :href="mainImagePreviewSource"
                    target="_blank"
                    rel="noreferrer"
                    class="travel-link travel-link-secondary justify-center"
                  >
                    Открыть изображение
                  </a>
                </div>
              </div>
            </div>

            <div class="flex flex-wrap gap-3">
              <Button type="submit" label="Сохранить" icon="pi pi-save" :loading="savingMain" />
              <a href="/admin/tours" class="travel-link travel-link-secondary justify-center">
                Назад к списку
              </a>
            </div>
          </form>
        </template>
      </Card>

      <Card class="glass-panel rounded-[2rem] border-0">
        <template #content>
          <div class="space-y-5">
            <div>
              <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Изображения</div>
              <h2 class="mt-2 text-3xl font-semibold text-[var(--travel-ink)]">
                Галерея тура
              </h2>
            </div>

            <div v-if="images.length" class="travel-grid md:grid-cols-2 xl:grid-cols-3">
              <article
                v-for="image in images"
                :key="image.id"
                class="rounded-[1.5rem] border border-[var(--travel-line)] bg-white/75 p-4"
              >
                <img :src="resolveMediaUrl(image.image_url)" :alt="image.alt_text || tour.title" class="h-44 w-full rounded-[1.1rem] object-cover" />
                <div class="mt-4 space-y-1">
                  <div class="text-sm font-semibold text-[var(--travel-ink)]">{{ image.alt_text || 'Без alt text' }}</div>
                  <div class="text-xs text-[var(--travel-muted)]">Порядок: {{ image.sort_order }}</div>
                </div>
                <Button
                  label="Удалить"
                  severity="danger"
                  outlined
                  icon="pi pi-trash"
                  class="mt-4 w-full"
                  @click="confirmDeleteImage(image.id)"
                />
              </article>
            </div>

            <form class="grid gap-4 rounded-[1.8rem] bg-white/70 p-5 md:grid-cols-2 xl:grid-cols-[1fr_1.2fr_1fr_120px_auto]" @submit.prevent="addImage">
              <div class="space-y-2 md:col-span-2 xl:col-span-1">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Файл изображения</span>
                <input
                  ref="galleryImageInput"
                  class="block w-full rounded-[1rem] border border-[var(--travel-line)] bg-white px-4 py-3 text-sm text-[var(--travel-ink)]"
                  type="file"
                  accept="image/*"
                  @change="handleGalleryImageFileChange"
                >
                <small v-if="getFieldError(imageErrors, 'image_file')" class="text-red-600">
                  {{ getFieldError(imageErrors, 'image_file') }}
                </small>
              </div>
              <div class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">URL изображения</span>
                <InputText v-model="imageForm.image_url" type="url" fluid />
                <small v-if="getFieldError(imageErrors, 'image_url')" class="text-red-600">
                  {{ getFieldError(imageErrors, 'image_url') }}
                </small>
              </div>
              <div class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Альтернативный текст</span>
                <InputText v-model="imageForm.alt_text" fluid />
              </div>
              <div class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Порядок</span>
                <InputText v-model="imageForm.sort_order" type="number" min="0" fluid />
              </div>
              <div class="flex items-end">
                <Button type="submit" label="Добавить" icon="pi pi-plus" :loading="imageSubmitting" />
              </div>
              <div v-if="galleryPreviewSource" class="space-y-3 md:col-span-2 xl:col-span-5">
                <div class="overflow-hidden rounded-[1.5rem] border border-[var(--travel-line)] bg-white">
                  <img
                    v-if="!galleryImagePreviewFailed"
                    data-testid="gallery-image-preview"
                    :src="galleryPreviewSource"
                    alt="Превью изображения галереи"
                    class="h-48 w-full object-cover"
                    @error="galleryImagePreviewFailed = true"
                  />
                  <div
                    v-else
                    class="flex h-48 items-center justify-center px-5 text-center text-sm text-[var(--travel-muted)]"
                  >
                    Файл может быть сохранён, но сервер не отдал его для превью. Проверьте URL или доступность файла.
                  </div>
                </div>
                <div class="flex flex-wrap gap-3">
                  <a
                    :href="galleryPreviewSource"
                    target="_blank"
                    rel="noreferrer"
                    class="travel-link travel-link-secondary justify-center"
                  >
                    Открыть превью
                  </a>
                  <Button
                    type="button"
                    label="Очистить превью"
                    severity="secondary"
                    outlined
                    @click="clearGalleryImageSelection"
                  />
                </div>
              </div>
            </form>
          </div>
        </template>
      </Card>

      <Card class="glass-panel rounded-[2rem] border-0">
        <template #content>
          <div class="space-y-5">
            <div>
              <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Даты</div>
              <h2 class="mt-2 text-3xl font-semibold text-[var(--travel-ink)]">
                Даты и цены
              </h2>
            </div>

            <DataTable
              :value="dates"
              data-key="id"
              responsive-layout="scroll"
              striped-rows
              class="overflow-hidden rounded-[1.5rem]"
            >
              <Column header="Даты">
                <template #body="{ data }">
                  {{ formatDateRange(data.start_date, data.end_date) }}
                </template>
              </Column>
              <Column header="Цена">
                <template #body="{ data }">
                  {{ formatPrice(data.price, data.currency) }}
                </template>
              </Column>
              <Column field="available_seats" header="Места" />
              <Column header="Действия">
                <template #body="{ data }">
                  <div class="flex gap-2">
                    <Button label="Редактировать" size="small" icon="pi pi-pencil" @click="openDateDialog(data)" />
                    <Button label="Удалить" size="small" severity="danger" outlined icon="pi pi-trash" @click="confirmDeleteDate(data.id)" />
                  </div>
                </template>
              </Column>
            </DataTable>

            <form class="grid gap-4 rounded-[1.8rem] bg-white/70 p-5 md:grid-cols-2 xl:grid-cols-6" @submit.prevent="addDate">
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Начало</span>
                <InputText v-model="dateCreateForm.start_date" type="date" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Окончание</span>
                <InputText v-model="dateCreateForm.end_date" type="date" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Цена</span>
                <InputText v-model="dateCreateForm.price" type="number" min="0" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Валюта</span>
                <Select v-model="dateCreateForm.currency" :options="currencyOptions" option-label="label" option-value="value" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Места</span>
                <InputText v-model="dateCreateForm.available_seats" type="number" min="0" fluid />
              </label>
              <div class="flex items-end">
                <Button type="submit" label="Добавить" icon="pi pi-plus" :loading="dateSubmitting" />
              </div>
            </form>

            <small v-if="Object.keys(dateErrors).length" class="text-red-600">
              {{ getFieldError(dateErrors, 'start_date') || getFieldError(dateErrors, 'end_date') || getFieldError(dateErrors, 'price') || getFieldError(dateErrors, 'currency') || getFieldError(dateErrors, 'available_seats') }}
            </small>
          </div>
        </template>
      </Card>

      <Card class="glass-panel rounded-[2rem] border-0">
        <template #content>
          <div class="space-y-5">
            <div>
              <div class="text-xs uppercase tracking-[0.24em] text-[var(--travel-muted)]">Точки маршрута</div>
              <h2 class="mt-2 text-3xl font-semibold text-[var(--travel-ink)]">
                Маршрут тура
              </h2>
            </div>

            <DataTable
              :value="routePoints"
              data-key="id"
              responsive-layout="scroll"
              striped-rows
              class="overflow-hidden rounded-[1.5rem]"
            >
              <Column field="sort_order" header="#" />
              <Column field="title" header="Название" />
              <Column header="Координаты">
                <template #body="{ data }">
                  {{ data.latitude }}, {{ data.longitude }}
                </template>
              </Column>
              <Column header="Действия">
                <template #body="{ data }">
                  <div class="flex gap-2">
                    <Button label="Редактировать" size="small" icon="pi pi-pencil" @click="openRouteDialog(data)" />
                    <Button label="Удалить" size="small" severity="danger" outlined icon="pi pi-trash" @click="confirmDeleteRoutePoint(data.id)" />
                  </div>
                </template>
              </Column>
            </DataTable>

            <form class="grid gap-4 rounded-[1.8rem] bg-white/70 p-5 xl:grid-cols-[1.1fr_1.2fr_150px_150px_120px_auto]" @submit.prevent="addRoutePoint">
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Название</span>
                <InputText v-model="routeCreateForm.title" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Описание</span>
                <InputText v-model="routeCreateForm.description" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Широта</span>
                <InputText v-model="routeCreateForm.latitude" type="number" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Долгота</span>
                <InputText v-model="routeCreateForm.longitude" type="number" fluid />
              </label>
              <label class="space-y-2">
                <span class="text-sm font-medium text-[var(--travel-ink)]">Порядок</span>
                <InputText v-model="routeCreateForm.sort_order" type="number" min="0" fluid />
              </label>
              <div class="flex items-end">
                <Button type="submit" label="Добавить" icon="pi pi-plus" :loading="routeSubmitting" />
              </div>
            </form>

            <small v-if="Object.keys(routeErrors).length" class="text-red-600">
              {{ getFieldError(routeErrors, 'title') || getFieldError(routeErrors, 'latitude') || getFieldError(routeErrors, 'longitude') || getFieldError(routeErrors, 'sort_order') }}
            </small>
          </div>
        </template>
      </Card>
    </div>

    <Dialog v-model:visible="dateDialogVisible" modal header="Редактировать дату" :style="{ width: '42rem' }">
      <div class="grid gap-4 md:grid-cols-2">
        <label class="space-y-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Начало</span>
          <InputText v-model="dateEditForm.start_date" type="date" fluid />
        </label>
        <label class="space-y-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Окончание</span>
          <InputText v-model="dateEditForm.end_date" type="date" fluid />
        </label>
        <label class="space-y-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Цена</span>
          <InputText v-model="dateEditForm.price" type="number" min="0" fluid />
        </label>
        <label class="space-y-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Валюта</span>
          <Select v-model="dateEditForm.currency" :options="currencyOptions" option-label="label" option-value="value" fluid />
        </label>
        <label class="space-y-2 md:col-span-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Места</span>
          <InputText v-model="dateEditForm.available_seats" type="number" min="0" fluid />
        </label>
      </div>
      <small v-if="Object.keys(dateErrors).length" class="mt-4 block text-red-600">
        {{ getFieldError(dateErrors, 'start_date') || getFieldError(dateErrors, 'end_date') || getFieldError(dateErrors, 'price') || getFieldError(dateErrors, 'currency') || getFieldError(dateErrors, 'available_seats') }}
      </small>
      <template #footer>
        <Button label="Отмена" severity="secondary" outlined @click="dateDialogVisible = false" />
        <Button label="Сохранить" icon="pi pi-save" :loading="dateSubmitting" @click="saveDateEdit" />
      </template>
    </Dialog>

    <Dialog v-model:visible="routeDialogVisible" modal header="Редактировать точку маршрута" :style="{ width: '44rem' }">
      <div class="grid gap-4 md:grid-cols-2">
        <label class="space-y-2 md:col-span-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Название</span>
          <InputText v-model="routeEditForm.title" fluid />
        </label>
        <label class="space-y-2 md:col-span-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Описание</span>
          <Textarea v-model="routeEditForm.description" auto-resize rows="4" fluid />
        </label>
        <label class="space-y-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Широта</span>
          <InputText v-model="routeEditForm.latitude" type="number" fluid />
        </label>
        <label class="space-y-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Долгота</span>
          <InputText v-model="routeEditForm.longitude" type="number" fluid />
        </label>
        <label class="space-y-2 md:col-span-2">
          <span class="text-sm font-medium text-[var(--travel-ink)]">Порядок</span>
          <InputText v-model="routeEditForm.sort_order" type="number" min="0" fluid />
        </label>
      </div>
      <small v-if="Object.keys(routeErrors).length" class="mt-4 block text-red-600">
        {{ getFieldError(routeErrors, 'title') || getFieldError(routeErrors, 'latitude') || getFieldError(routeErrors, 'longitude') || getFieldError(routeErrors, 'sort_order') }}
      </small>
      <template #footer>
        <Button label="Отмена" severity="secondary" outlined @click="routeDialogVisible = false" />
        <Button label="Сохранить" icon="pi pi-save" :loading="routeSubmitting" @click="saveRouteEdit" />
      </template>
    </Dialog>
  </AdminLayout>
</template>
