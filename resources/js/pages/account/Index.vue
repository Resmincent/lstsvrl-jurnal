<script setup lang="ts">
import Pagination from '@/components/Pagination.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import account from '@/routes/accounts';
import { BreadcrumbItem } from '@/types';
import { AccountFilters, AccountPagination } from '@/types/account';
import { Head, Link } from '@inertiajs/vue3';
import {
    createColumnHelper,
    FlexRender,
    getCoreRowModel,
    useVueTable,
} from '@tanstack/vue-table';
import { computed, h } from 'vue';

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
        balanceType: item.balance_type,
        isActive: item.is_active,
    })),
);

const columnHelper = createColumnHelper<any>();

const columns = [
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
        cell: (info) => info.getValue(),
    }),
    columnHelper.accessor('isActive', {
        header: 'Active',
        cell: (info) => (info.getValue() ? 'Yes' : 'No'),
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
                <div class="flex items-center justify-between">
                    <!-- <input
                        v-model="search"
                        type="text"
                        placeholder="Search users..."
                        class="h-10 w-64 rounded-md border px-3 py-2 text-black focus:ring focus:ring-cyan-200 focus:outline-none"
                    /> -->

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
                                    colspan="6"
                                    class="border-b px-4 py-8 text-center text-sm text-gray-500"
                                >
                                    No user data found
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
                    <Pagination :pagination="props.accounts" class="p-4" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
