<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import journalEntries from '@/routes/journal-entries';
import { BreadcrumbItem } from '@/types';
import { JournalEntry } from '@/types/journalEntry';
import { computed } from 'vue';

const props = defineProps<{
    entry: JournalEntry;
    totals: { debit: number | string; credit: number | string };
    balanced: boolean;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Journal Entries', href: journalEntries.index().url },
    {
        title: `Detail ${props.entry.number}`,
        href: journalEntries.show(props.entry).url,
    },
];

const formatIDR = (n?: number | string | null) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(Number(n ?? 0));

const formatDate = (v?: string) =>
    v
        ? new Intl.DateTimeFormat('id-ID', {
              year: 'numeric',
              month: 'long',
              day: '2-digit',
          }).format(new Date(v))
        : '—';

const totalDebit = computed(() => Number(props.totals?.debit ?? 0));
const totalCredit = computed(() => Number(props.totals?.credit ?? 0));
const diff = computed(() => totalDebit.value - totalCredit.value);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <!-- Header ringkas -->
            <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                <div class="grid gap-y-3 md:grid-rows-2">
                    <div class="flex justify-between">
                        <div class="text-sm text-black">Journal Number</div>
                        <div class="text-sm font-bold text-black">
                            {{ props.entry.number }}
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <div class="text-sm text-black">Date</div>
                        <div class="text-sm font-bold text-black">
                            {{ formatDate(props.entry.date) }}
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <div class="text-sm text-black">Status</div>
                        <div v-if="props.entry.is_posted">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                                >POSTED</span
                            >
                        </div>
                        <div v-else>
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-amber-300 bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800"
                                >DRAFT</span
                            >
                        </div>
                    </div>

                    <div class="flex justify-between">
                        <div class="text-sm text-black">Balanced</div>
                        <div v-if="balanced">
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-green-300 bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"
                                >Seimbang</span
                            >
                        </div>
                        <div v-else>
                            <span
                                class="inline-flex items-center gap-1 rounded-full border border-red-300 bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800"
                                >Tidak Seimbang</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-5"></div>

            <!-- Informasi Memo -->
            <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                <HeadingSmall
                    title="Journal Information"
                    class="my-2 border-b text-gray-800"
                />
                <div class="grid gap-x-6 gap-y-3 py-2 md:grid-cols-1">
                    <div class="grid grid-cols-[140px_1fr] items-baseline">
                        <div class="text-sm text-black">Memo</div>
                        <div
                            class="text-sm font-bold whitespace-pre-line text-black"
                        >
                            {{ props.entry.memo || '—' }}
                        </div>
                    </div>
                    <div
                        v-if="props.entry.is_posted"
                        class="grid grid-cols-[140px_1fr] items-baseline"
                    >
                        <div class="text-sm text-black">Posted At</div>
                        <div class="text-sm font-bold text-black">
                            {{ formatDate(props.entry.updated_at) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-5"></div>

            <!-- Tabel Lines -->
            <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                <HeadingSmall
                    title="Journal Lines"
                    class="my-2 border-b text-gray-800"
                />

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="text-left text-gray-600">
                            <tr class="border-b">
                                <th class="py-2 pr-4">#</th>
                                <th class="py-2 pr-4">Account Code</th>
                                <th class="py-2 pr-4">Account Name</th>
                                <th class="py-2 pr-4">Position</th>
                                <th class="py-2 pr-4 text-right">Debit</th>
                                <th class="py-2 pr-4 text-right">Credit</th>
                                <th class="py-2 pr-0">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="ln in props.entry.lines"
                                :key="ln.id"
                                class="border-b last:border-0"
                            >
                                <td class="py-2 pr-4 text-black">
                                    {{ ln.line_number }}
                                </td>
                                <td class="py-2 pr-4 text-black">
                                    {{ ln.account?.code ?? '—' }}
                                </td>
                                <td class="py-2 pr-4 text-black">
                                    {{ ln.account?.name ?? '—' }}
                                </td>
                                <td class="py-2 pr-4 text-black capitalize">
                                    {{ ln.position }}
                                </td>
                                <td class="py-2 pr-4 text-right text-black">
                                    {{
                                        formatIDR(
                                            ln.position === 'debit'
                                                ? ln.amount
                                                : 0,
                                        )
                                    }}
                                </td>
                                <td class="py-2 pr-4 text-right text-black">
                                    {{
                                        formatIDR(
                                            ln.position === 'credit'
                                                ? ln.amount
                                                : 0,
                                        )
                                    }}
                                </td>
                                <td class="py-2 pr-0 text-black">
                                    {{ ln.description || '—' }}
                                </td>
                            </tr>
                            <tr v-if="!props.entry.lines?.length">
                                <td
                                    class="py-4 text-center text-gray-500"
                                    colspan="7"
                                >
                                    No lines
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Ringkasan Total -->
                <div class="mt-4 grid gap-2 md:ml-auto md:w-1/2">
                    <div class="grid grid-cols-[1fr_auto]">
                        <div class="text-sm text-black">Total Debit</div>
                        <div class="text-sm font-semibold text-black">
                            {{ formatIDR(totalDebit) }}
                        </div>
                    </div>
                    <div class="grid grid-cols-[1fr_auto]">
                        <div class="text-sm text-black">Total Credit</div>
                        <div class="text-sm font-semibold text-black">
                            {{ formatIDR(totalCredit) }}
                        </div>
                    </div>
                    <div
                        class="grid grid-cols-[1fr_auto] border-t pt-2"
                        :class="diff !== 0 ? 'text-red-700' : ''"
                    >
                        <div class="text-sm text-black">Difference (D - C)</div>
                        <div class="text-sm font-bold text-black">
                            {{ formatIDR(diff) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="h-5"></div>

            <!-- Catatan tambahan (optional) -->
            <div v-if="props.entry.memo && props.entry.memo.length > 0">
                <div class="w-full rounded-xl border-gray-200 bg-white p-5">
                    <div class="text-sm whitespace-pre-line text-black">
                        {{ props.entry.memo }}
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
