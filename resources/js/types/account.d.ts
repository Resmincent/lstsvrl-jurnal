import { JournalLineWithEntry } from './journalEntry';
import { PaginationLink } from './pagination';

export type AccountType =
    | 'asset'
    | 'liability'
    | 'equity'
    | 'revenue'
    | 'expense';

export type BalanceType = 'debit' | 'credit';

export interface Account {
    id: number;
    name: string;
    type: AccountType;
    balance_type: BalanceType;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    code: string;

    postedJournalLines?: JournalLineWithEntry[];
}

export interface AccountCreatePayload {
    code: string;
    name: string;
    type: AccountType | '';
    balance_type: BalanceType | '';
    is_active?: boolean;
}

export interface AccountUpdatePayload {
    code?: string;
    name?: string;
    type?: AccountType;
    balance_type?: BalanceType;
    is_active?: boolean;
}
export interface AccountFilters {
    type?: AccountType;
    active?: boolean | string;
}

export interface AccountPagination {
    current_page: number;
    data: Account[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
