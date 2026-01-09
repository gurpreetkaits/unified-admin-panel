import {
    cancelInvitation,
    index as teamIndex,
    invite,
    remove,
    updateRole,
} from '@/actions/App/Http/Controllers/TeamMemberController';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
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
import {
    type BreadcrumbItem,
    type Project,
    type ProjectInvitation,
    type ProjectMember,
} from '@/types';
import { Form, Head, router } from '@inertiajs/react';
import { Clock, Crown, Loader2, Mail, Trash2, UserPlus } from 'lucide-react';
import { useState } from 'react';

interface RoleOption {
    value: string;
    label: string;
}

interface AvailableProject {
    id: number;
    name: string;
}

interface Props {
    project: Project;
    members: ProjectMember[];
    owner: { id: number; name: string; email: string };
    pendingInvitations: ProjectInvitation[];
    roles: RoleOption[];
    canManageTeam: boolean;
    availableProjects: AvailableProject[];
}

export default function TeamPage({
    project,
    members,
    owner,
    pendingInvitations,
    roles,
    canManageTeam,
    availableProjects,
}: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Team',
            href: teamIndex.url(project.id),
        },
    ];

    const [selectedRole, setSelectedRole] = useState('viewer');
    const [selectedAdditionalProjects, setSelectedAdditionalProjects] =
        useState<number[]>([]);
    const [updatingMemberId, setUpdatingMemberId] = useState<number | null>(
        null,
    );
    const [removingMemberId, setRemovingMemberId] = useState<number | null>(
        null,
    );
    const [cancellingInvitationId, setCancellingInvitationId] = useState<
        number | null
    >(null);

    const toggleProject = (projectId: number) => {
        setSelectedAdditionalProjects((prev) =>
            prev.includes(projectId)
                ? prev.filter((id) => id !== projectId)
                : [...prev, projectId],
        );
    };

    const handleRoleChange = (memberId: number, newRole: string) => {
        setUpdatingMemberId(memberId);
        router.patch(
            updateRole.url(project.id, memberId),
            { role: newRole },
            {
                preserveScroll: true,
                onFinish: () => setUpdatingMemberId(null),
            },
        );
    };

    const handleRemoveMember = (memberId: number) => {
        if (!confirm('Are you sure you want to remove this team member?')) {
            return;
        }

        setRemovingMemberId(memberId);
        router.delete(remove.url(project.id, memberId), {
            preserveScroll: true,
            onFinish: () => setRemovingMemberId(null),
        });
    };

    const handleCancelInvitation = (invitationId: number) => {
        if (!confirm('Are you sure you want to cancel this invitation?')) {
            return;
        }

        setCancellingInvitationId(invitationId);
        router.delete(cancelInvitation.url(project.id, invitationId), {
            preserveScroll: true,
            onFinish: () => setCancellingInvitationId(null),
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
            <Head title={`Team - ${project.name}`} />
            <div className="flex h-full flex-1 flex-col gap-6 overflow-y-auto p-4">
                <div>
                    <h1 className="text-2xl font-bold">Team Management</h1>
                    <p className="text-muted-foreground">
                        Manage team members for {project.name}
                    </p>
                </div>

                {/* Invite Form */}
                {canManageTeam && (
                    <div className="rounded-xl border border-sidebar-border/70 p-6 dark:border-sidebar-border">
                        <div className="mb-4 flex items-center gap-2">
                            <UserPlus className="size-5" />
                            <h2 className="text-lg font-semibold">
                                Invite Team Member
                            </h2>
                        </div>
                        <Form
                            {...invite.form(project.id)}
                            className="flex flex-col gap-4"
                            onSuccess={() => setSelectedAdditionalProjects([])}
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="flex flex-col gap-4 sm:flex-row sm:items-end">
                                        <div className="grid flex-1 gap-2">
                                            <Label htmlFor="email">
                                                Email Address
                                            </Label>
                                            <Input
                                                id="email"
                                                name="email"
                                                type="email"
                                                placeholder="colleague@example.com"
                                                required
                                            />
                                            <InputError
                                                message={errors.email}
                                            />
                                        </div>
                                        <div className="grid w-full gap-2 sm:w-40">
                                            <Label htmlFor="role">Role</Label>
                                            <input
                                                type="hidden"
                                                name="role"
                                                value={selectedRole}
                                            />
                                            <Select
                                                value={selectedRole}
                                                onValueChange={setSelectedRole}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select role" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {roles.map((role) => (
                                                        <SelectItem
                                                            key={role.value}
                                                            value={role.value}
                                                        >
                                                            {role.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.role} />
                                        </div>
                                        <Button
                                            type="submit"
                                            disabled={processing}
                                        >
                                            {processing ? (
                                                <>
                                                    <Loader2 className="mr-2 size-4 animate-spin" />
                                                    Sending...
                                                </>
                                            ) : (
                                                <>
                                                    <Mail className="mr-2 size-4" />
                                                    Invite
                                                </>
                                            )}
                                        </Button>
                                    </div>

                                    {availableProjects.length > 0 && (
                                        <div className="grid gap-2">
                                            <Label>
                                                Also grant access to these
                                                projects
                                            </Label>
                                            <div className="flex flex-wrap gap-4">
                                                {availableProjects.map(
                                                    (proj) => (
                                                        <label
                                                            key={proj.id}
                                                            className="flex cursor-pointer items-center gap-2"
                                                        >
                                                            <Checkbox
                                                                checked={selectedAdditionalProjects.includes(
                                                                    proj.id,
                                                                )}
                                                                onCheckedChange={() =>
                                                                    toggleProject(
                                                                        proj.id,
                                                                    )
                                                                }
                                                            />
                                                            <span className="text-sm">
                                                                {proj.name}
                                                            </span>
                                                            <input
                                                                type="hidden"
                                                                name="additional_project_ids[]"
                                                                value={proj.id}
                                                                disabled={
                                                                    !selectedAdditionalProjects.includes(
                                                                        proj.id,
                                                                    )
                                                                }
                                                            />
                                                        </label>
                                                    ),
                                                )}
                                            </div>
                                            <InputError
                                                message={
                                                    errors.additional_project_ids
                                                }
                                            />
                                        </div>
                                    )}
                                </>
                            )}
                        </Form>
                        <p className="mt-3 text-xs text-muted-foreground">
                            If the user already has an account, they will be
                            added immediately. Otherwise, an invitation email
                            will be sent.
                        </p>
                    </div>
                )}

                {/* Team Members */}
                <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                    <div className="border-b border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border">
                        <h2 className="text-lg font-semibold">Team Members</h2>
                        <p className="text-sm text-muted-foreground">
                            {members.length + 1} member
                            {members.length === 0 ? '' : 's'}
                        </p>
                    </div>
                    <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        {/* Owner Row */}
                        <div className="flex items-center justify-between px-6 py-4">
                            <div className="flex items-center gap-3">
                                <div className="flex size-10 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                                    <Crown className="size-5 text-amber-600 dark:text-amber-400" />
                                </div>
                                <div>
                                    <div className="font-medium">
                                        {owner.name}
                                    </div>
                                    <div className="text-sm text-muted-foreground">
                                        {owner.email}
                                    </div>
                                </div>
                            </div>
                            <Badge
                                variant="outline"
                                className="border-amber-300 text-amber-600 dark:border-amber-700 dark:text-amber-400"
                            >
                                Owner
                            </Badge>
                        </div>

                        {/* Member Rows */}
                        {members.map((member) => (
                            <div
                                key={member.id}
                                className="flex items-center justify-between px-6 py-4"
                            >
                                <div className="flex items-center gap-3">
                                    <div className="flex size-10 items-center justify-center rounded-full bg-muted">
                                        <span className="text-sm font-medium">
                                            {member.user.name
                                                .split(' ')
                                                .map((n) => n[0])
                                                .join('')
                                                .slice(0, 2)
                                                .toUpperCase()}
                                        </span>
                                    </div>
                                    <div>
                                        <div className="font-medium">
                                            {member.user.name}
                                        </div>
                                        <div className="text-sm text-muted-foreground">
                                            {member.user.email}
                                        </div>
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    {canManageTeam ? (
                                        <>
                                            <Select
                                                value={member.role}
                                                onValueChange={(value) =>
                                                    handleRoleChange(
                                                        member.id,
                                                        value,
                                                    )
                                                }
                                                disabled={
                                                    updatingMemberId ===
                                                    member.id
                                                }
                                            >
                                                <SelectTrigger className="w-28">
                                                    {updatingMemberId ===
                                                    member.id ? (
                                                        <Loader2 className="size-4 animate-spin" />
                                                    ) : (
                                                        <SelectValue />
                                                    )}
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {roles.map((role) => (
                                                        <SelectItem
                                                            key={role.value}
                                                            value={role.value}
                                                        >
                                                            {role.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() =>
                                                    handleRemoveMember(
                                                        member.id,
                                                    )
                                                }
                                                disabled={
                                                    removingMemberId ===
                                                    member.id
                                                }
                                                className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            >
                                                {removingMemberId ===
                                                member.id ? (
                                                    <Loader2 className="size-4 animate-spin" />
                                                ) : (
                                                    <Trash2 className="size-4" />
                                                )}
                                            </Button>
                                        </>
                                    ) : (
                                        <Badge variant="secondary">
                                            {member.role_label}
                                        </Badge>
                                    )}
                                </div>
                            </div>
                        ))}

                        {members.length === 0 && (
                            <div className="px-6 py-8 text-center text-muted-foreground">
                                No other team members yet. Invite someone above!
                            </div>
                        )}
                    </div>
                </div>

                {/* Pending Invitations */}
                {pendingInvitations.length > 0 && (
                    <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                        <div className="border-b border-sidebar-border/70 px-6 py-4 dark:border-sidebar-border">
                            <h2 className="text-lg font-semibold">
                                Pending Invitations
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                {pendingInvitations.length} pending invitation
                                {pendingInvitations.length === 1 ? '' : 's'}
                            </p>
                        </div>
                        <div className="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                            {pendingInvitations.map((invitation) => (
                                <div
                                    key={invitation.id}
                                    className="flex items-center justify-between px-6 py-4"
                                >
                                    <div className="flex items-center gap-3">
                                        <div className="flex size-10 items-center justify-center rounded-full bg-muted">
                                            <Mail className="size-5 text-muted-foreground" />
                                        </div>
                                        <div>
                                            <div className="font-medium">
                                                {invitation.email}
                                            </div>
                                            <div className="flex items-center gap-1 text-sm text-muted-foreground">
                                                <Clock className="size-3" />
                                                Expires{' '}
                                                {formatDate(
                                                    invitation.expires_at,
                                                )}
                                            </div>
                                            {invitation.additional_projects &&
                                                invitation.additional_projects
                                                    .length > 0 && (
                                                    <div className="mt-1 text-xs text-muted-foreground">
                                                        +{' '}
                                                        {invitation.additional_projects
                                                            .map((p) => p.name)
                                                            .join(', ')}
                                                    </div>
                                                )}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-3">
                                        <Badge variant="outline">
                                            {invitation.role_label}
                                        </Badge>
                                        {canManageTeam && (
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                onClick={() =>
                                                    handleCancelInvitation(
                                                        invitation.id,
                                                    )
                                                }
                                                disabled={
                                                    cancellingInvitationId ===
                                                    invitation.id
                                                }
                                                className="text-destructive hover:bg-destructive/10 hover:text-destructive"
                                            >
                                                {cancellingInvitationId ===
                                                invitation.id ? (
                                                    <Loader2 className="size-4 animate-spin" />
                                                ) : (
                                                    <Trash2 className="size-4" />
                                                )}
                                            </Button>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
