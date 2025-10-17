<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { reactive } from 'vue';

const props = defineProps<{
    summary: {
        // akun utama
        cash: string;
        bank: string;
        accounts_receivable: string;
        equipment: string;

        // agregat tipe
        assets: string;
        liabilities: string;
        equity: string;

        // kinerja
        revenue: string;
        expense: string;
        net_income: string;

        period_label?: string;
    };
    filters: {
        start_date?: string | null;
        end_date?: string | null;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

// ===== Filters (Apply/Reset) =====
const filters = reactive({
    start_date: props.filters?.start_date ?? '',
    end_date: props.filters?.end_date ?? '',
});

const applyFilters = () => {
    if (
        filters.start_date &&
        filters.end_date &&
        filters.start_date > filters.end_date
    ) {
        const t = filters.start_date;
        filters.start_date = filters.end_date;
        filters.end_date = t;
    }
    router.get(
        dashboard().url,
        {
            ...(filters.start_date ? { start_date: filters.start_date } : {}),
            ...(filters.end_date ? { end_date: filters.end_date } : {}),
        },
        { preserveState: true, replace: true, only: ['summary', 'filters'] },
    );
};

const resetFilters = () => {
    filters.start_date = '';
    filters.end_date = '';
    applyFilters();
};

// ===== Utils =====
const cur = (n: string) =>
    Number(n || 0).toLocaleString('id-ID', {
        style: 'currency',
        currency: 'IDR',
    });
const isPositive = (n: string) => Number(n || 0) >= 0;
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div
            class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
        >
            <!-- Header & Filters -->
            <div
                class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
            >
                <div class="flex flex-wrap items-end gap-4">
                    <div class="mr-auto">
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                            Periode
                        </div>
                        <div
                            class="text-lg font-semibold text-gray-900 dark:text-gray-100"
                        >
                            {{ props.summary.period_label || 'Semua periode' }}
                        </div>
                    </div>

                    <div class="flex flex-col">
                        <label
                            class="mb-1 text-xs font-medium text-gray-600 dark:text-gray-300"
                            >Start</label
                        >
                        <input
                            v-model="filters.start_date"
                            type="date"
                            class="h-10 rounded border px-3 text-sm text-gray-800 focus:ring focus:ring-cyan-200 focus:outline-none dark:border-gray-700 dark:bg-transparent dark:text-gray-100"
                        />
                    </div>
                    <div class="flex flex-col">
                        <label
                            class="mb-1 text-xs font-medium text-gray-600 dark:text-gray-300"
                            >End</label
                        >
                        <input
                            v-model="filters.end_date"
                            type="date"
                            class="h-10 rounded border px-3 text-sm text-gray-800 focus:ring focus:ring-cyan-200 focus:outline-none dark:border-gray-700 dark:bg-transparent dark:text-gray-100"
                        />
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="applyFilters"
                            class="h-10 rounded bg-cyan-600 px-4 text-sm font-medium text-white hover:bg-cyan-700"
                        >
                            Apply
                        </button>
                        <button
                            type="button"
                            @click="resetFilters"
                            class="h-10 rounded border px-4 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-100 dark:hover:bg-[#0E1626]"
                            title="Reset filters"
                        >
                            Reset
                        </button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-5">
                <!-- Cash -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
                >
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Cash
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-gray-900 dark:text-gray-100"
                    >
                        {{ cur(props.summary.cash) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        Saldo akhir
                    </div>
                </div>

                <!-- Bank -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
                >
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Bank
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-gray-900 dark:text-gray-100"
                    >
                        {{ cur(props.summary.bank) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        Saldo akhir
                    </div>
                </div>

                <!-- Accounts Receivable -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
                >
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Accounts Receivable
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-gray-900 dark:text-gray-100"
                    >
                        {{ cur(props.summary.accounts_receivable) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        Saldo akhir
                    </div>
                </div>

                <!-- Equipment -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
                >
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Equipment
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-gray-900 dark:text-gray-100"
                    >
                        {{ cur(props.summary.equipment) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        Saldo akhir
                    </div>
                </div>

                <!-- Net Income -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 p-4"
                    :class="
                        isPositive(props.summary.net_income)
                            ? 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-[#0B1F17]'
                            : 'border-red-200 bg-red-50 dark:border-red-800 dark:bg-[#210F12]'
                    "
                >
                    <div
                        class="text-xs"
                        :class="
                            isPositive(props.summary.net_income)
                                ? 'text-green-700 dark:text-green-300'
                                : 'text-red-700 dark:text-red-300'
                        "
                    >
                        Net Income
                    </div>
                    <div
                        class="text-l mt-1 font-semibold"
                        :class="
                            isPositive(props.summary.net_income)
                                ? 'text-green-900 dark:text-green-200'
                                : 'text-red-900 dark:text-red-200'
                        "
                    >
                        {{ cur(props.summary.net_income) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide uppercase"
                        :class="
                            isPositive(props.summary.net_income)
                                ? 'text-green-700 dark:text-green-400'
                                : 'text-red-700 dark:text-red-400'
                        "
                    >
                        Revenue - Expense
                    </div>
                </div>
            </div>

            <!-- Aggregates -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
                >
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Revenue (Periode)
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-gray-900 dark:text-gray-100"
                    >
                        {{ cur(props.summary.revenue) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        Akumulasi kredit
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
                >
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Expense (Periode)
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-gray-900 dark:text-gray-100"
                    >
                        {{ cur(props.summary.expense) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        Akumulasi debit
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
                >
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        Assets / Liabilities / Equity
                    </div>
                    <div class="mt-2 grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <div
                                class="text-[10px] text-gray-500 uppercase dark:text-gray-400"
                            >
                                Assets
                            </div>
                            <div
                                class="font-semibold text-gray-900 dark:text-gray-100"
                            >
                                {{ cur(props.summary.assets) }}
                            </div>
                        </div>
                        <div>
                            <div
                                class="text-[10px] text-gray-500 uppercase dark:text-gray-400"
                            >
                                Liabilities
                            </div>
                            <div
                                class="font-semibold text-gray-900 dark:text-gray-100"
                            >
                                {{ cur(props.summary.liabilities) }}
                            </div>
                        </div>
                        <div>
                            <div
                                class="text-[10px] text-gray-500 uppercase dark:text-gray-400"
                            >
                                Equity
                            </div>
                            <div
                                class="font-semibold text-gray-900 dark:text-gray-100"
                            >
                                {{ cur(props.summary.equity) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail ringkas -->
            <div
                class="relative min-h-[40vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-[#0B1220]"
            >
                <div class="mb-3 flex items-center justify-between">
                    <div
                        class="text-sm font-semibold text-gray-900 dark:text-gray-100"
                    >
                        Ringkasan Akun Utama
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">
                        {{ props.summary.period_label || 'Semua periode' }}
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead
                            class="bg-gray-100 text-gray-700 dark:bg-[#0E1626] dark:text-gray-200"
                        >
                            <tr>
                                <th class="px-3 py-2 text-left">Account</th>
                                <th class="px-3 py-2 text-right">
                                    Ending Balance
                                </th>
                                <th class="px-3 py-2 text-left">Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b dark:border-gray-800">
                                <td
                                    class="px-3 py-2 text-gray-800 dark:text-gray-100"
                                >
                                    Cash
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-gray-900 dark:text-gray-100"
                                >
                                    {{ cur(props.summary.cash) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-gray-600 dark:text-gray-300"
                                >
                                    Kas (111)
                                </td>
                            </tr>

                            <tr class="border-b dark:border-gray-800">
                                <td
                                    class="px-3 py-2 text-gray-800 dark:text-gray-100"
                                >
                                    Bank
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-gray-900 dark:text-gray-100"
                                >
                                    {{ cur(props.summary.bank) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-gray-600 dark:text-gray-300"
                                >
                                    Bank (112)
                                </td>
                            </tr>

                            <tr class="border-b dark:border-gray-800">
                                <td
                                    class="px-3 py-2 text-gray-800 dark:text-gray-100"
                                >
                                    Accounts Receivable
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-gray-900 dark:text-gray-100"
                                >
                                    {{ cur(props.summary.accounts_receivable) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-gray-600 dark:text-gray-300"
                                >
                                    Piutang Usaha (113)
                                </td>
                            </tr>

                            <tr class="border-b dark:border-gray-800">
                                <td
                                    class="px-3 py-2 text-gray-800 dark:text-gray-100"
                                >
                                    Equipment
                                </td>
                                <td
                                    class="px-3 py-2 text-right text-gray-900 dark:text-gray-100"
                                >
                                    {{ cur(props.summary.equipment) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-gray-600 dark:text-gray-300"
                                >
                                    Peralatan (114)
                                </td>
                            </tr>

                            <tr>
                                <td
                                    class="px-3 py-2 text-gray-800 dark:text-gray-100"
                                >
                                    Net Income
                                </td>
                                <td
                                    class="px-3 py-2 text-right font-medium"
                                    :class="
                                        isPositive(props.summary.net_income)
                                            ? 'text-green-700 dark:text-green-300'
                                            : 'text-red-700 dark:text-red-300'
                                    "
                                >
                                    {{ cur(props.summary.net_income) }}
                                </td>
                                <td
                                    class="px-3 py-2 text-gray-600 dark:text-gray-300"
                                >
                                    Selisih pendapatan dan beban (411–412 vs
                                    511–514)
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
