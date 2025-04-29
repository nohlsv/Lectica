import type { PageProps } from '@inertiajs/core';
import type { LucideIcon } from 'lucide-vue-next';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface PaginatedData<T> {
    data: T[];
    current_page: number;
    last_page: number;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    from: number;
    to: number;
    total: number;
}

export interface SharedData extends PageProps {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}
export interface File {
    id: number;
    name: string;
    path: string;
    content: string;
    file_hash: string;
    user: User;
    created_at: string;
    updated_at: string;
}


export interface File {
    id: number;
    name: string;
    path: string;
    content: string;
    file_hash: string;
    user_id: number;
    created_at: string;
    updated_at: string;
    tags?: Tag[];
}

export interface Tag {
    id: number;
    name: string;
}

export type BreadcrumbItemType = BreadcrumbItem;
