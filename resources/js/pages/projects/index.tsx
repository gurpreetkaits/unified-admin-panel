import {
    index as projectsIndex,
    create as createProject,
    switchMethod,
    destroy,
} from '@/actions/App/Http/Controllers/ProjectController';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    CheckCircle,
    Crown,
    Database,
    Loader2,
    Plus,
    Trash2,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: projectsIndex().url,
    },
];

interface ProjectItem {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_connected: boolean;
    is_owner: boolean;
    created_at: string;
}

interface Props {
    projects: ProjectItem[];
}

export default function ProjectsIndex({ projects }: Props) {
    const { currentProject } = usePage<SharedData>().props;
    const [deletingProjectId, setDeletingProjectId] = useState<number | null>(
        null,
    );
    const [switchingProjectId, setSwitchingProjectId] = useState<number | null>(
        null,
    );

    const handleSwitchProject = (projectId: number) => {
        if (projectId === currentProject?.id) return;

        setSwitchingProjectId(projectId);
        router.post(
            switchMethod.url(projectId),
            {},
            {
                preserveScroll: true,
                onFinish: () => setSwitchingProjectId(null),
            },
        );
    };

    const handleDeleteProject = (projectId: number) => {
        if (
            !confirm(
                'Are you sure you want to delete this project? This action cannot be undone.',
            )
        ) {
            return;
        }

        setDeletingProjectId(projectId);
        router.delete(destroy.url(projectId), {
            onFinish: () => setDeletingProjectId(null),
        });
    };

    const formatDate = (dateString: string) => {
        return new Date(dateString).toLocaleDateString(undefined, {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Projects" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Projects</h1>
                        <p className="text-muted-foreground">
                            Manage your database projects
                        </p>
                    </div>
                    <Button asChild>
                        <Link href={createProject().url}>
                            <Plus className="mr-2 size-4" />
                            Add Project
                        </Link>
                    </Button>
                </div>

                {projects.length === 0 ? (
                    <div className="flex flex-1 flex-col items-center justify-center gap-4">
                        <div className="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border">
                            <Database className="mx-auto mb-4 size-12 text-muted-foreground" />
                            <h2 className="mb-2 text-xl font-semibold">
                                No Projects Yet
                            </h2>
                            <p className="mb-4 text-muted-foreground">
                                Create your first project to connect to a
                                database.
                            </p>
                            <Button asChild>
                                <Link href={createProject().url}>
                                    <Plus className="mr-2 size-4" />
                                    Add Project
                                </Link>
                            </Button>
                        </div>
                    </div>
                ) : (
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {projects.map((project) => {
                            const isActive = project.id === currentProject?.id;
                            return (
                                <div
                                    key={project.id}
                                    className={`rounded-xl border p-4 transition-colors ${
                                        isActive
                                            ? 'border-primary bg-primary/5'
                                            : 'border-sidebar-border/70 hover:border-sidebar-border dark:border-sidebar-border'
                                    }`}
                                >
                                    <div className="mb-3 flex items-start justify-between">
                                        <div className="flex items-center gap-2">
                                            <h3 className="font-semibold">
                                                {project.name}
                                            </h3>
                                            {project.is_owner && (
                                                <Crown className="size-4 text-amber-500" />
                                            )}
                                        </div>
                                        <div className="flex items-center gap-1">
                                            {project.is_connected ? (
                                                <Badge
                                                    variant="outline"
                                                    className="gap-1 border-green-300 text-green-600 dark:border-green-700 dark:text-green-400"
                                                >
                                                    <CheckCircle className="size-3" />
                                                    Connected
                                                </Badge>
                                            ) : (
                                                <Badge
                                                    variant="outline"
                                                    className="gap-1"
                                                >
                                                    <XCircle className="size-3" />
                                                    Not Connected
                                                </Badge>
                                            )}
                                        </div>
                                    </div>

                                    {project.description && (
                                        <p className="mb-3 line-clamp-2 text-sm text-muted-foreground">
                                            {project.description}
                                        </p>
                                    )}

                                    <p className="mb-4 text-xs text-muted-foreground">
                                        Created {formatDate(project.created_at)}
                                    </p>

                                    <div className="flex items-center gap-2">
                                        {isActive ? (
                                            <Badge variant="secondary">
                                                Current
                                            </Badge>
                                        ) : (
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    handleSwitchProject(
                                                        project.id,
                                                    )
                                                }
                                                disabled={
                                                    switchingProjectId ===
                                                    project.id
                                                }
                                            >
                                                {switchingProjectId ===
                                                project.id ? (
                                                    <Loader2 className="mr-2 size-4 animate-spin" />
                                                ) : null}
                                                Switch to this
                                            </Button>
                                        )}

                                        {project.is_owner && (
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                onClick={() =>
                                                    handleDeleteProject(
                                                        project.id,
                                                    )
                                                }
                                                disabled={
                                                    deletingProjectId ===
                                                    project.id
                                                }
                                                className="ml-auto text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            >
                                                {deletingProjectId ===
                                                project.id ? (
                                                    <Loader2 className="size-4 animate-spin" />
                                                ) : (
                                                    <Trash2 className="size-4" />
                                                )}
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            );
                        })}

                        {/* Add New Project Card */}
                        <Link
                            href={createProject().url}
                            className="flex min-h-[180px] flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed border-muted-foreground/30 p-4 text-muted-foreground transition-colors hover:border-muted-foreground/50 hover:text-muted-foreground/80"
                        >
                            <Plus className="size-8" />
                            <span className="font-medium">Add New Project</span>
                        </Link>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
