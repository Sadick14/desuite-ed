<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { Plus, Search, Pencil, Trash2, X, User, Shield } from 'lucide-vue-next';
import { ref } from 'vue';

interface Role {
  value: string;
  label: string;
}

defineProps<{
  users: {
    data: any[];
    links: any[];
  };
  roles: Role[];
}>();

const showModal = ref(false);
const editingUser = ref<any | null>(null);
const search = ref('');

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: 'member',
});

const openCreate = () => {
  editingUser.value = null;
  form.reset();
  form.role = 'member';
  showModal.value = true;
};

const openEdit = (user: any) => {
  editingUser.value = user;
  form.name = user.name;
  form.email = user.email;
  form.password = '';
  form.role = user.role;
  showModal.value = true;
};

const saveUser = () => {
  const url = editingUser.value ? `/users/${editingUser.value.id}` : '/users';
  const method = editingUser.value ? 'put' : 'post';
  
  form[method](url, {
    onSuccess: () => {
      showModal.value = false;
    }
  });
};

const deleteUser = (id: number) => {
  if (confirm('Delete this user?')) {
    router.delete(`/users/${id}`);
  }
};

const getRoleBadgeClass = (role: string) => {
  const classes: Record<string, string> = {
    owner: 'bg-purple-100 text-purple-800 border border-purple-200',
    admin: 'bg-lime-100 text-lime-800 border border-lime-200',
    teacher: 'bg-blue-100 text-blue-800 border border-blue-200',
    student: 'bg-amber-100 text-amber-800 border border-amber-200',
    parent: 'bg-teal-100 text-teal-800 border border-teal-200',
    member: 'bg-gray-100 text-gray-800 border border-gray-200',
  };

  return classes[role] || 'bg-gray-100 text-gray-800 border border-gray-200';
};

const cleanLabel = (label: string) => {
  return label.replace(/&laquo;|&raquo;|&lsaquo;|&rsaquo;/g, '').trim();
};
</script>

<template>
  <Head title="Users" />

  <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
      
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
            User Management
          </h1>
          <p class="text-sm text-gray-600 mt-1">
            Manage system users and permissions
          </p>
        </div>
        
        <button
          @click="openCreate"
          class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-amber-500 cursor-pointer"
        >
          <Plus class="w-4 h-4 stroke-[2.5]" />
          Add User
        </button>
      </div>

      <div class="relative max-w-md shadow-sm rounded-lg">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <Search class="h-4 w-4 text-gray-400" />
        </div>
        <input
          v-model="search"
          type="text"
          placeholder="Search users..."
          class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
        />
        <div v-if="search" class="absolute inset-y-0 right-0 pr-3 flex items-center">
          <button @click="search = ''" class="text-gray-400 hover:text-gray-600 transition">
            <X class="h-4 w-4" />
          </button>
        </div>
      </div>

      <div class="border border-amber-100 rounded-xl overflow-hidden bg-white shadow-xl shadow-amber-900/[0.02] overflow-x-auto">
        <table class="min-w-full divide-y divide-amber-100">
          <thead class="bg-amber-50/70 backdrop-blur-sm">
            <tr>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                User
              </th>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Email
              </th>
              <th scope="col" class="px-5 py-3.5 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                Role
              </th>
              <th scope="col" class="px-5 py-3.5 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-amber-50 bg-white">
            <tr v-for="user in users.data" :key="user.id" class="hover:bg-amber-50/30 transition-colors">
              <td class="px-5 py-3.5 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-9 w-9 rounded-xl bg-gradient-to-br from-lime-100 to-lime-200/60 flex items-center justify-center border border-lime-200/40">
                    <User class="text-lime-700 w-4 h-4" />
                  </div>
                  <div class="ml-3">
                    <p class="text-sm font-semibold text-gray-900">
                      {{ user.name }}
                    </p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-sm text-gray-600">
                {{ user.email }}
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap">
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                  :class="getRoleBadgeClass(user.role)"
                >
                  <Shield class="w-3 h-3" />
                  {{ user.role }}
                </span>
              </td>
              <td class="px-5 py-3.5 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <button
                  @click="openEdit(user)"
                  class="text-green-600 hover:text-green-800 transition-colors"
                >
                  <Pencil class="w-4 h-4" />
                </button>
                <button
                  @click="deleteUser(user.id)"
                  class="text-red-500 hover:text-red-700 transition-colors"
                >
                  <Trash2 class="w-4 h-4" />
                </button>
              </td>
            </tr>
            <tr v-if="users.data.length === 0">
              <td colspan="4" class="px-6 py-16 text-center text-gray-500 font-medium">
                No users found.
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="users.links && users.links.length > 3" class="flex justify-center mt-8">
        <nav class="inline-flex rounded-xl shadow-sm -space-x-px bg-white p-1 border border-amber-100" aria-label="Pagination">
          <template v-for="(link, idx) in users.links" :key="idx">
            <component
              :is="link.url ? 'a' : 'span'"
              :href="link.url"
              :class="[
                'relative inline-flex items-center px-3.5 py-2 text-xs font-bold rounded-lg transition-all mx-0.5',
                link.active 
                  ? 'z-10 bg-lime-400 text-gray-900 border-transparent shadow-sm'
                  : 'text-gray-600 hover:bg-amber-50 border-transparent',
                !link.url && 'cursor-not-allowed opacity-40',
              ]"
              v-text="cleanLabel(link.label)"
            />
          </template>
        </nav>
      </div>
    </div>

    <!-- User Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" @click.self="showModal = false">
        <div class="flex min-h-screen items-center justify-center p-4">
          <div class="fixed inset-0 bg-amber-950/20 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

          <div class="relative bg-white rounded-2xl shadow-2xl border border-amber-100 max-w-md w-full transform transition-all overflow-hidden">
            
            <button
              @click="showModal = false"
              class="absolute top-4 right-4 text-gray-400 hover:text-gray-900 transition-colors p-1"
            >
              <X class="w-4 h-4" />
            </button>

            <div class="px-6 pt-6 pb-4 border-b border-amber-50">
              <h2 class="text-lg font-black text-gray-900 tracking-tight">
                {{ editingUser ? 'Edit User' : 'Create New User' }}
              </h2>
            </div>

            <div class="p-6 space-y-4">
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="User Name"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="email@example.com"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Password {{ editingUser ? '(leave blank to keep current)' : '' }}</label>
                <input
                  v-model="form.password"
                  type="password"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500"
                  placeholder="••••••••"
                />
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-1.5">Role</label>
                <select
                  v-model="form.role"
                  class="w-full border border-amber-200 rounded-xl px-3 py-2 text-sm font-medium text-gray-900 focus:outline-none focus:ring-2 focus:ring-amber-500 bg-white"
                >
                  <option
                    v-for="role in roles"
                    :key="role.value"
                    :value="role.value"
                  >
                    {{ role.label }}
                  </option>
                </select>
              </div>
            </div>

            <div class="px-6 py-4 border-t border-amber-50 flex justify-between bg-amber-50/10">
              <button
                @click="showModal = false"
                class="px-4 py-2 text-xs font-bold text-gray-500 border border-transparent rounded-xl hover:bg-amber-50/50 transition-colors focus:outline-none"
              >
                Cancel
              </button>
              <button
                @click="saveUser"
                class="px-4 py-2 bg-lime-400 hover:bg-lime-500 text-gray-900 rounded-xl disabled:opacity-40 disabled:cursor-not-allowed text-xs font-black uppercase tracking-wider border border-lime-500/20 transition-all"
              >
                Save
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>
