import { cn } from '@/lib/utils';
import { type Project, type SharedData } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import {
    switchMethod,
    create,
} from '@/actions/App/Http/Controllers/ProjectController';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

function getProjectInitials(name: string): string {
    return name
        .split(' ')
        .map((word) => word[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}

function getProjectColor(id: number): string {
    const colors = [
        'bg-blue-500',
        'bg-green-500',
        'bg-purple-500',
        'bg-orange-500',
        'bg-pink-500',
        'bg-cyan-500',
        'bg-indigo-500',
        'bg-rose-500',
    ];
    return colors[id % colors.length];
}

export function ProjectIconRail() {
    const { projects, currentProject } = usePage<SharedData>().props;

    const handleProjectSwitch = (project: Project) => {
        if (project.id === currentProject?.id) return;

        router.post(switchMethod.url(project.id), {}, { preserveScroll: true });
    };

    return (
        <TooltipProvider delayDuration={0}>
            <div className="bg-sidebar flex h-svh w-16 flex-col items-center border-r py-2">
                {/* Project Icons */}
                <div className="flex flex-1 flex-col items-center gap-2 overflow-y-auto py-2">
                    {projects.map((project) => {
                        const isActive = project.id === currentProject?.id;
                        return (
                            <Tooltip key={project.id}>
                                <TooltipTrigger asChild>
                                    <button
                                        onClick={() =>
                                            handleProjectSwitch(project)
                                        }
                                        className={cn(
                                            'relative flex h-10 w-10 items-center justify-center rounded-xl text-sm font-semibold text-white transition-all',
                                            getProjectColor(project.id),
                                            isActive
                                                ? 'ring-primary ring-offset-sidebar ring-2 ring-offset-2'
                                                : 'opacity-70 hover:opacity-100',
                                        )}
                                    >
                                        {getProjectInitials(project.name)}
                                        {isActive && (
                                            <span className="bg-primary absolute -left-2 top-1/2 h-5 w-1 -translate-y-1/2 rounded-r" />
                                        )}
                                    </button>
                                </TooltipTrigger>
                                <TooltipContent side="right" sideOffset={8}>
                                    <p>{project.name}</p>
                                </TooltipContent>
                            </Tooltip>
                        );
                    })}
                </div>

                {/* Add Project Button */}
                <div className="border-t pt-2">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <Link
                                href={create().url}
                                className="text-muted-foreground flex h-10 w-10 items-center justify-center rounded-xl border-2 border-dashed border-muted-foreground/30 transition-colors hover:border-muted-foreground/50 hover:text-muted-foreground/80"
                            >
                                <Plus className="h-5 w-5" />
                            </Link>
                        </TooltipTrigger>
                        <TooltipContent side="right" sideOffset={8}>
                            <p>Add Project</p>
                        </TooltipContent>
                    </Tooltip>
                </div>
            </div>
        </TooltipProvider>
    );
}
