<script setup lang="ts">
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Send, MessageSquare, Users, GraduationCap, DollarSign, FileText } from 'lucide-vue-next';
import { ref, computed, watch } from 'vue';

const props = defineProps<{
    students: any[];
    templates: any[];
    terms: any[];
}>();

const form = useForm({
    recipients: [] as Array<{ student_id?: number; phone: string; name?: string }>,
    message: '',
    type: 'general',
    student_ids: [] as number[],
    term_id: '',
});

const search = ref('');
const selectedClass = ref('');
const selectedTemplate = ref('');
const selectAll = ref(false);

const uniqueClasses = computed(() => {
    const classes = new Map();
    props.students.forEach((student: any) => {
        if (student.class && !classes.has(student.class.id)) {
            classes.set(student.class.id, student.class);
        }
    });

    return Array.from(classes.values()).sort((a: any, b: any) => a.name.localeCompare(b.name));
});

const filteredStudents = computed(() => {
    let students = props.students;

    if (selectedClass.value) {
        students = students.filter((s: any) => s.class?.id === Number(selectedClass.value));
    }

    if (search.value.trim()) {
        const term = search.value.toLowerCase();
        students = students.filter((s: any) =>
            `${s.first_name} ${s.last_name}`.toLowerCase().includes(term) ||
            s.student_id?.toLowerCase().includes(term) ||
            s.parent_name?.toLowerCase().includes(term)
        );
    }

    return students;
});

const isStudentSelected = (studentId: number) => {
    return form.student_ids.includes(studentId);
};

const toggleStudent = (student: any) => {
    const index = form.student_ids.indexOf(student.id);

    if (index > -1) {
        form.student_ids.splice(index, 1);
    } else {
        form.student_ids.push(student.id);
    }
};

const toggleSelectAll = () => {
    selectAll.value = !selectAll.value;

    if (selectAll.value) {
        form.student_ids = filteredStudents.value.map((s: any) => s.id);
    } else {
        form.student_ids = [];
    }
};

const selectedStudents = computed(() => {
    return props.students.filter((s: any) => form.student_ids.includes(s.id));
});

watch(selectedTemplate, (templateSlug) => {
    const template = props.templates.find((t: any) => t.slug === templateSlug);

    if (template) {
        form.message = template.message;
        form.type = template.type;
    }
});

const messageLength = computed(() => form.message.length);
const smsCount = computed(() => Math.ceil(messageLength.value / 160));

const submit = () => {
    form.recipients = selectedStudents.value.map((s: any) => ({
        student_id: s.id,
        phone: s.parent_phone,
        name: s.parent_name,
    }));

    if (form.recipients.length === 0) {
        alert('Please select at least one recipient!');

        return;
    }

    if (!form.message.trim()) {
        alert('Please enter a message!');

        return;
    }

    form.post('/sms/send', {
        preserveScroll: true,
    });
};

defineOptions({
    layout: () => ({
        breadcrumbs: [
            { title: 'SMS', href: '/sms' },
            { title: 'Compose', href: '/sms/compose' },
        ],
    }),
});
</script>

