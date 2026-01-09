import TextLink from '@/components/text-link';
import AuthLayout from '@/layouts/auth-layout';
import { login } from '@/routes';
import { Head } from '@inertiajs/react';
import { XCircle } from 'lucide-react';

interface Props {
    message: string;
}

export default function InvalidInvitation({ message }: Props) {
    return (
        <AuthLayout
            title="Invalid Invitation"
            description="This invitation link is not valid"
        >
            <Head title="Invalid Invitation" />

            <div className="flex flex-col items-center gap-6">
                <div className="flex size-16 items-center justify-center rounded-full bg-destructive/10">
                    <XCircle className="size-8 text-destructive" />
                </div>

                <div className="text-center">
                    <p className="text-muted-foreground">{message}</p>
                    <p className="mt-2 text-sm text-muted-foreground">
                        If you believe this is an error, please contact the
                        project owner.
                    </p>
                </div>

                <p className="text-center text-sm text-muted-foreground">
                    <TextLink href={login().url}>Go to login</TextLink>
                </p>
            </div>
        </AuthLayout>
    );
}
