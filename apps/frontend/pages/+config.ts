import type { Config } from 'vike/types';
import vikeVue from 'vike-vue/config';

export default {
  extends: vikeVue,
  title: 'Tour Catalog AI',
  description: 'MVP-каталог туров с публичной витриной и admin-панелью.',
} satisfies Config;
