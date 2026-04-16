<template>
    <div class="min-h-screen bg-surface-alt font-sans text-text">
        <!-- Top bar -->
        <header class="bg-surface border-b border-border sticky top-0 z-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center gap-3">
                        <NuxtLink to="/admin" class="flex items-center gap-2.5">
                            <div
                                class="w-9 h-9 rounded-xl bg-linear-to-br from-primary to-primary-light flex items-center justify-center"
                            >
                                <span class="text-white text-sm font-bold"
                                    >E</span
                                >
                            </div>
                            <span
                                class="font-semibold text-lg tracking-tight text-text"
                                >EasyRP</span
                            >
                        </NuxtLink>
                        <span
                            class="text-xs font-medium bg-primary/10 text-primary px-2 py-0.5 rounded-full"
                            >Admin</span
                        >
                    </div>

                    <nav class="flex items-center gap-1">
                        <NuxtLink
                            to="/admin"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="
                                $route.path === '/admin'
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-text-secondary hover:text-text hover:bg-surface-alt'
                            "
                        >
                            Overview
                        </NuxtLink>
                        <NuxtLink
                            to="/admin/tenants"
                            class="px-3 py-2 text-sm font-medium rounded-lg transition-colors"
                            :class="
                                $route.path.startsWith('/admin/tenants')
                                    ? 'bg-primary/10 text-primary'
                                    : 'text-text-secondary hover:text-text hover:bg-surface-alt'
                            "
                        >
                            Tenants
                        </NuxtLink>
                    </nav>

                    <div class="flex items-center gap-3">
                        <span class="text-sm text-text-secondary">{{
                            user?.name
                        }}</span>
                        <button
                            class="text-sm text-text-muted hover:text-text transition-colors"
                            @click="handleLogout"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <slot />
        </main>
    </div>
</template>

<script setup lang="ts">
const { user, logout } = useAuth();

async function handleLogout() {
    await logout();
    navigateTo("/auth/login");
}
</script>
