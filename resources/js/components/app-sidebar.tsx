import { NavUser } from '@/components/nav-user';
import { ProjectSwitcher } from '@/components/project-switcher';
import { Input } from '@/components/ui/input';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarResizeHandle,
} from '@/components/ui/sidebar';
import { Switch } from '@/components/ui/switch';
import { Label } from '@/components/ui/label';
import { index as tablesIndex } from '@/actions/App/Http/Controllers/TableController';
import { type SharedData } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { Pin, Search, Table2 } from 'lucide-react';
import { useMemo, useState } from 'react';

export function AppSidebar() {
    const { currentProject, databaseTables } = usePage<SharedData>().props;
    const pinnedTables = currentProject?.pinned_tables ?? [];

    const [pinnedOnly, setPinnedOnly] = useState(false);
    const [searchQuery, setSearchQuery] = useState('');

    const tablesToShow = useMemo(() => {
        const tables = pinnedOnly ? pinnedTables : databaseTables;

        if (!searchQuery.trim()) {
            return tables;
        }

        const query = searchQuery.toLowerCase();
        return tables.filter((table) => table.toLowerCase().includes(query));
    }, [pinnedOnly, pinnedTables, databaseTables, searchQuery]);

    const handleTableClick = (
        e: React.MouseEvent,
        tableName: string,
    ) => {
        e.preventDefault();

        // Check if we're already on the tables page
        const isOnTablesPage =
            window.location.pathname === '/tables' ||
            window.location.pathname.startsWith('/tables');

        if (isOnTablesPage) {
            // Preserve state to keep existing tabs open
            router.get(
                tablesIndex().url,
                { tab: tableName },
                { preserveState: true, preserveScroll: true },
            );
        } else {
            // Navigate to tables page with the tab parameter
            router.visit(`${tablesIndex().url}?tab=${encodeURIComponent(tableName)}`);
        }
    };

    const hasDatabase = databaseTables.length > 0;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <ProjectSwitcher />
            </SidebarHeader>

            <SidebarContent>
                {hasDatabase && (
                    <SidebarGroup className="flex-1">
                        <div className="flex items-center justify-between px-2">
                            <SidebarGroupLabel className="p-0">Tables</SidebarGroupLabel>
                            <div className="flex items-center gap-1.5 group-data-[collapsible=icon]:hidden">
                                <Label
                                    htmlFor="pinned-toggle"
                                    className="text-muted-foreground flex cursor-pointer items-center gap-1 text-xs"
                                >
                                    <Pin className="size-3" />
                                    Pinned
                                </Label>
                                <Switch
                                    id="pinned-toggle"
                                    checked={pinnedOnly}
                                    onCheckedChange={setPinnedOnly}
                                    className="scale-75"
                                />
                            </div>
                        </div>

                        <div className="relative mt-2 px-2 group-data-[collapsible=icon]:hidden">
                            <Search className="text-muted-foreground pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2" />
                            <Input
                                type="text"
                                placeholder="Search tables..."
                                value={searchQuery}
                                onChange={(e) => setSearchQuery(e.target.value)}
                                className="h-8 pl-8 text-sm"
                            />
                        </div>

                        <SidebarGroupContent className="mt-2">
                            <SidebarMenu>
                                {tablesToShow.length === 0 ? (
                                    <div className="text-muted-foreground px-2 py-3 text-center text-xs">
                                        {pinnedOnly
                                            ? 'No pinned tables'
                                            : searchQuery
                                                ? 'No tables found'
                                                : 'No tables available'
                                        }
                                    </div>
                                ) : (
                                    tablesToShow.map((tableName) => {
                                        const isPinned = pinnedTables.includes(tableName);
                                        return (
                                            <SidebarMenuItem key={tableName}>
                                                <SidebarMenuButton asChild>
                                                    <a
                                                        href={`${tablesIndex().url}?tab=${encodeURIComponent(tableName)}`}
                                                        onClick={(e) => handleTableClick(e, tableName)}
                                                        className="group/item"
                                                    >
                                                        <Table2 className="size-4" />
                                                        <span className="truncate">{tableName}</span>
                                                        {isPinned && !pinnedOnly && (
                                                            <Pin className="text-muted-foreground ml-auto size-3" />
                                                        )}
                                                    </a>
                                                </SidebarMenuButton>
                                            </SidebarMenuItem>
                                        );
                                    })
                                )}
                            </SidebarMenu>
                        </SidebarGroupContent>
                    </SidebarGroup>
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>

            <SidebarResizeHandle />
        </Sidebar>
    );
}
