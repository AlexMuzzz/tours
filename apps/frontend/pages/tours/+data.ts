import { loadCatalogPageData } from '@/pages/public/catalogPageData';

export { loadCatalogPageData as data };
export type Data = Awaited<ReturnType<typeof loadCatalogPageData>>;
