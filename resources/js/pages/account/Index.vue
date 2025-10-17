<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import account from '@/routes/accounts';
import { BreadcrumbItem } from '@/types';
import { AccountFilters, AccountPagination } from '@/types/account';
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
    {
        title: 'Account',
        href: account.index().url,
    },
];

const props = defineProps<{
    accounts: AccountPagination;
    filters: AccountFilters;
}>();

const items = computed(() =>
    props.accounts.data.map((item) => ({
        id: item.id,
        name: item.name,
        type: item.type,
        code: item.code,
        balanceType: item.balance_type,
        isActive: item.is_active,
        created_at: item.created_at || '-',
        updated_at: item.updated_at || '-',
    })),
);

type AccountType = 'asset' | 'liability' | 'equity' | 'revenue' | 'expense';

const toLabel = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);

const typeOptions = (
    ['asset', 'liability', 'equity', 'revenue', 'expense'] as AccountType[]
).map((v) => ({ value: v, label: toLabel(v) }));

const activeOptions = [
    { label: 'All', value: '' },
    { label: 'Active', value: 'true' },
    { label: 'Inactive', value: 'false' },
];

const filters = reactive<AccountFilters>({
    type: props.filters?.type ?? undefined,
    active:
        typeof props.filters?.active === 'boolean'
            ? String(props.filters.active)
            : (props.filters?.active ?? ''),
});

const applyFilters = () => {
    router.get(
        account.index().url,
        // hanya kirim key yang terisi agar URL bersih
        {
            ...(filters.type ? { type: filters.type } : {}),
            ...(filters.active !== '' ? { active: filters.active } : {}),
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            only: ['accounts', 'filters'],
        },
    );
};

const resetFilters = () => {
    filters.type = undefined;
    filters.active = '';
    applyFilters();
};

watch(() => [filters.type, filters.active], applyFilters);

const columnHelper = createColumnHelper<any>();

const columns = [
    columnHelper.accessor('id', {
        header: 'ID',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('code', {
        header: 'Code',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('name', {
        header: 'Name',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('type', {
        header: 'Type',
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('balanceType', {
        header: 'Balance Type',
        cell: (info) => info.getValue() as string,
    }),
    columnHelper.accessor('isActive', {
        header: 'Active',
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
            return h('div', { class: 'flex gap-4' }, [
                h(
                    'a',
                    {
                        href: account.edit(id).url,
                        class: 'text-cyan-400 hover:underline',
                    },
                    'Edit',
                ),
                h(
                    'button',
                    {
                        type: 'button',
                        class: 'text-red-600 hover:text-red-800',
                        // onClick: () => deleteUser(id),
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
    <Head title="Account" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="rounded-lg bg-white shadow-md">
            <div class="space-y-4 p-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div class="flex flex-wrap items-end gap-3">
                        <!-- Filter Type -->
                        <div class="flex flex-col">
                            <label
                                class="mb-1 text-xs font-medium text-gray-600"
                                >Type</label
                            >
                            <select
                                v-model="filters.type"
                                class="h-10 min-w-[180px] rounded border px-3 text-sm text-gray-700 focus:ring focus:ring-cyan-200 focus:outline-none"
                            >
                                <option :value="undefined">All</option>
                                <option
                                    v-for="opt in typeOptions"
                                    :key="opt.value"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
                        </div>

                        <!-- Filter Status -->
                        <div class="flex flex-col">
                            <label
                                class="mb-1 text-xs font-medium text-gray-600"
                                >Active</label
                            >
                            <select
                                v-model="filters.active"
                                class="h-10 min-w-[140px] rounded border px-3 text-sm text-gray-700 focus:ring focus:ring-cyan-200 focus:outline-none"
                            >
                                <option
                                    v-for="opt in activeOptions"
                                    :key="opt.label"
                                    :value="opt.value"
                                >
                                    {{ opt.label }}
                                </option>
                            </select>
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
                        :href="account.create().url"
                        class="inline-flex items-center gap-2 rounded bg-cyan-800 px-4 py-2 text-sm font-medium text-white hover:bg-cyan-700"
                    >
                        Add Account
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
                                    colspan="8"
                                    class="border-b px-4 py-8 text-center text-sm text-gray-500"
                                >
                                    No account data found
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
                                    class="border-b px-4 py-2 text-sm text-gray-600 capitalize"
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
                    <Pagination :pagination="props.accounts" class="p-4" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
