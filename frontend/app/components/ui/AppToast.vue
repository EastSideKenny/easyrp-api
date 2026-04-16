<template>
  <div
    class="fixed bottom-4 right-4 z-99999 flex flex-col gap-3 pointer-events-none"
  >
    <TransitionGroup
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="translate-x-8 opacity-0"
      enter-to-class="translate-x-0 opacity-100"
      leave-from-class="translate-x-0 opacity-100"
      leave-to-class="translate-x-8 opacity-0"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 max-w-sm w-full rounded-xl border px-4 py-3 shadow-xl"
        :class="variantClasses[toast.variant]"
      >
        <component
          :is="variantIcon[toast.variant]"
          class="w-5 h-5 shrink-0 mt-0.5"
        />
        <p class="text-sm font-medium flex-1">{{ toast.message }}</p>
        <button
          type="button"
          class="shrink-0 opacity-60 hover:opacity-100 transition-opacity"
          @click="remove(toast.id)"
        >
          <X class="w-4 h-4" />
        </button>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import {
  CheckCircle2,
  AlertCircle,
  AlertTriangle,
  Info,
  X,
} from "lucide-vue-next";

const { toasts, remove } = useToast();

const variantClasses: Record<string, string> = {
  success: "bg-[#ecfdf5] border-success/30 text-success",
  error: "bg-[#fef2f2] border-danger/30 text-danger",
  warning: "bg-[#fffbeb] border-warning/30 text-warning",
  info: "bg-[#eff6ff] border-info/30 text-info",
};

const variantIcon: Record<string, any> = {
  success: CheckCircle2,
  error: AlertCircle,
  warning: AlertTriangle,
  info: Info,
};
</script>