<template>
    <Head title="Compose SMS" />
    
    <div class="min-h-screen bg-gradient-to-b from-amber-50 via-white to-amber-50 text-gray-900">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">
            <!-- Header -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-amber-100/60 pb-5">
                <div class="flex items-center gap-3">
                    <Link
                        href="/sms"
                        class="p-2 rounded-lg hover:bg-gray-100 transition-colors"
                    >
                        <ArrowLeft class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-900">
                            Compose SMS
                        </h1>
                        <p class="text-gray-600 mt-1 text-sm">
                            Send SMS messages to parents and guardians
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid lg:grid-cols-2 gap-6">
                <!-- Recipients Selection -->
                <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                    <div class="p-5 border-b border-amber-100 bg-amber-50/50">
                        <div class="flex items-center gap-2">
                            <Users class="w-5 h-5 text-amber-700" />
                            <h2 class="text-lg font-semibold text-gray-900">Select Recipients</h2>
                            <span v-if="selectedStudents.length > 0" class="ml-auto px-2.5 py-0.5 bg-amber-200 text-amber-800 text-xs font-semibold rounded-full">
                                {{ selectedStudents.length }} selected
                            </span>
                        </div>
                    </div>

                    <div class="p-5 space-y-4">
                        <!-- Search & Filter -->
                        <div class="flex flex-col sm:flex-row gap-3">
                            <div class="flex-1 relative">
                                <input
                                    v-model="search"
                                    type="text"
                                    placeholder="Search students..."
                                    class="block w-full pl-3 pr-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                                />
                            </div>
                            <select
                                v-model="selectedClass"
                                class="px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                            >
                                <option value="">All Classes</option>
                                <option v-for="cls in uniqueClasses" :key="cls.id" :value="cls.id">
                                    {{ cls.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Select All -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    :checked="selectAll"
                                    @change="toggleSelectAll"
                                    class="w-4 h-4 text-lime-600 border-gray-300 rounded focus:ring-lime-500"
                                />
                                <span class="text-sm text-gray-700">Select all students</span>
                            </label>
                            <span class="text-xs text-gray-500">{{ filteredStudents.length }} students</span>
                        </div>

                        <!-- Students List -->
                        <div class="border border-gray-200 rounded-lg max-h-80 overflow-y-auto">
                            <div v-for="student in filteredStudents" :key="student.id" class="flex items-center gap-3 p-3 hover:bg-amber-50/50 transition-colors border-b border-gray-100 last:border-b-0">
                                <input
                                    type="checkbox"
                                    :checked="isStudentSelected(student.id)"
                                    @change="toggleStudent(student)"
                                    class="w-4 h-4 text-lime-600 border-gray-300 rounded focus:ring-lime-500"
                                />
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <GraduationCap class="w-4 h-4 text-amber-500" />
                                        <span class="text-sm font-medium text-gray-900">
                                            {{ student.first_name }} {{ student.last_name }}
                                        </span>
                                        <span class="text-xs text-gray-500">{{ student.student_id }}</span>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-1">
                                        {{ student.class?.name }} • {{ student.parent_name }} • {{ student.parent_phone }}
                                    </p>
                                </div>
                            </div>
                            <div v-if="filteredStudents.length === 0" class="p-8 text-center text-gray-500">
                                No students found.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Message Composition -->
                <div class="space-y-6">
                    <!-- Templates -->
                    <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                        <div class="p-5 border-b border-amber-100 bg-amber-50/50">
                            <div class="flex items-center gap-2">
                                <FileText class="w-5 h-5 text-amber-700" />
                                <h2 class="text-lg font-semibold text-gray-900">Message Template</h2>
                            </div>
                        </div>
                        <div class="p-5">
                            <select
                                v-model="selectedTemplate"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                            >
                                <option value="">Select a template...</option>
                                <option v-for="template in templates" :key="template.id" :value="template.slug">
                                    {{ template.name }}
                                </option>
                            </select>
                            <p class="text-xs text-gray-500 mt-2">
                                Variables: {student_name}, {parent_name}, {school_name}, {balance}, {amount}
                            </p>
                        </div>
                    </div>

                    <!-- Message -->
                    <div class="bg-white rounded-2xl border border-amber-100 shadow-xl shadow-amber-900/[0.01] overflow-hidden">
                        <div class="p-5 border-b border-amber-100 bg-amber-50/50">
                            <div class="flex items-center gap-2">
                                <MessageSquare class="w-5 h-5 text-amber-700" />
                                <h2 class="text-lg font-semibold text-gray-900">SMS Message</h2>
                                <span class="ml-auto px-2.5 py-0.5 bg-gray-100 text-gray-600 text-xs font-semibold rounded-full">
                                    {{ messageLength }} chars • {{ smsCount }} SMS
                                </span>
                            </div>
                        </div>
                        <div class="p-5 space-y-4">
                            <select
                                v-model="form.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition"
                            >
                                <option value="general">General</option>
                                <option value="payment_confirmation">Payment Confirmation</option>
                                <option value="balance_reminder">Balance Reminder</option>
                                <option value="announcement">Announcement</option>
                                <option value="attendance">Attendance</option>
                            </select>
                            <textarea
                                v-model="form.message"
                                rows="8"
                                placeholder="Type your message here..."
                                class="block w-full px-3 py-2 border border-gray-300 rounded-lg bg-white/80 backdrop-blur-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent transition resize-none"
                            ></textarea>
                            <div class="flex items-center justify-between">
                                <span class="text-xs" :class="messageLength > 1600 ? 'text-red-600' : 'text-gray-500'">
                                    {{ messageLength > 1600 ? 'Message too long!' : 'SMS length: 1-1600 characters' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Send Button -->
                    <button
                        @click="submit"
                        :disabled="form.processing || form.recipients.length === 0"
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-lime-400 hover:bg-lime-500 text-gray-900 text-sm font-semibold rounded-xl shadow-sm transition-all cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <Send class="w-4 h-4" />
                        {{ form.processing ? 'Sending...' : `Send to ${selectedStudents.length} Recipient${selectedStudents.length === 1 ? '' : 's'}` }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
