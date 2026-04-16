<template>
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <UiAppSectionHeader
                title="Team Members"
                description="Manage who has access to your workspace and their roles."
            />
            <UiAppButton v-if="isOwnerOrAdmin" @click="showCreate = true">
                <UserPlus class="w-4 h-4" />
                Add Member
            </UiAppButton>
        </div>

        <!-- Loading -->
        <UiAppCard v-if="loading" :no-padding="true">
            <div class="flex items-center justify-center py-16 text-text-muted">
                <svg
                    class="w-5 h-5 animate-spin mr-2"
                    fill="none"
                    viewBox="0 0 24 24"
                >
                    <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                    />
                    <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                    />
                </svg>
                Loading…
            </div>
        </UiAppCard>

        <!-- Users Table -->
        <UiAppCard v-else :no-padding="true">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border bg-surface-alt">
                            <th
                                class="px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider text-left"
                            >
                                Name
                            </th>
                            <th
                                class="px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider text-left"
                            >
                                Email
                            </th>
                            <th
                                class="px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider text-left"
                            >
                                Role
                            </th>
                            <th
                                class="px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider text-left"
                            >
                                Status
                            </th>
                            <th
                                class="px-6 py-3.5 text-xs font-semibold text-text-muted uppercase tracking-wider text-right"
                            >
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-border">
                        <tr
                            v-for="u in users"
                            :key="u.id"
                            class="hover:bg-surface-alt/50 transition-colors"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center text-sm font-bold shrink-0"
                                    >
                                        {{ u.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <span class="font-medium text-text">{{
                                        u.name
                                    }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-text-secondary">
                                {{ u.email }}
                            </td>
                            <td class="px-6 py-4">
                                <UiAppBadge :variant="roleBadge(u.role)">{{
                                    u.role
                                }}</UiAppBadge>
                            </td>
                            <td class="px-6 py-4">
                                <UiAppBadge
                                    :variant="
                                        u.is_active ? 'success' : 'danger'
                                    "
                                >
                                    {{ u.is_active ? "Active" : "Inactive" }}
                                </UiAppBadge>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div
                                    v-if="u.role !== 'owner'"
                                    class="flex items-center justify-end gap-1"
                                >
                                    <button
                                        class="p-1.5 rounded-lg text-text-muted hover:text-primary hover:bg-primary/10 transition-colors"
                                        title="Edit"
                                        @click="openEdit(u)"
                                    >
                                        <Pencil class="w-4 h-4" />
                                    </button>
                                    <button
                                        class="p-1.5 rounded-lg text-text-muted hover:text-danger hover:bg-danger/10 transition-colors"
                                        title="Delete"
                                        @click="confirmDelete(u)"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                                <span v-else class="text-xs text-text-muted"
                                    >—</span
                                >
                            </td>
                        </tr>
                        <tr v-if="users.length === 0">
                            <td
                                colspan="5"
                                class="px-6 py-12 text-center text-text-muted"
                            >
                                No team members found.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </UiAppCard>

        <!-- Create Modal -->
        <UiAppModal v-model="showCreate" title="Add Team Member" size="md">
            <form class="space-y-4" @submit.prevent="handleCreate">
                <UiAppFormField
                    label="Name"
                    :error="createErrors.name"
                    required
                >
                    <input
                        v-model="createForm.name"
                        type="text"
                        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    />
                </UiAppFormField>

                <UiAppFormField
                    label="Email"
                    :error="createErrors.email"
                    required
                >
                    <input
                        v-model="createForm.email"
                        type="email"
                        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    />
                </UiAppFormField>

                <UiAppFormField
                    label="Password"
                    :error="createErrors.password"
                    required
                >
                    <input
                        v-model="createForm.password"
                        type="password"
                        autocomplete="new-password"
                        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    />
                </UiAppFormField>

                <UiAppFormField label="Confirm Password" required>
                    <input
                        v-model="createForm.password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    />
                </UiAppFormField>

                <UiAppFormField
                    label="Role"
                    :error="createErrors.role"
                    required
                >
                    <select
                        v-model="createForm.role"
                        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
                    >
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </UiAppFormField>
            </form>

            <template #footer>
                <UiAppButton variant="outline" @click="showCreate = false"
                    >Cancel</UiAppButton
                >
                <UiAppButton :loading="creating" @click="handleCreate"
                    >Create</UiAppButton
                >
            </template>
        </UiAppModal>

        <!-- Edit Modal -->
        <UiAppModal v-model="showEdit" title="Edit Member" size="md">
            <form
                v-if="editUser"
                class="space-y-4"
                @submit.prevent="handleUpdate"
            >
                <UiAppFormField label="Name">
                    <input
                        v-model="editForm.name"
                        type="text"
                        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm text-text bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all"
                    />
                </UiAppFormField>

                <UiAppFormField label="Role">
                    <select
                        v-model="editForm.role"
                        class="w-full border border-border rounded-xl px-4 py-2.5 text-sm bg-surface-alt focus:bg-surface focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/10 transition-all appearance-none"
                    >
                        <option value="admin">Admin</option>
                        <option value="staff">Staff</option>
                    </select>
                </UiAppFormField>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        v-model="editForm.is_active"
                        type="checkbox"
                        class="w-4 h-4 rounded border-border text-primary focus:ring-primary/20"
                    />
                    <span class="text-sm font-medium text-text">Active</span>
                </label>
            </form>

            <template #footer>
                <UiAppButton variant="outline" @click="showEdit = false"
                    >Cancel</UiAppButton
                >
                <UiAppButton :loading="updating" @click="handleUpdate"
                    >Save</UiAppButton
                >
            </template>
        </UiAppModal>

        <!-- Delete Confirmation -->
        <UiAppModal v-model="showDeleteConfirm" title="Delete Member" size="sm">
            <p class="text-sm text-text-secondary">
                Are you sure you want to remove
                <strong class="text-text">{{ deleteTarget?.name }}</strong> from
                the team? This action cannot be undone.
            </p>
            <template #footer>
                <UiAppButton
                    variant="outline"
                    @click="showDeleteConfirm = false"
                    >Cancel</UiAppButton
                >
                <UiAppButton
                    variant="danger"
                    :loading="deleting"
                    @click="handleDelete"
                    >Delete</UiAppButton
                >
            </template>
        </UiAppModal>
    </div>
</template>

<script setup lang="ts">
import { UserPlus, Pencil, Trash2 } from "lucide-vue-next";

definePageMeta({
    layout: "default",
    middleware: ["auth", "tenant-admin"],
});

const toast = useToast();
const { user } = useAuth();
const { fetchUsers, createUser, updateUser, deleteUser } = useSettings();

const loading = ref(true);
const creating = ref(false);
const updating = ref(false);
const deleting = ref(false);

interface TenantUser {
    id: number;
    name: string;
    email: string;
    role: "owner" | "admin" | "staff";
    is_active: boolean;
    last_login_at: string | null;
    created_at: string;
}

const users = ref<TenantUser[]>([]);

const isOwnerOrAdmin = computed(
    () => user.value && ["owner", "admin"].includes(user.value.role),
);

// ── Create ──
const showCreate = ref(false);
const createForm = reactive({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
    role: "staff",
});
const createErrors = reactive<Record<string, string>>({});

// ── Edit ──
const showEdit = ref(false);
const editUser = ref<TenantUser | null>(null);
const editForm = reactive({ name: "", role: "staff", is_active: true });

// ── Delete ──
const showDeleteConfirm = ref(false);
const deleteTarget = ref<TenantUser | null>(null);

function roleBadge(role: string) {
    if (role === "owner") return "primary" as const;
    if (role === "admin") return "warning" as const;
    return "neutral" as const;
}

onMounted(async () => {
    try {
        users.value = await fetchUsers();
    } catch {
        toast.error("Failed to load team members.");
    } finally {
        loading.value = false;
    }
});

async function handleCreate() {
    creating.value = true;
    Object.keys(createErrors).forEach((k) => delete createErrors[k]);
    try {
        const res = await createUser(createForm);
        users.value.unshift(res.user);
        showCreate.value = false;
        createForm.name = "";
        createForm.email = "";
        createForm.password = "";
        createForm.password_confirmation = "";
        createForm.role = "staff";
        toast.success("Team member added.");
    } catch (err: any) {
        const data = err?.response?._data;
        if (data?.errors) {
            for (const [k, v] of Object.entries(data.errors)) {
                createErrors[k] = Array.isArray(v) ? v[0] : (v as string);
            }
        } else {
            toast.error(data?.message || "Failed to create user.");
        }
    } finally {
        creating.value = false;
    }
}

function openEdit(u: TenantUser) {
    editUser.value = u;
    editForm.name = u.name;
    editForm.role = u.role;
    editForm.is_active = u.is_active;
    showEdit.value = true;
}

async function handleUpdate() {
    if (!editUser.value) return;
    updating.value = true;
    try {
        const res = await updateUser(editUser.value.id, {
            name: editForm.name,
            role: editForm.role,
            is_active: editForm.is_active,
        });
        const idx = users.value.findIndex((u) => u.id === editUser.value!.id);
        if (idx !== -1) users.value[idx] = res.user;
        showEdit.value = false;
        toast.success("Member updated.");
    } catch (err: any) {
        toast.error(
            err?.response?._data?.message || "Failed to update member.",
        );
    } finally {
        updating.value = false;
    }
}

function confirmDelete(u: TenantUser) {
    deleteTarget.value = u;
    showDeleteConfirm.value = true;
}

async function handleDelete() {
    if (!deleteTarget.value) return;
    deleting.value = true;
    try {
        await deleteUser(deleteTarget.value.id);
        users.value = users.value.filter(
            (u) => u.id !== deleteTarget.value!.id,
        );
        showDeleteConfirm.value = false;
        toast.success("Member removed.");
    } catch (err: any) {
        toast.error(
            err?.response?._data?.message || "Failed to delete member.",
        );
    } finally {
        deleting.value = false;
    }
}
</script>
