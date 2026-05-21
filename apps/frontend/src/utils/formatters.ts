import type { CurrencyCode, TourCategory } from '@/types/api';

const categoryLabels: Record<TourCategory, string> = {
  hiking: 'Хайкинг',
  city: 'Городской',
  gastro: 'Гастрономия',
  nature: 'Природа',
  winter: 'Зимний',
  adventure: 'Приключение',
  culture: 'Культура',
};

export function formatCategory(category: TourCategory): string {
  return categoryLabels[category] ?? category;
}

export function formatDuration(days: number): string {
  const remainder10 = days % 10;
  const remainder100 = days % 100;

  if (remainder10 === 1 && remainder100 !== 11) {
    return `${days} день`;
  }

  if (remainder10 >= 2 && remainder10 <= 4 && (remainder100 < 12 || remainder100 > 14)) {
    return `${days} дня`;
  }

  return `${days} дней`;
}

export function formatDate(value: string | null): string {
  if (!value) {
    return 'Дата уточняется';
  }

  return new Intl.DateTimeFormat('ru-RU', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  }).format(new Date(value));
}

export function formatDateRange(start: string | null, end: string | null): string {
  if (!start && !end) {
    return 'Даты уточняются';
  }

  if (!start || !end) {
    return formatDate(start ?? end);
  }

  return `${formatDate(start)} - ${formatDate(end)}`;
}

export function formatPrice(value: number | null, currency: CurrencyCode = 'RUB'): string {
  if (value === null) {
    return 'По запросу';
  }

  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency,
    maximumFractionDigits: 0,
  }).format(value);
}

export function formatDateTime(value: string | null): string {
  if (!value) {
    return '—';
  }

  return new Intl.DateTimeFormat('ru-RU', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(new Date(value));
}
