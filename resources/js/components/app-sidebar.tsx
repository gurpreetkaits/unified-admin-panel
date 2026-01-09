import { NavUser } from '@/components/nav-user';
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
import {
    index as tablesIndex,
    pin as pinTable,
    unpin as unpinTable,
} from '@/actions/App/Http/Controllers/TableController';
import { index as projectsIndex } from '@/actions/App/Http/Controllers/ProjectController';
import { type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { FolderKanban, Layers, Pin, PinOff, Search, Users } from 'lucide-react';
import { useMemo, useState } from 'react';

export function AppSidebar() {
    const { currentProject, currentProjectRole, databaseTables, permissions } =
        usePage<SharedData>().props;
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

    const handleTableClick = (e: React.MouseEvent, tableName: string) => {
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
            router.visit(
                `${tablesIndex().url}?tab=${encodeURIComponent(tableName)}`,
            );
        }
    };

    const handlePinToggle = (e: React.MouseEvent, tableName: string, isPinned: boolean) => {
        e.preventDefault();
        e.stopPropagation();

        if (isPinned) {
            router.post(unpinTable.url(tableName), {}, { preserveScroll: true });
        } else {
            router.post(pinTable.url(tableName), {}, { preserveScroll: true });
        }
    };

    const hasDatabase = databaseTables.length > 0;

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <div className="flex items-center gap-2 px-2 py-1.5">
                    <div className="bg-sidebar-primary text-sidebar-primary-foreground flex aspect-square size-8 items-center justify-center rounded-lg">
                        <FolderKanban className="size-4" />
                    </div>
                    <div className="grid flex-1 text-left text-sm leading-tight group-data-[collapsible=icon]:hidden">
                        <span className="truncate font-semibold">
                            {currentProject?.name ?? 'No Project'}
                        </span>
                        {currentProjectRole && (
                            <span className="text-muted-foreground truncate text-xs capitalize">
                                {currentProjectRole}
                            </span>
                        )}
                    </div>
                </div>

                {/* Navigation links */}
                <SidebarMenu className="group-data-[collapsible=icon]:hidden">
                    <SidebarMenuItem>
                        <SidebarMenuButton asChild size="sm">
                            <Link href={projectsIndex().url}>
                                <Layers className="size-4" />
                                <span>All Projects</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                    {/* Team link - shown for users who can manage team */}
                    {permissions?.canManageTeam && currentProject && (
                        <SidebarMenuItem>
                            <SidebarMenuButton asChild size="sm">
                                <Link
                                    href={`/projects/${currentProject.id}/team`}
                                >
                                    <Users className="size-4" />
                                    <span>Manage Team</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    )}
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {hasDatabase && (
                    <SidebarGroup className="flex-1">
                        <div className="flex items-center justify-between px-2">
                            <SidebarGroupLabel className="p-0">
                                Tables
                            </SidebarGroupLabel>
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
                                              : 'No tables available'}
                                    </div>
                                ) : (
                                    tablesToShow.map((tableName) => {
                                        const isPinned =
                                            pinnedTables.includes(tableName);
                                        return (
                                            <SidebarMenuItem key={tableName}>
                                                <SidebarMenuButton asChild>
                                                    <a
                                                        href={`${tablesIndex().url}?tab=${encodeURIComponent(tableName)}`}
                                                        onClick={(e) =>
                                                            handleTableClick(
                                                                e,
                                                                tableName,
                                                            )
                                                        }
                                                        className="group/item"
                                                    >
                                                        <span className="truncate">
                                                            {tableName}
                                                        </span>
                                                        <button
                                                            onClick={(e) =>
                                                                handlePinToggle(
                                                                    e,
                                                                    tableName,
                                                                    isPinned,
                                                                )
                                                            }
                                                            className={`ml-auto rounded p-0.5 transition-opacity ${
                                                                isPinned
                                                                    ? 'text-primary opacity-100'
                                                                    : 'text-muted-foreground opacity-0 hover:text-foreground group-hover/item:opacity-100'
                                                            }`}
                                                            title={
                                                                isPinned
                                                                    ? 'Unpin table'
                                                                    : 'Pin table'
                                                            }
                                                        >
                                                            {isPinned ? (
                                                                <Pin className="size-3.5" />
                                                            ) : (
                                                                <PinOff className="size-3.5" />
                                                            )}
                                                        </button>
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
