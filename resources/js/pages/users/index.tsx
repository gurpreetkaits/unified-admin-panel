import { Badge } from '@/components/ui/badge';
import AppLayout from '@/layouts/app-layout';
import { index as usersIndex } from '@/actions/App/Http/Controllers/ProjectUserController';
import { create as createProject } from '@/actions/App/Http/Controllers/ProjectController';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Database, AlertCircle } from 'lucide-react';
import { Button } from '@/components/ui/button';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: usersIndex().url,
    },
];

interface PaginatedUsers {
    data: Record<string, unknown>[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

interface Props {
    users: PaginatedUsers;
    columns: string[];
    hasDatabase: boolean;
}

function formatValue(value: unknown): string {
    if (value === null || value === undefined) {
        return '-';
    }
    if (typeof value === 'boolean') {
        return value ? 'Yes' : 'No';
    }
    if (typeof value === 'object') {
        return JSON.stringify(value);
    }
    // Check if it's a date string
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
        return new Date(value).toLocaleDateString();
    }
    return String(value);
}

function getStatusBadge(status: unknown) {
    if (typeof status !== 'string') return null;

    const statusLower = status.toLowerCase();
    if (statusLower === 'active' || statusLower === 'verified') {
        return <Badge variant="default">{status}</Badge>;
    }
    if (statusLower === 'inactive' || statusLower === 'pending') {
        return <Badge variant="secondary">{status}</Badge>;
    }
    if (statusLower === 'suspended' || statusLower === 'banned') {
        return <Badge variant="destructive">{status}</Badge>;
    }
    return <Badge variant="outline">{status}</Badge>;
}

// Columns to display (prioritized order)
const priorityColumns = ['id', 'name', 'email', 'status', 'created_at'];

export default function UsersIndex({ users, columns, hasDatabase }: Props) {
    const { currentProject } = usePage<SharedData>().props;

    // Sort columns: priority columns first, then alphabetically
    const displayColumns = columns.length > 0
        ? [
            ...priorityColumns.filter(col => columns.includes(col)),
            ...columns.filter(col => !priorityColumns.includes(col)).sort()
          ].slice(0, 6) // Limit to 6 columns for readability
        : priorityColumns;

    if (!hasDatabase) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Users" />
                <div className="flex h-full flex-1 flex-col items-center justify-center gap-4 p-4">
                    <div className="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border">
                        <Database className="mx-auto mb-4 size-12 text-muted-foreground" />
                        <h2 className="mb-2 text-xl font-semibold">No Database Connected</h2>
                        <p className="mb-4 text-muted-foreground">
                            {currentProject?.name} doesn't have a database connection configured.
                        </p>
                        <Button asChild>
                            <Link href={createProject().url}>Configure Database</Link>
                        </Button>
                    </div>
                </div>
            </AppLayout>
        );
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Users</h1>
                        <p className="text-muted-foreground">
                            Users from {currentProject?.name}
                        </p>
                    </div>
                    <div className="text-sm text-muted-foreground">
                        {users.total} total users
                    </div>
                </div>

                <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div className="overflow-x-auto">
                        <table className="w-full">
                            <thead>
                                <tr className="border-b border-sidebar-border/70 dark:border-sidebar-border">
                                    {displayColumns.map((column) => (
                                        <th
                                            key={column}
                                            className="px-4 py-3 text-left text-sm font-medium capitalize text-muted-foreground"
                                        >
                                            {column.replace(/_/g, ' ')}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody>
                                {users.data.length === 0 ? (
                                    <tr>
                                        <td
                                            colSpan={displayColumns.length}
                                            className="px-4 py-8 text-center text-muted-foreground"
                                        >
                                            No users found.
                                        </td>
                                    </tr>
                                ) : (
                                    users.data.map((user, index) => (
                                        <tr
                                            key={user.id as number ?? index}
                                            className="border-b border-sidebar-border/70 last:border-0 dark:border-sidebar-border"
                                        >
                                            {displayColumns.map((column) => (
                                                <td key={column} className="px-4 py-3">
                                                    {column === 'status' ? (
                                                        getStatusBadge(user[column])
                                                    ) : column === 'email_verified_at' ? (
                                                        user[column] ? (
                                                            <Badge variant="outline" className="text-green-600">
                                                                Verified
                                                            </Badge>
                                                        ) : (
                                                            <Badge variant="outline" className="text-yellow-600">
                                                                Pending
                                                            </Badge>
                                                        )
                                                    ) : column === 'name' || column === 'id' ? (
                                                        <span className="font-medium">
                                                            {formatValue(user[column])}
                                                        </span>
                                                    ) : (
                                                        <span className="text-muted-foreground">
                                                            {formatValue(user[column])}
                                                        </span>
                                                    )}
                                                </td>
                                            ))}
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>

                    {users.last_page > 1 && (
                        <div className="flex items-center justify-between border-t border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                            <div className="text-sm text-muted-foreground">
                                Showing {users.from} to {users.to} of {users.total} results
                            </div>
                            <div className="flex gap-2">
                                {users.links.map((link, index) => (
                                    <Link
                                        key={index}
                                        href={link.url ?? '#'}
                                        className={`rounded px-3 py-1 text-sm ${
                                            link.active
                                                ? 'bg-primary text-primary-foreground'
                                                : link.url
                                                  ? 'hover:bg-muted'
                                                  : 'cursor-not-allowed opacity-50'
                                        }`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ))}
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
