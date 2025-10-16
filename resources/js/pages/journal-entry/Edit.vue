<script setup lang="ts">
import journalEntry from '@/routes/journal-entries';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

// Components & layout
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';

// Types
import type { BreadcrumbItem } from '@/types';
import type { Account } from '@/types/account';
import type {
    JournalEntry,
    JournalEntryUpdatePayload,
    LinePosition,
} from '@/types/journalEntry';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Journal Entry', href: journalEntry.index().url },
    { title: 'Edit', href: '#' },
];

const props = defineProps<{
    entry: JournalEntry;
    accounts: Account[];
}>();

const form = useForm<JournalEntryUpdatePayload>({
    number: props.entry.number ?? '',
    date: props.entry.date ?? new Date().toISOString().slice(0, 10),
    memo: props.entry.memo ?? '',
    lines: (props.entry.lines ?? []).map((l, i) => ({
        account_id: l.account_id,
        position: l.position as LinePosition,
        amount: Number(l.amount ?? 0),
        description: l.description ?? '',
        line_number: l.line_number ?? i + 1,
    })),
});

import { onMounted } from 'vue';

onMounted(() => {
    console.log('ENTRY RAW:', JSON.parse(JSON.stringify(props.entry)));
    console.log('ENTRY LINES LENGTH:', props.entry.lines?.length);
});

// ===== Helpers =====
const addLine = (pos: LinePosition = 'debit') => {
    form.lines.push({
        account_id: null as any,
        position: pos,
        amount: 0,
        description: '',
        line_number: (form.lines?.length || 0) + 1,
    });
};

const removeLine = (idx: number) => {
    if (form.lines.length > 1) form.lines.splice(idx, 1);
    // reorder line_number
    form.lines.forEach((l, i) => (l.line_number = i + 1));
};

const debitTotal = computed(() =>
    form.lines.reduce(
        (s, l) => s + (l.position === 'debit' ? Number(l.amount || 0) : 0),
        0,
    ),
);
const creditTotal = computed(() =>
    form.lines.reduce(
        (s, l) => s + (l.position === 'credit' ? Number(l.amount || 0) : 0),
        0,
    ),
);
const isBalanced = computed(
    () => Math.abs(debitTotal.value - creditTotal.value) < 0.000001,
);

