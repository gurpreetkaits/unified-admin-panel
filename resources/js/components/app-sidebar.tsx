import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { ProjectSwitcher } from '@/components/project-switcher';
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
} from '@/components/ui/sidebar';
import { index as tablesIndex, show as showTable } from '@/actions/App/Http/Controllers/TableController';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { Database, Table2 } from 'lucide-react';

const mainNavItems: NavItem[] = [
    {
        title: 'Tables',
        href: tablesIndex(),
        icon: Database,
    },
];

export function AppSidebar() {
    const { currentProject } = usePage<SharedData>().props;
    const pinnedTables = currentProject?.pinned_tables ?? [];

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <ProjectSwitcher />
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />

                {pinnedTables.length > 0 && (
                    <SidebarGroup className="mt-4">
                        <SidebarGroupLabel>Pinned Tables</SidebarGroupLabel>
                        <SidebarGroupContent>
                            <SidebarMenu>
                                {pinnedTables.map((tableName) => (
                                    <SidebarMenuItem key={tableName}>
                                        <SidebarMenuButton asChild>
                                            <Link href={showTable.url(tableName)}>
                                                <Table2 className="size-4" />
                                                <span>{tableName}</span>
                                            </Link>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                ))}
                            </SidebarMenu>
                        </SidebarGroupContent>
                    </SidebarGroup>
                )}
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
