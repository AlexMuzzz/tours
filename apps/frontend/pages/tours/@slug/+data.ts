import { ApiClientError } from '@/services/apiClient';
import { tourService } from '@/services/tourService';
import { getErrorMessage } from '@/utils/errors';
import type { Tour } from '@/types/api';

export { data };
export type Data = Awaited<ReturnType<typeof data>>;

interface TourDetailPageContext {
  routeParams: {
    slug: string;
  };
}

async function data(pageContext: TourDetailPageContext) {
  const slug = pageContext.routeParams.slug;

  try {
    const tour = await tourService.getTour(slug);

    return {
      tour: tour as Tour,
      error: null as string | null,
    };
  } catch (error) {
    const message = error instanceof ApiClientError && error.status === 404
      ? 'Тур не найден или сейчас недоступен в публичном каталоге.'
      : getErrorMessage(error, 'Не удалось загрузить тур.');

    return {
      tour: null as Tour | null,
      error: message,
    };
  }
}
