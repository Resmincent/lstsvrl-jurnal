<script setup lang="ts">
import HeadingSmall from '@/components/HeadingSmall.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import accounts from '@/routes/accounts';
import type { BreadcrumbItem } from '@/types';
import type {
    AccountType,
    BalanceType,
    Account as BaseAccount,
} from '@/types/account';
import { Head, Link, useForm } from '@inertiajs/vue3';

type AccountForEdit = BaseAccount & { code: string };

const props = defineProps<{
    account: AccountForEdit;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Account', href: accounts.index().url },
    { title: 'Edit Account', href: accounts.edit(props.account.id).url },
];

// dropdown options
const typeOptions: AccountType[] = [
    'asset',
    'liability',
    'equity',
    'revenue',
    'expense',
];
const balanceOptions: BalanceType[] = ['debit', 'credit'];
const toLabel = (s: string) => s.charAt(0).toUpperCase() + s.slice(1);

// form state (prefill dari props)
const form = useForm({
    code: props.account.code ?? '',
    name: props.account.name ?? '',
    type: (props.account.type ?? '') as AccountType | '',
    balance_type: (props.account.balance_type ?? '') as BalanceType | '',
    is_active: !!props.account.is_active,
});

function submit() {
    form.put(accounts.update(props.account.id).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="`Edit Account - ${props.account.code ?? ''}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto h-full w-2/3 items-center justify-center p-5">
            <form @submit.prevent="submit">
                <!-- Header -->
                <div
                    class="w-full rounded-xl border-gray-200 bg-white shadow-md"
                >
                    <div class="border-b px-6 py-4">
                        <HeadingSmall
                            title="Edit Account"
                            :description="`Update account ${props.account.code} - ${props.account.name}`"
                            class="text-gray-800"
                        />
                    </div>
                </div>

                <div class="h-5"></div>

                <div
                    class="w-full rounded-xl border-gray-200 bg-white px-6 py-4 shadow-md"
                >
                    <HeadingSmall
                        title="Account Information"
                        description="Edit the account details below"
                        class="text-gray-800"
                    />

                    <div class="py-3">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Code</label
                        >
                        <input
                            v-model="form.code"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-600 focus:ring focus:ring-cyan-200"
                            placeholder="e.g. 101"
                            @input="form.code = (form.code || '').toUpperCase()"
                        />
                        <div
                            v-if="form.errors.code"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.code }}
                        </div>
                    </div>

                    <div class="py-3">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Name</label
                        >
                        <input
                            v-model="form.name"
                            type="text"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-600 focus:ring focus:ring-cyan-200"
                            placeholder="e.g. Cash"
                        />
                        <div
                            v-if="form.errors.name"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="py-3">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Type</label
                        >
                        <select
                            v-model="form.type"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-600 focus:ring focus:ring-cyan-200"
                        >
                            <option disabled value="">Select type</option>
                            <option
                                v-for="t in typeOptions"
                                :key="t"
                                :value="t"
                            >
                                {{ toLabel(t) }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.type"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.type }}
                        </div>
                    </div>

                    <div class="py-3">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Balance Type</label
                        >
                        <select
                            v-model="form.balance_type"
                            class="w-full rounded-md border px-3 py-2 text-black focus:border-cyan-600 focus:ring focus:ring-cyan-200"
                        >
                            <option disabled value="">
                                Select balance type
                            </option>
                            <option
                                v-for="b in balanceOptions"
                                :key="b"
                                :value="b"
                            >
                                {{ toLabel(b) }}
                            </option>
                        </select>
                        <div
                            v-if="form.errors.balance_type"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.balance_type }}
                        </div>
                    </div>

                    <!-- Active -->
                    <div class="py-3">
                        <label
                            class="mb-1 block text-sm font-medium text-gray-700"
                            >Active</label
                        >
                        <label class="inline-flex items-center gap-2">
                            <input
                                v-model="form.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-cyan-600 focus:ring focus:ring-cyan-200"
                            />
                            <span class="text-gray-700"
                                >This account is active</span
                            >
                        </label>
                        <div
                            v-if="form.errors.is_active"
                            class="mt-1 text-sm text-red-600"
                        >
                            {{ form.errors.is_active }}
                        </div>
                    </div>
                </div>

                <div class="h-5"></div>

                <div class="flex justify-end gap-2">
                    <Link
                        :href="accounts.index().url"
                        class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                    >
                        Cancel
                    </Link>

                    <button
                        type="submit"
                        class="inline-flex justify-center rounded-md border border-transparent bg-cyan-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-cyan-700 focus:ring-2 focus:ring-cyan-500 focus:ring-offset-2 focus:outline-none"
                        :disabled="form.processing"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
