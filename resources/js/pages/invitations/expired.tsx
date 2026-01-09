import TextLink from '@/components/text-link';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { Head } from '@inertiajs/react';
import { Clock } from 'lucide-react';

interface Props {
    project: string;
}

export default function ExpiredInvitation({ project }: Props) {
    return (
        <AuthLayout
            title="Invitation Expired"
            description="This invitation link has expired"
        >
            <Head title="Invitation Expired" />

            <div className="flex flex-col items-center gap-6">
                <div className="flex size-16 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/30">
                    <Clock className="size-8 text-amber-600 dark:text-amber-400" />
                </div>

                <div className="text-center">
                    <p className="text-muted-foreground">
                        The invitation to join{' '}
                        <span className="font-semibold text-foreground">
                            {project}
                        </span>{' '}
                        has expired.
                    </p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Please contact the project owner to request a new
                        invitation.
                    </p>
                </div>

                <p className="text-center text-sm text-muted-foreground">
                    <TextLink href={login().url}>Go to login</TextLink>
                </p>
            </div>
        </AuthLayout>
    );
}
