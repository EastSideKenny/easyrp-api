<template>
  <div
    class="min-h-screen bg-surface-alt font-sans text-text flex items-center justify-center p-6"
  >
    <div class="max-w-md w-full text-center space-y-6">
      <div
        class="w-16 h-16 mx-auto rounded-2xl bg-danger/10 flex items-center justify-center"
      >
        <span class="text-3xl">🚫</span>
      </div>

      <div class="space-y-2">
        <h1 class="text-2xl font-bold text-text">Workspace Not Found</h1>
        <p class="text-sm text-text-secondary leading-relaxed">
          {{ errorMessage }}
        </p>
      </div>

      <div class="space-y-3">
        <p class="text-xs text-text-muted">
          If you believe this is a mistake, please contact the workspace owner
          or check the URL and try again.
        </p>
        <a
          :href="mainSiteUrl"
          class="inline-block bg-primary text-white text-sm font-semibold px-6 py-2.5 rounded-xl hover:bg-primary-dark hover:shadow-elevated transition-all duration-200"
        >
          Go to EasyRP
        </a>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({
  layout: false,
});

const config = useRuntimeConfig();
const { tenantError } = useTenant();
const route = useRoute();

const errorMessage = computed(() => {
  if (route.query.reason === "access_denied") {
    return "You don't have access to this workspace. You have been signed out.";
  }
  return (
    tenantError.value ||
    "The workspace you are looking for does not exist or has been deactivated."
  );
});

const mainSiteUrl = computed(() => {
  const domain = config.public.appDomain as string;
  const protocol = import.meta.dev ? "http" : "https";
  return `${protocol}://${domain}`;
});
</script>
