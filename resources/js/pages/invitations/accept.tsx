import InputError from '@/components/input-error';
import TextLink from '@/components/text-link';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { confirm } from '@/routes/invitations';
import { Form, Head } from '@inertiajs/react';
import { CheckCircle, Loader2 } from 'lucide-react';

interface InvitationDetails {
    token: string;
    email: string;
    role: string;
    project: string;
    userExists: boolean;
    hasPassword: boolean;
}

interface Props {
    invitation: InvitationDetails;
}

export default function AcceptInvitation({ invitation }: Props) {
    const loginUrl = `${login.url()}?redirect=${encodeURIComponent(`/invitations/${invitation.token}`)}`;

    // New user without password - show set password form
    const isNewUser = invitation.userExists && !invitation.hasPassword;
    // Existing user with password - show login form
    const isExistingUser = invitation.userExists && invitation.hasPassword;
    // User doesn't exist at all (edge case - shouldn't happen with new flow)
    const userNotFound = !invitation.userExists;

    return (
        <AuthLayout
            title="Team Invitation"
            description={`You've been invited to join ${invitation.project}`}
        >
            <Head title="Accept Invitation" />

            <div className="flex flex-col items-center gap-6">
                <div className="flex size-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <CheckCircle className="size-8 text-green-600 dark:text-green-400" />
                </div>

                <div className="text-center">
                    <p className="text-muted-foreground">
                        You've been invited to join{' '}
                        <span className="font-semibold text-foreground">
                            {invitation.project}
                        </span>{' '}
                        as a{' '}
                        <span className="font-semibold text-foreground">
                            {invitation.role}
                        </span>
                        .
                    </p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Invitation sent to{' '}
                        <span className="font-medium">{invitation.email}</span>
                    </p>
                </div>

                {userNotFound ? (
                    <div className="text-center text-sm text-muted-foreground">
                        <p>This invitation link appears to be invalid.</p>
                        <p className="mt-2">
                            <TextLink href={loginUrl}>
                                Try logging in instead
                            </TextLink>
                        </p>
                    </div>
                ) : isNewUser ? (
                    // New user - set password form
                    <div className="w-full">
                        <Form
                            {...confirm.form(invitation.token)}
                            className="grid gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                            value={invitation.email}
                                            readOnly
                                            className="bg-muted"
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="name">Your Name</Label>
                                        <Input
                                            id="name"
                                            name="name"
                                            type="text"
                                            autoFocus
                                            autoComplete="name"
                                            placeholder="Enter your full name"
                                            required
                                        />
                                        <InputError message={errors.name} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="password">
                                            Create Password
                                        </Label>
                                        <Input
                                            id="password"
                                            name="password"
                                            type="password"
                                            autoComplete="new-password"
                                            placeholder="Create a password"
                                            required
                                        />
                                        <InputError message={errors.password} />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="password_confirmation">
                                            Confirm Password
                                        </Label>
                                        <Input
                                            id="password_confirmation"
                                            name="password_confirmation"
                                            type="password"
                                            autoComplete="new-password"
                                            placeholder="Confirm your password"
                                            required
                                        />
                                    </div>

                                    <Button
                                        type="submit"
                                        className="w-full"
                                        disabled={processing}
                                    >
                                        {processing ? (
                                            <>
                                                <Loader2 className="mr-2 size-4 animate-spin" />
                                                Creating account...
                                            </>
                                        ) : (
                                            'Create Account & Accept'
                                        )}
                                    </Button>
                                </>
                            )}
                        </Form>
                    </div>
                ) : isExistingUser ? (
                    // Existing user - login form
                    <div className="w-full">
                        <Form
                            {...confirm.form(invitation.token)}
                            className="grid gap-4"
                        >
                            {({ processing, errors }) => (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="email">Email</Label>
                                        <Input
                                            id="email"
                                            name="email"
                                            type="email"
                                            value={invitation.email}
                                            readOnly
                                            className="bg-muted"
                                        />
                                    </div>

                                    <div className="grid gap-2">
                                        <Label htmlFor="password">
                                            Password
                                        </Label>
                                        <Input
                                            id="password"
                                            name="password"
                                            type="password"
                                            autoFocus
                                            autoComplete="current-password"
                                            placeholder="Enter your password"
                                            required
                                        />
                                        <InputError message={errors.password} />
                                        <InputError message={errors.email} />
                                    </div>

                                    <Button
                                        type="submit"
                                        className="w-full"
                                        disabled={processing}
                                    >
                                        {processing ? (
                                            <>
                                                <Loader2 className="mr-2 size-4 animate-spin" />
                                                Accepting...
                                            </>
                                        ) : (
                                            'Accept Invitation'
                                        )}
                                    </Button>
                                </>
                            )}
                        </Form>

                        <p className="mt-4 text-center text-sm text-muted-foreground">
                            Not {invitation.email}?{' '}
                            <TextLink href={loginUrl}>
                                Log in with a different account
                            </TextLink>
                        </p>
                    </div>
                ) : null}
            </div>
        </AuthLayout>
    );
}