const submit = () => {
    form.put(journalEntry.update(props.entry.id).url, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Edit ${props.entry.number}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div
                    class="w-full rounded-xl border-gray-200 bg-white shadow-md"
                >
                    <div
                        class="flex items-center justify-between border-b px-6 py-4"
                    >
                        <HeadingSmall
                            :title="`Edit Journal Entry`"
                            :description="`Perbarui data jurnal ${props.entry.number}`"
                            class="text-gray-800"
                        />
                        <div class="flex gap-2">
                            <button
                                type="button"
                                class="rounded-md border px-3 py-1.5 text-sm text-black hover:bg-gray-50"
                                @click="() => addLine('debit')"
                            >
                                + Add Debit
                            </button>
                            <button
                                type="button"
                                class="rounded-md border px-3 py-1.5 text-sm text-black hover:bg-gray-50"
                                @click="() => addLine('credit')"
                            >
                                + Add Credit
                            </button>
                        </div>
                    </div>
                </div>

                <div class="h-5" />

                <!-- Journal Information -->
                <div
                    class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md"
                >
                    <HeadingSmall
                        title="Journal Information"
                        description="Ubah nomor, tanggal, dan memo"
                        class="text-gray-800"
                    />

                    <div class="mt-4 grid gap-4 md:grid-cols-3">
                        <!-- Number -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Number</label
                            >
                            <input
                                v-model="form.number"
                                type="text"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                placeholder="e.g. JRN-2025-0001"
                            />
                            <div
                                v-if="form.errors.number"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.number }}
                            </div>
                        </div>

                        <!-- Date -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Date</label
                            >
                            <input
                                v-model="form.date"
                                type="date"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                            />
                            <div
                                v-if="form.errors.date"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.date }}
                            </div>
                        </div>

                        <!-- Memo -->
                        <div>
                            <label
                                class="mb-1 block text-sm font-medium text-gray-700"
                                >Memo</label
                            >
                            <input
                                v-model="form.memo"
                                type="text"
                                class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                placeholder="Deskripsi singkat"
                            />
                            <div
                                v-if="form.errors.memo"
                                class="mt-1 text-sm text-red-600"
                            >
                                {{ form.errors.memo }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="h-5" />

                <!-- Lines -->
                <div
                    class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md"
                >
                    <div class="flex items-center justify-between">
                        <HeadingSmall
                            title="Lines"
                            description="Edit baris debit/kredit"
                            class="text-gray-800"
                        />
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-lg border">
                        <table class="min-w-full">
                            <thead class="bg-gray-100 text-sm text-black">
                                <tr>
                                    <th class="px-3 py-2 text-center">
                                        Account
                                    </th>
                                    <th class="px-3 py-2 text-center">
                                        Position
                                    </th>
                                    <th class="px-3 py-2 text-center">
                                        Amount
                                    </th>
                                    <th class="px-3 py-2 text-center">
                                        Description
                                    </th>
                                    <th class="px-3 py-2" />
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="(l, idx) in form.lines"
                                    :key="idx"
                                    class="border-t text-sm"
                                >
                                    <td class="px-3 py-2">
                                        <select
                                            v-model.number="l.account_id"
                                            class="w-72 rounded-md border px-2 py-1.5 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        >
                                            <option :value="null">
                                                — Select Account —
                                            </option>
                                            <option
                                                v-for="a in props.accounts"
                                                :key="a.id"
                                                :value="a.id"
                                            >
                                                {{ a.name }}
                                            </option>
                                        </select>
                                        <div
                                            v-if="
                                                form.errors[
                                                    `lines.${idx}.account_id`
                                                ]
                                            "
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{
                                                form.errors[
                                                    `lines.${idx}.account_id`
                                                ]
                                            }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-center">
                                        <select
                                            v-model="l.position as any"
                                            class="w-28 rounded-md border px-2 py-1.5 text-black capitalize focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        >
                                            <option value="debit">Debit</option>
                                            <option value="credit">
                                                Credit
                                            </option>
                                        </select>
                                        <div
                                            v-if="
                                                form.errors[
                                                    `lines.${idx}.position`
                                                ]
                                            "
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{
                                                form.errors[
                                                    `lines.${idx}.position`
                                                ]
                                            }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <input
                                            v-model.number="l.amount"
                                            type="number"
                                            min="0"
                                            step="any"
                                            class="w-40 rounded-md border px-2 py-1.5 text-right text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                        <div
                                            v-if="
                                                form.errors[
                                                    `lines.${idx}.amount`
                                                ]
                                            "
                                            class="mt-1 text-xs text-red-600"
                                        >
                                            {{
                                                form.errors[
                                                    `lines.${idx}.amount`
                                                ]
                                            }}
                                        </div>
                                    </td>

                                    <td class="px-3 py-2">
                                        <input
                                            v-model="l.description"
                                            type="text"
                                            placeholder="Keterangan (opsional)"
                                            class="w-full rounded-md border px-2 py-1.5 text-black focus:border-cyan-500 focus:ring focus:ring-cyan-200"
                                        />
                                    </td>

                                    <td class="px-3 py-2 text-right">
                                        <button
                                            type="button"
                                            class="text-red-600 hover:underline disabled:opacity-40"
                                            :disabled="form.lines.length === 1"
                                            @click="removeLine(idx)"
                                        >
                                            Remove
                                        </button>
                                    </td>
                                </tr>
                            </tbody>

                            <tfoot class="m-2 text-sm text-black">
                                <tr class="border-t bg-gray-50">
                                    <td
                                        colspan="2"
                                        class="px-3 py-2 text-right font-medium"
                                    >
                                        Debit Total
                                    </td>
                                    <td
                                        class="px-3 py-2 text-right font-medium"
                                    >
                                        {{
                                            debitTotal.toLocaleString('id-ID', {
                                                style: 'currency',
                                                currency: 'IDR',
                                            })
                                        }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr class="bg-gray-50">
                                    <td
                                        colspan="2"
                                        class="px-3 py-2 text-right"
                                    >
                                        Credit Total
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        {{
                                            creditTotal.toLocaleString(
                                                'id-ID',
                                                {
                                                    style: 'currency',
                                                    currency: 'IDR',
                                                },
                                            )
                                        }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr
                                    class="border-t"
                                    :class="
                                        isBalanced
                                            ? 'bg-green-50 text-green-800'
                                            : 'bg-red-50 text-red-700'
                                    "
                                >
                                    <td
                                        colspan="2"
                                        class="px-3 py-2 text-right font-semibold"
                                    >
                                        Status
                                    </td>
                                    <td
                                        class="px-3 py-2 text-right font-semibold"
                                    >
                                        {{
                                            isBalanced
                                                ? 'Balanced'
                                                : 'Not Balanced'
                                        }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div
                        v-if="form.errors.lines"
                        class="mt-2 text-sm text-red-600"
                    >
                        {{ form.errors.lines }}
                    </div>
                </div>

                <div class="h-5" />

                <!-- Actions -->
                <div class="flex flex-wrap items-center justify-end gap-2">
                    <button
                        type="button"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        @click="$inertia.visit(journalEntry.index().url)"
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none disabled:opacity-60"
                        :disabled="form.processing"
                        :title="isBalanced ? '' : 'Debit dan kredit belum sama'"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Changes' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
