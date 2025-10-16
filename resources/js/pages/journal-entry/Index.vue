<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import journalEntry from '@/routes/journal-entries';
import { BreadcrumbItem } from '@/types';
import type {
    JournalEntryPagination,
    JournalFilters,
} from '@/types/journalEntry';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import moment from 'moment';
import { computed, h, reactive, watch } from 'vue';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Journal Entry', href: journalEntry.index().url },
];

const props = defineProps<{
    entries: JournalEntryPagination;
    filters: JournalFilters;
}>();

const items = computed(() =>
    props.entries.data.map((e) => ({
        id: e.id,
        number: e.number,
        date: e.date,
        memo: e.memo,
        isPosted: e.is_posted,
        created_at: e.created_at || '-',
        updated_at: e.updated_at || '-',
    })),
);

const toBoolString = (v: boolean | string | undefined) =>
    typeof v === 'boolean' ? String(v) : (v ?? '');

const postedOptions = [
    { label: 'All', value: '' },
    { label: 'Posted', value: 'true' },
    { label: 'Unposted', value: 'false' },
];

const filters = reactive<JournalFilters>({
    q: props.filters?.q ?? '',
    posted: toBoolString(props.filters?.posted),
    start_date: props.filters?.start_date,
    end_date: props.filters?.end_date,
});

const applyFilters = () => {
    router.get(
        journalEntry.index().url,
        {
            ...(filters.q ? { q: filters.q } : {}),
            ...(filters.posted !== '' ? { posted: filters.posted } : {}),
            ...(filters.start_date ? { start_date: filters.start_date } : {}),
            ...(filters.end_date ? { end_date: filters.end_date } : {}),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['entries', 'filters'],
        },
    );
};

const resetFilters = () => {
    filters.q = '';
    filters.posted = '';
    filters.start_date = undefined;
    filters.end_date = undefined;
    applyFilters();
};

watch(
    () => [filters.q, filters.posted, filters.start_date, filters.end_date],
    applyFilters,
);

const columnHelper = createColumnHelper<any>();

