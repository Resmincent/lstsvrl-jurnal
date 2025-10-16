import { Account, BalanceType } from './account';
import { PaginationLink } from './pagination';

export type LinePosition = BalanceType;

export interface JournalEntry {
    id: number;
    number: string;
    date: string;
    memo: string;
    is_posted: boolean;
    created_at: string;
    updated_at: string;

    lines?: JournalLine[];
}

export interface JournalLine {
    id: number;
    journal_entry_id: number;
    account_id: number;
    position: LinePosition;
    amount: number | string;
    memo: string;
    created_at: string;
    updated_at: string;

    account?: Account;
}

export interface JournalEntrySummary {
    id: number;
    journal_entry_id: number;
    account_id: number;
    position: LinePosition;
    amount: number | string;
    memo: string;
}

export interface JournalLineWithEntry extends JournalLine {
    entry?: JournalEntry;
}

export interface JournalLineInput {
    account_id: number;
    position: LinePosition;
    amount: number | string;
    description?: string | null;
    line_number?: number;
}

export interface JournalEntryCreatePayload {
    number: string;
    date: string;
    memo: string;
    lines: JournalLineInput[];
}

export interface JournalEntryUpdatePayload extends JournalEntryCreatePayload {
    is_posted?: boolean;
}

export interface JournalFilters {
    q?: string;
    posted?: boolean | string;
    start_date?: IsoDate;
    end_date?: IsoDate;
}

export interface JournalEntryPagination {
    current_page: number;
    data: JournalEntry[];
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
    links: PaginationLink[];
}
