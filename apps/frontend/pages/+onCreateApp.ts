import { createPinia } from 'pinia';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import ToastService from 'primevue/toastservice';
import ConfirmationService from 'primevue/confirmationservice';
import type { App } from 'vue';
import '@/styles/main.css';

export { onCreateApp };

function onCreateApp(pageContext: { app?: App }) {
  const app = pageContext.app;

  if (!app) {
    return;
  }

  const pinia = createPinia();

  app.use(pinia);
  app.use(ToastService);
  app.use(ConfirmationService);
  app.use(PrimeVue, {
    ripple: true,
    inputStyle: 'filled',
    theme: {
      preset: Aura,
      options: {
        darkModeSelector: '.travel-dark',
        cssLayer: {
          name: 'primevue',
          order: 'theme, base, primevue',
        },
      },
    },
  });
}