const columns = [
    columnHelper.accessor('number', {
        header: 'Number',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('date', {
        header: 'Date',
        cell: (info) => moment(info.getValue()).format('YYYY-MM-DD'),
    }),
    columnHelper.accessor('memo', {
        header: 'Memo',
        cell: (info) => info.getValue() || '-',
    }),
    columnHelper.accessor('isPosted', {
        header: 'Posted',
        cell: (info) => (info.getValue() ? 'Yes' : 'No'),
    }),
    columnHelper.accessor('created_at', {
        header: 'Created',
        cell: (info) => {
            const value = info.getValue();
            return value
                ? moment(value).format('YYYY-MM-DD HH:mm') + ' WIB'
                : '-';
        },
    }),
    columnHelper.accessor('updated_at', {
        header: 'Updated',
        cell: (info) => {
            const value = info.getValue();
            return value
                ? moment(value).format('YYYY-MM-DD HH:mm') + ' WIB'
                : '-';
        },
    }),
    columnHelper.display({
        id: 'actions',
        header: 'Actions',
        cell: (info) => {
            const id = info.row.original.id;
            const isPosted = info.row.original.isPosted as boolean;
            return h('div', { class: 'flex gap-4' }, [
                h(
                    'a',
                    {
                        href: journalEntry.edit(id).url,
                        class: 'text-cyan-400 hover:underline',
                    },
                    'Edit',
                ),
                // Post action
                h(
                    'button',
                    {
                        type: 'button',
                        class: 'text-emerald-700 hover:text-emerald-900 disabled:text-gray-400',
                        disabled: isPosted,
                        onClick: () => {
                            if (isPosted) return;
                            if (!confirm('Post this journal entry?')) return;
                            router.post(
                                journalEntry.post(id).url,
                                {},
                                {
                                    preserveScroll: true,
                                },
                            );
                        },
                    },
                    'Post',
                ),
                // Delete action
                h(
                    'button',
                    {
                        type: 'button',
                        class: 'text-red-600 hover:text-red-800',
                        onClick: () => {
                            if (!confirm('Delete this journal entry?')) return;
                            router.delete(journalEntry.destroy(id).url, {
                                preserveScroll: true,
                            });
                        },
                    },
                    'Delete',
                ),
            ]);
        },
    }),
];

const tableData = useVueTable({
    data: items,
    columns: columns,
    getCoreRowModel: getCoreRowModel(),
});
</script>

<template>
    <Head title="Journal Entry" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <!-- Search input -->
                        <div class="flex flex-col">
                            <label
                                class="mb-1 text-xs font-medium text-gray-600"
                                >Search</label
                            >
                            <input
                                v-model="filters.q"
                                type="text"
                                placeholder="Number or memo..."
                                class="h-10 w-[240px] rounded border px-3 text-sm text-gray-700 focus:ring focus:ring-cyan-200 focus:outline-none"
                            />
                        </div>

                        <!-- Posted dropdown -->
                        <div class="flex flex-col">
                            <label
                                class="mb-1 text-xs font-medium text-gray-600"
                                >Posted</label
                            >
                            <select
                                v-model="filters.posted as any"
                                class="h-10 min-w-[140px] rounded border px-3 text-sm text-gray-700 focus:ring focus:ring-cyan-200 focus:outline-none"
                            >
                                <option
                                    v-for="opt in postedOptions"
                                    :key="opt.label"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Date range -->
                        <div class="flex flex-col">
                            <label
                                class="mb-1 text-xs font-medium text-gray-600"
                                >Start Date</label
                            >
                            <input
                                v-model="filters.start_date as any"
                                type="date"
                                class="h-10 rounded border px-3 text-sm text-gray-700 focus:ring focus:ring-cyan-200 focus:outline-none"
                            />
                        </div>
                        <div class="flex flex-col">
                            <label
                                class="mb-1 text-xs font-medium text-gray-600"
                                >End Date</label
                            >
                            <input
                                v-model="filters.end_date as any"
                                type="date"
                                class="h-10 rounded border px-3 text-sm text-gray-700 focus:ring focus:ring-cyan-200 focus:outline-none"
                            />
                        </div>

                        <!-- Reset -->
                        <button
                            type="button"
                            @click="resetFilters"
                            class="h-10 rounded bg-gray-100 px-3 text-sm font-medium text-gray-800 hover:bg-gray-200"
                            title="Reset filters"
                        >
                            Reset
                        </button>
                    </div>

                    <Link
                        :href="journalEntry.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Journal Entry
                    </Link>
                </div>

                <div class="overflow-x-auto rounded-lg border">
                    <table class="min-w-full border-collapse bg-white">
                        <thead class="bg-gray-100">
                            <tr
                                v-for="headerGroup in tableData.getHeaderGroups()"
                                :key="headerGroup.id"
                            >
                                <th
                                    v-for="header in headerGroup.headers"
                                    :key="header.id"
                                    class="border-b px-4 py-2 text-left text-sm font-semibold text-gray-700"
                                >
                                    <component
                                        :is="FlexRender"
                                        :render="header.column.columnDef.header"
                                        :props="header.getContext()"
                                    />
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr
                                v-if="tableData.getRowModel().rows.length === 0"
                            >
                                <td
                                    colspan="7"
                                    class="border-b px-4 py-8 text-center text-sm text-gray-500"
                                >
                                    No journal entries found
                                </td>
                            </tr>
                            <tr
                                v-for="row in tableData.getRowModel().rows"
                                :key="row.id"
                                class="hover:bg-gray-50"
                            >
                                <td
                                    v-for="cell in row.getVisibleCells()"
                                    :key="cell.id"
                                    class="border-b px-4 py-2 text-sm text-gray-600"
                                >
                                    <component
                                        :is="FlexRender"
                                        :render="cell.column.columnDef.cell"
                                        :props="cell.getContext()"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :pagination="props.entries" class="p-4" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
