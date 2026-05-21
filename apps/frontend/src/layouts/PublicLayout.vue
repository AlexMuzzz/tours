<template>
  <div class="page-shell">
    <header class="sticky top-0 z-20 border-b border-white/50 bg-[rgba(251,247,239,0.75)] backdrop-blur-xl">
      <div class="content-wrapper flex items-center gap-4 py-4">
        <a href="/" class="flex items-center gap-3">
          <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[linear-gradient(135deg,var(--travel-ocean),var(--travel-forest))] text-lg font-semibold text-white">
            TA
          </div>
          <div>
            <div class="text-xs uppercase tracking-[0.28em] text-[var(--travel-muted)]">
              Tour Catalog AI
            </div>
            <div class="text-lg font-semibold text-[var(--travel-ink)]">
              Каталог туров
            </div>
          </div>
        </a>
      </div>
    </header>

    <main class="flex-1">
      <slot />
    </main>

    <footer class="mt-10 border-t border-white/50 bg-white/50">
      <div class="content-wrapper flex flex-col gap-2 py-4 text-xs text-[var(--travel-muted)] md:flex-row md:items-center md:justify-between">
        <div>Tour Catalog AI MVP на Vue + Vike + PrimeVue.</div>
        <div>Каталог туров, semantic search и демо-админка.</div>
      </div>
    </footer>
  </div>
</template>
