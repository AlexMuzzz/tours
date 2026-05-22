import { ref, watch, type WatchSource } from 'vue';

export function useImageAvailability(source: WatchSource<string>) {
  const isBroken = ref(false);
  const isLoaded = ref(false);

  watch(
    source,
    (url, _previousUrl, onCleanup) => {
      isBroken.value = false;
      isLoaded.value = false;

      if (!url || typeof Image === 'undefined') {
        return;
      }

      let disposed = false;
      const probe = new Image();

      const handleLoad = () => {
        if (disposed) {
          return;
        }

        isLoaded.value = true;
        isBroken.value = false;
      };

      const handleError = () => {
        if (disposed) {
          return;
        }

        isLoaded.value = false;
        isBroken.value = true;
      };

      probe.addEventListener('load', handleLoad);
      probe.addEventListener('error', handleError);
      probe.src = url;

      if (probe.complete) {
        if (probe.naturalWidth > 0) {
          handleLoad();
        } else {
          handleError();
        }
      }

      onCleanup(() => {
        disposed = true;
        probe.removeEventListener('load', handleLoad);
        probe.removeEventListener('error', handleError);
      });
    },
    { immediate: true },
  );

  return {
    isBroken,
    isLoaded,
  };
}
