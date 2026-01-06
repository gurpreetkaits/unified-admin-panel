import { create, store, testConnection } from '@/actions/App/Http/Controllers/ProjectController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Form, Head, router } from '@inertiajs/react';
import { CheckCircle, Database, Loader2, XCircle } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Add Project',
        href: create().url,
    },
];

export default function CreateProject() {
    const [name, setName] = useState('');
    const [slug, setSlug] = useState('');
    const [slugManuallyEdited, setSlugManuallyEdited] = useState(false);

    // Database fields
    const [dbDriver, setDbDriver] = useState('mysql');
    const [dbHost, setDbHost] = useState('');
    const [dbPort, setDbPort] = useState('3306');
    const [dbDatabase, setDbDatabase] = useState('');
    const [dbUsername, setDbUsername] = useState('');
    const [dbPassword, setDbPassword] = useState('');
    const [usersTable, setUsersTable] = useState('users');
    const [feedbacksTable, setFeedbacksTable] = useState('');

    // Connection test state
    const [testing, setTesting] = useState(false);
    const [connectionStatus, setConnectionStatus] = useState<'idle' | 'success' | 'error'>('idle');
    const [connectionError, setConnectionError] = useState<string | null>(null);
    const [availableTables, setAvailableTables] = useState<string[]>([]);

    const generateSlug = (value: string) => {
        return value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    };

    const handleNameChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newName = e.target.value;
        setName(newName);

        if (!slugManuallyEdited) {
            setSlug(generateSlug(newName));
        }
    };

    const handleSlugChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        setSlugManuallyEdited(true);
        setSlug(e.target.value);
    };

    const getCsrfToken = (): string => {
        const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
        return match ? decodeURIComponent(match[1]) : '';
    };

    const handleTestConnection = async (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();
        if (!dbHost || !dbDatabase || !dbUsername) return;

        setTesting(true);
        setConnectionStatus('idle');
        setConnectionError(null);

        try {
            const response = await fetch(testConnection.url(), {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-XSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({
                    db_driver: dbDriver,
                    db_host: dbHost,
                    db_port: parseInt(dbPort),
                    db_database: dbDatabase,
                    db_username: dbUsername,
                    db_password: dbPassword,
                }),
            });

            const data = await response.json();

            if (data.connected) {
                setConnectionStatus('success');
                setConnectionError(null);
                setAvailableTables(data.tables || []);
            } else {
                setConnectionStatus('error');
                setConnectionError(data.error || 'Connection failed');
                setAvailableTables([]);
            }
        } catch (error) {
            setConnectionStatus('error');
            setConnectionError(error instanceof Error ? error.message : 'An unexpected error occurred');
            setAvailableTables([]);
        } finally {
            setTesting(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Add Project" />
            <div className="flex h-full flex-1 flex-col gap-4 overflow-y-auto p-4">
                <div>
                    <h1 className="text-2xl font-bold">Add New Project</h1>
                    <p className="text-muted-foreground">
                        Create a new project and connect to its database
                    </p>
                </div>

                <Form {...store.form()} className="max-w-2xl space-y-6">
                    {({ processing, errors }) => (
                        <>
                            {/* Project Info */}
                            <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                                <h2 className="mb-4 text-lg font-semibold">Project Information</h2>
                                <div className="space-y-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Project Name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            value={name}
                                            onChange={handleNameChange}
                                            required
                                            placeholder="My Awesome App"
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="slug">Slug</Label>
                                        <Input
                                            id="slug"
                                            name="slug"
                                            value={slug}
                                            onChange={handleSlugChange}
                                            required
                                            placeholder="my-awesome-app"
                                        />
                                        <p className="text-xs text-muted-foreground">
                                            Lowercase letters, numbers, and hyphens only
                                        </p>
                                        <InputError message={errors.slug} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="description">
                                            Description{' '}
                                            <span className="text-muted-foreground">(optional)</span>
                                        </Label>
                                        <textarea
                                            id="description"
                                            name="description"
                                            rows={2}
                                            className="flex w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                                            placeholder="A brief description of your project..."
                                        />
                                        <InputError message={errors.description} />
                                    </div>
                                </div>
                            </div>

                            {/* Database Connection */}
                            <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                                <div className="mb-4 flex items-center justify-between">
                                    <div className="flex items-center gap-2">
                                        <Database className="size-5" />
                                        <h2 className="text-lg font-semibold">Database Connection</h2>
                                    </div>
                                    {connectionStatus === 'success' && (
                                        <Badge variant="default" className="gap-1">
                                            <CheckCircle className="size-3" />
                                            Connected
                                        </Badge>
                                    )}
                                    {connectionStatus === 'error' && (
                                        <Badge variant="destructive" className="gap-1">
                                            <XCircle className="size-3" />
                                            Failed
                                        </Badge>
                                    )}
                                </div>

                                {connectionStatus === 'error' && connectionError && (
                                    <div className="rounded-lg border border-destructive/50 bg-destructive/10 p-3 text-sm text-destructive">
                                        <p className="font-medium">Connection Error</p>
                                        <p className="mt-1 text-xs opacity-90">{connectionError}</p>
                                    </div>
                                )}

                                <div className="space-y-4">
                                    <div className="grid gap-2">
                                        <Label htmlFor="db_driver">Database Driver</Label>
                                        <input type="hidden" name="db_driver" value={dbDriver} />
                                        <Select
                                            value={dbDriver}
                                            onValueChange={setDbDriver}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select database driver" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="mysql">MySQL (Local)</SelectItem>
                                                <SelectItem value="mariadb">MariaDB (Docker)</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p className="text-xs text-muted-foreground">
                                            Use MySQL for local installations, MariaDB for Docker environments
                                        </p>
                                        <InputError message={errors.db_driver} />
                                    </div>

                                    <div className="grid grid-cols-3 gap-4">
                                        <div className="col-span-2 grid gap-2">
                                            <Label htmlFor="db_host">Host</Label>
                                            <Input
                                                id="db_host"
                                                name="db_host"
                                                value={dbHost}
                                                onChange={(e) => setDbHost(e.target.value)}
                                                placeholder="localhost or IP address"
                                            />
                                            <InputError message={errors.db_host} />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="db_port">Port</Label>
                                            <Input
                                                id="db_port"
                                                name="db_port"
                                                type="number"
                                                value={dbPort}
                                                onChange={(e) => setDbPort(e.target.value)}
                                                placeholder="3306"
                                            />
                                            <InputError message={errors.db_port} />
                                        </div>
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="db_database">Database Name</Label>
                                        <Input
                                            id="db_database"
                                            name="db_database"
                                            value={dbDatabase}
                                            onChange={(e) => setDbDatabase(e.target.value)}
                                            placeholder="my_app_database"
                                        />
                                        <InputError message={errors.db_database} />
                                    </div>

                                    <div className="grid grid-cols-2 gap-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="db_username">Username</Label>
                                            <Input
                                                id="db_username"
                                                name="db_username"
                                                value={dbUsername}
                                                onChange={(e) => setDbUsername(e.target.value)}
                                                placeholder="root"
                                            />
                                            <InputError message={errors.db_username} />
                                        </div>
                                        <div className="grid gap-2">
                                            <Label htmlFor="db_password">Password</Label>
                                            <Input
                                                id="db_password"
                                                name="db_password"
                                                type="password"
                                                value={dbPassword}
                                                onChange={(e) => setDbPassword(e.target.value)}
                                                placeholder="••••••••"
                                            />
                                            <InputError message={errors.db_password} />
                                        </div>
                                    </div>

                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={handleTestConnection}
                                        disabled={testing || !dbHost || !dbDatabase || !dbUsername}
                                    >
                                        {testing ? (
                                            <>
                                                <Loader2 className="mr-2 size-4 animate-spin" />
                                                Testing...
                                            </>
                                        ) : (
                                            'Test Connection'
                                        )}
                                    </Button>
                                </div>
                            </div>

                            {/* Table Configuration */}
                            {connectionStatus === 'success' && availableTables.length > 0 && (
                                <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                                    <h2 className="mb-4 text-lg font-semibold">Table Configuration</h2>
                                    <div className="space-y-4">
                                        <div className="grid gap-2">
                                            <Label htmlFor="users_table">Users Table</Label>
                                            <Select
                                                name="users_table"
                                                value={usersTable}
                                                onValueChange={setUsersTable}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select users table" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {availableTables.map((table) => (
                                                        <SelectItem key={table} value={table}>
                                                            {table}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.users_table} />
                                        </div>

                                        <div className="grid gap-2">
                                            <Label htmlFor="feedbacks_table">
                                                Feedbacks Table{' '}
                                                <span className="text-muted-foreground">(optional)</span>
                                            </Label>
                                            <Select
                                                name="feedbacks_table"
                                                value={feedbacksTable}
                                                onValueChange={setFeedbacksTable}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select feedbacks table" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="">None</SelectItem>
                                                    {availableTables.map((table) => (
                                                        <SelectItem key={table} value={table}>
                                                            {table}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.feedbacks_table} />
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="flex gap-3">
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Creating...' : 'Create Project'}
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </AppLayout>
    );
}
