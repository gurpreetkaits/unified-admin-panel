import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index as tablesIndex, show as showTable, pin, unpin } from '@/actions/App/Http/Controllers/TableController';
import { create as createProject } from '@/actions/App/Http/Controllers/ProjectController';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import { Database, Pin, PinOff, Table2, ExternalLink } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tables',
        href: tablesIndex().url,
    },
];

interface TableInfo {
    name: string;
    row_count: number;
    is_pinned: boolean;
}

interface Props {
    tables: TableInfo[];
    hasDatabase: boolean;
    pinnedTables: string[];
}

export default function TablesIndex({ tables, hasDatabase, pinnedTables: initialPinnedTables }: Props) {
    const { currentProject } = usePage<SharedData>().props;
    const [pinnedTables, setPinnedTables] = useState<string[]>(initialPinnedTables);
    const [pinning, setPinning] = useState<string | null>(null);

    const getCsrfToken = (): string => {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    };

    const handleTogglePin = async (tableName: string, isPinned: boolean) => {
        setPinning(tableName);

        try {
            const url = isPinned ? unpin.url(tableName) : pin.url(tableName);
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
            });

            const data = await response.json();

            if (data.success) {
                setPinnedTables(data.pinned_tables);
                router.reload({ only: ['currentProject'] });
            }
        } catch (error) {
            console.error('Failed to toggle pin:', error);
        } finally {
            setPinning(null);
        }
    };

    if (!hasDatabase) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Tables" />
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
            <Head title="Tables" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Database Tables</h1>
                        <p className="text-muted-foreground">
                            Browse and pin tables from {currentProject?.name}
                        </p>
                    </div>
                    <div className="text-sm text-muted-foreground">
                        {tables.length} tables
                    </div>
                </div>

                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {tables.length === 0 ? (
                        <div className="col-span-full rounded-xl border border-sidebar-border/70 p-8 text-center text-muted-foreground dark:border-sidebar-border">
                            No tables found in database.
                        </div>
                    ) : (
                        tables.map((table) => {
                            const isPinned = pinnedTables.includes(table.name);
                            return (
                                <div
                                    key={table.name}
                                    className="group rounded-xl border border-sidebar-border/70 p-4 transition-colors hover:bg-muted/50 dark:border-sidebar-border"
                                >
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="flex items-center gap-3">
                                            <Table2 className="size-5 text-muted-foreground" />
                                            <div>
                                                <h3 className="font-medium">{table.name}</h3>
                                                <p className="text-sm text-muted-foreground">
                                                    {table.row_count.toLocaleString()} rows
                                                </p>
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-1">
                                            {isPinned && (
                                                <Badge variant="secondary" className="gap-1">
                                                    <Pin className="size-3" />
                                                    Pinned
                                                </Badge>
                                            )}
                                        </div>
                                    </div>
                                    <div className="mt-4 flex items-center gap-2">
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            className="flex-1"
                                            asChild
                                        >
                                            <Link href={showTable.url(table.name)}>
                                                <ExternalLink className="mr-2 size-4" />
                                                View Data
                                            </Link>
                                        </Button>
                                        <Button
                                            variant={isPinned ? 'secondary' : 'outline'}
                                            size="sm"
                                            onClick={() => handleTogglePin(table.name, isPinned)}
                                            disabled={pinning === table.name}
                                        >
                                            {isPinned ? (
                                                <PinOff className="size-4" />
                                            ) : (
                                                <Pin className="size-4" />
                                            )}
                                        </Button>
                                    </div>
                                </div>
                            );
                        })
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
