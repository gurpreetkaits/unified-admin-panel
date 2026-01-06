import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    useSidebar,
} from '@/components/ui/sidebar';
import { useIsMobile } from '@/hooks/use-mobile';
import { type Project, type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Check, ChevronsUpDown, FolderKanban, Plus } from 'lucide-react';
import { create, switchMethod } from '@/actions/App/Http/Controllers/ProjectController';

export function ProjectSwitcher() {
    const { projects, currentProject } = usePage<SharedData>().props;
    const { state } = useSidebar();
    const isMobile = useIsMobile();

    const handleProjectSwitch = (project: Project) => {
        if (project.id === currentProject?.id) return;

        router.post(switchMethod.url(project.id), {}, {
            preserveScroll: true,
        });
    };

    return (
        <SidebarMenu>
            <SidebarMenuItem>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <SidebarMenuButton
                            size="lg"
                            className="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        >
                            <div className="flex aspect-square size-8 items-center justify-center rounded-lg bg-sidebar-primary text-sidebar-primary-foreground">
                                <FolderKanban className="size-4" />
                            </div>
                            <div className="grid flex-1 text-left text-sm leading-tight">
                                <span className="truncate font-semibold">
                                    {currentProject?.name ?? 'Select Project'}
                                </span>
                                <span className="truncate text-xs text-muted-foreground">
                                    {currentProject?.slug ?? ''}
                                </span>
                            </div>
                            <ChevronsUpDown className="ml-auto size-4" />
                        </SidebarMenuButton>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent
                        className="w-(--radix-dropdown-menu-trigger-width) min-w-56 rounded-lg"
                        align="start"
                        side={
                            isMobile
                                ? 'bottom'
                                : state === 'collapsed'
                                  ? 'right'
                                  : 'bottom'
                        }
                        sideOffset={4}
                    >
                        <DropdownMenuLabel className="text-xs text-muted-foreground">
                            Projects
                        </DropdownMenuLabel>
                        <DropdownMenuSeparator />
                        {projects.map((project) => (
                            <DropdownMenuItem
                                key={project.id}
                                onClick={() => handleProjectSwitch(project)}
                                className="cursor-pointer gap-2 p-2"
                            >
                                <div className="flex size-6 items-center justify-center rounded-sm border">
                                    <FolderKanban className="size-4 shrink-0" />
                                </div>
                                <span className="flex-1">{project.name}</span>
                                {project.id === currentProject?.id && (
                                    <Check className="size-4" />
                                )}
                            </DropdownMenuItem>
                        ))}
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild className="cursor-pointer gap-2 p-2">
                            <Link href={create().url}>
                                <div className="flex size-6 items-center justify-center rounded-sm border border-dashed">
                                    <Plus className="size-4 shrink-0" />
                                </div>
                                <span className="flex-1">Add Project</span>
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </SidebarMenuItem>
        </SidebarMenu>
    );
}
