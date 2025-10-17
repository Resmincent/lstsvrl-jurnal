<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';
import type { Summary } from '@/types/dashboard';
import { Head } from '@inertiajs/vue3';

const props = defineProps<{
    summary: Summary;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: dashboard().url },
];

// Format rupiah
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
            <div class="grid auto-rows-min gap-4 md:grid-cols-5">
                <!-- Cash -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
                >
                    <div class="text-xs text-black dark:text-black">Cash</div>
                    <div
                        class="text-l mt-1 font-semibold text-black dark:text-black"
                    >
                        {{ cur(props.summary.cash) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-black uppercase dark:text-black"
                    >
                        Saldo akhir
                    </div>
                </div>

                <!-- Bank -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
                >
                    <div class="text-xs text-black dark:text-black">Bank</div>
                    <div
                        class="text-l mt-1 font-semibold text-black dark:text-black"
                    >
                        {{ cur(props.summary.bank) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-black uppercase dark:text-black"
                    >
                        Saldo akhir
                    </div>
                </div>

                <!-- Accounts Receivable -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
                >
                    <div class="text-xs text-black dark:text-black">
                        Accounts Receivable
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-black dark:text-black"
                    >
                        {{ cur(props.summary.accounts_receivable) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-black uppercase dark:text-black"
                    >
                        Saldo akhir
                    </div>
                </div>

                <!-- Equipment -->
                <div
                    class="relative overflow-hidden rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
                >
                    <div class="text-xs text-black dark:text-black">
                        Equipment
                    </div>
                    <div
                        class="text-l mt-1 font-semibold text-black dark:text-black"
                    >
                        {{ cur(props.summary.equipment) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-black uppercase dark:text-black"
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
                    class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
                >
                    <div class="text-xs text-black dark:text-black">
                        Revenue (All)
                    </div>
                    <div class="text-l bold mt-1 text-black">
                        {{ cur(props.summary.revenue) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase"
                    >
                        Akumulasi kredit
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
                >
                    <div class="text-xs text-gray-500">Expense (All)</div>
                    <div class="text-l mt-1 font-semibold text-black">
                        {{ cur(props.summary.expense) }}
                    </div>
                    <div
                        class="mt-1 text-[10px] tracking-wide text-gray-500 uppercase"
                    >
                        Akumulasi debit
                    </div>
                </div>

                <div
                    class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
                >
                    <div class="text-xs text-gray-500">
                        Assets / Liabilities / Equity
                    </div>
                    <div class="mt-2 grid grid-cols-3 gap-2 text-sm">
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase">
                                Assets
                            </div>
                            <div class="font-semibold text-black">
                                {{ cur(props.summary.assets) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase">
                                Liabilities
                            </div>
                            <div class="font-semibold text-black">
                                {{ cur(props.summary.liabilities) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-[10px] text-gray-500 uppercase">
                                Equity
                            </div>
                            <div class="font-semibold text-black">
                                {{ cur(props.summary.equity) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ringkasan Akun -->
            <div
                class="relative min-h-[40vh] flex-1 rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border"
            >
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
                                <td class="px-3 py-2 text-gray-800">Cash</td>
                                <td class="px-3 py-2 text-right text-black">
                                    {{ cur(props.summary.cash) }}
                                </td>
                                <td class="px-3 py-2 text-gray-600">
                                    Kas (111)
                                </td>
                            </tr>
                            <tr class="border-b dark:border-gray-800">
                                <td class="px-3 py-2 text-gray-800">Bank</td>
                                <td class="px-3 py-2 text-right text-black">
                                    {{ cur(props.summary.bank) }}
                                </td>
                                <td class="px-3 py-2 text-gray-600">
                                    Bank (112)
                                </td>
                            </tr>
                            <tr class="border-b dark:border-gray-800">
                                <td class="px-3 py-2 text-gray-800">
                                    Accounts Receivable
                                </td>
                                <td class="px-3 py-2 text-right text-black">
                                    {{ cur(props.summary.accounts_receivable) }}
                                </td>
                                <td class="px-3 py-2 text-gray-600">
                                    Piutang Usaha (113)
                                </td>
                            </tr>
                            <tr class="border-b dark:border-gray-800">
                                <td class="px-3 py-2 text-black">Equipment</td>
                                <td class="px-3 py-2 text-right text-black">
                                    {{ cur(props.summary.equipment) }}
                                </td>
                                <td class="px-3 py-2 text-gray-600">
                                    Peralatan (114)
                                </td>
                            </tr>
                            <tr>
                                <td class="px-3 py-2 text-black">Net Income</td>
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
                                <td class="px-3 py-2 text-gray-600">
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
