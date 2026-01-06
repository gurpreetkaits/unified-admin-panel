import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { index as feedbacksIndex } from '@/actions/App/Http/Controllers/FeedbackController';
import { create as createProject } from '@/actions/App/Http/Controllers/ProjectController';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { Database, MessageSquare, Star } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Feedbacks',
        href: feedbacksIndex().url,
    },
];

interface PaginatedFeedbacks {
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
    feedbacks: PaginatedFeedbacks;
    columns: string[];
    hasDatabase: boolean;
    hasFeedbacksTable: boolean;
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
    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
        return new Date(value).toLocaleDateString();
    }
    return String(value);
}

function getStatusBadge(status: unknown) {
    if (typeof status !== 'string') return null;

    const statusLower = status.toLowerCase();
    if (statusLower === 'resolved' || statusLower === 'completed') {
        return <Badge variant="default">{status}</Badge>;
    }
    if (statusLower === 'pending' || statusLower === 'new') {
        return <Badge variant="secondary">{status}</Badge>;
    }
    if (statusLower === 'reviewed' || statusLower === 'in_progress') {
        return <Badge variant="outline">{status}</Badge>;
    }
    return <Badge variant="outline">{status}</Badge>;
}

function RatingStars({ rating }: { rating: unknown }) {
    const numRating = typeof rating === 'number' ? rating : parseInt(String(rating));
    if (isNaN(numRating) || numRating < 1) {
        return <span className="text-muted-foreground">No rating</span>;
    }

    return (
        <div className="flex items-center gap-0.5">
            {Array.from({ length: 5 }).map((_, i) => (
                <Star
                    key={i}
                    className={`size-4 ${
                        i < numRating
                            ? 'fill-yellow-400 text-yellow-400'
                            : 'text-muted-foreground/30'
                    }`}
                />
            ))}
        </div>
    );
}

export default function FeedbacksIndex({ feedbacks, columns, hasDatabase, hasFeedbacksTable }: Props) {
    const { currentProject } = usePage<SharedData>().props;

    if (!hasDatabase) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Feedbacks" />
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

    if (!hasFeedbacksTable) {
        return (
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Feedbacks" />
                <div className="flex h-full flex-1 flex-col items-center justify-center gap-4 p-4">
                    <div className="rounded-xl border border-sidebar-border/70 p-8 text-center dark:border-sidebar-border">
                        <MessageSquare className="mx-auto mb-4 size-12 text-muted-foreground" />
                        <h2 className="mb-2 text-xl font-semibold">No Feedbacks Table Configured</h2>
                        <p className="mb-4 text-muted-foreground">
                            {currentProject?.name} doesn't have a feedbacks table configured.
                        </p>
                    </div>
                </div>
            </AppLayout>
        );
    }

    // Try to find common feedback columns
    const contentCol = columns.find(c => ['content', 'message', 'feedback', 'body', 'text', 'description'].includes(c));
    const emailCol = columns.find(c => ['email', 'user_email', 'from_email'].includes(c));
    const nameCol = columns.find(c => ['name', 'user_name', 'from_name', 'author'].includes(c));
    const ratingCol = columns.find(c => ['rating', 'score', 'stars'].includes(c));
    const statusCol = columns.find(c => ['status', 'state'].includes(c));
    const dateCol = columns.find(c => ['created_at', 'date', 'submitted_at'].includes(c));

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Feedbacks" />
            <div className="flex h-full flex-1 flex-col gap-4 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold">Feedbacks</h1>
                        <p className="text-muted-foreground">
                            User feedback from {currentProject?.name}
                        </p>
                    </div>
                    <div className="text-sm text-muted-foreground">
                        {feedbacks.total} total feedbacks
                    </div>
                </div>

                <div className="grid gap-4">
                    {feedbacks.data.length === 0 ? (
                        <div className="rounded-xl border border-sidebar-border/70 p-8 text-center text-muted-foreground dark:border-sidebar-border">
                            No feedbacks found.
                        </div>
                    ) : (
                        feedbacks.data.map((feedback, index) => (
                            <div
                                key={feedback.id as number ?? index}
                                className="rounded-xl border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                            >
                                <div className="flex items-start justify-between gap-4">
                                    <div className="flex-1 space-y-2">
                                        <div className="flex items-center gap-3">
                                            {nameCol && feedback[nameCol] && (
                                                <span className="font-medium">
                                                    {String(feedback[nameCol])}
                                                </span>
                                            )}
                                            {emailCol && feedback[emailCol] && (
                                                <span className="text-sm text-muted-foreground">
                                                    {String(feedback[emailCol])}
                                                </span>
                                            )}
                                        </div>
                                        {contentCol && (
                                            <p className="text-muted-foreground">
                                                {String(feedback[contentCol] ?? '')}
                                            </p>
                                        )}
                                        <div className="flex items-center gap-4 pt-2">
                                            {ratingCol && <RatingStars rating={feedback[ratingCol]} />}
                                            {dateCol && feedback[dateCol] && (
                                                <span className="text-sm text-muted-foreground">
                                                    {formatValue(feedback[dateCol])}
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    {statusCol && feedback[statusCol] && getStatusBadge(feedback[statusCol])}
                                </div>
                            </div>
                        ))
                    )}
                </div>

                {feedbacks.last_page > 1 && (
                    <div className="flex items-center justify-between rounded-xl border border-sidebar-border/70 px-4 py-3 dark:border-sidebar-border">
                        <div className="text-sm text-muted-foreground">
                            Showing {feedbacks.from} to {feedbacks.to} of {feedbacks.total} results
                        </div>
                        <div className="flex gap-2">
                            {feedbacks.links.map((link, index) => (
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
        </AppLayout>
    );
}
