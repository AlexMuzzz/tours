import type { CurrencyCode, TourCategory } from '@/types/api';

export const categoryOptions: Array<{ label: string; value: TourCategory }> = [
  { label: 'Хайкинг', value: 'hiking' },
  { label: 'Городской', value: 'city' },
  { label: 'Гастрономия', value: 'gastro' },
  { label: 'Природа', value: 'nature' },
  { label: 'Зимний', value: 'winter' },
  { label: 'Приключение', value: 'adventure' },
  { label: 'Культура', value: 'culture' },
];

export const currencyOptions: Array<{ label: string; value: CurrencyCode }> = [
  { label: 'RUB', value: 'RUB' },
  { label: 'USD', value: 'USD' },
  { label: 'EUR', value: 'EUR' },
];

export const sortOptions: Array<{
  label: string;
  value: NonNullable<import('@/types/api').CatalogFilters['sort']>;
}> = [
  { label: 'Сначала новые', value: 'newest' },
  { label: 'Цена по возрастанию', value: 'price_asc' },
  { label: 'Цена по убыванию', value: 'price_desc' },
  { label: 'Длительность по возрастанию', value: 'duration_asc' },
  { label: 'Длительность по убыванию', value: 'duration_desc' },
];
