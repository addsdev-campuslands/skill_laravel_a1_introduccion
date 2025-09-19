import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { supabase } from '@/lib/supabase';

export default function Register() {
    const form = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        provider: null,
        avatar: null,
    });

    const [oauthLoading, setOauthLoading] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    const handleOAuthRegister = async (provider) => {
        setOauthLoading(true);
        const { error } = await supabase.auth.signInWithOAuth({
            provider,
            options: {
                redirectTo: `${window.location.origin}/register`, // vuelve a /register
            },
        });
        if (error) {
            console.error('OAuth error:', error.message);
            setOauthLoading(false);
        }
    };

    useEffect(() => {
        const syncWithLaravelViaForm = async () => {
            const {
                data: { session },
            } = await supabase.auth.getSession();

            if (session?.access_token) {
                const user = session.user;

                form.setData({
                    name: user.user_metadata?.name || 'OAuth User',
                    email: user.email,
                    password: 'oauth-generated',
                    password_confirmation: 'oauth-generated',
                    provider: user.app_metadata?.provider || null,
                    avatar: user.user_metadata?.avatar_url || null,
                });

                // 🔹 En cuanto setea los datos, hace el POST
                form.post(route('register'), {
                    onSuccess: () => router.visit(route('dashboard')),
                    onError: (errors) => {
                        console.error('Errores de validación:', errors); // 🔹 solo en consola
                    },
                });                
            }
        };

        supabase.auth.onAuthStateChange((event) => {
            if (event === 'SIGNED_IN') {
                syncWithLaravelViaForm();
            }
        });
    }, []);

    return (
        <GuestLayout>
            <Head title="Register" />

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="name" value="Name" />
                    <TextInput
                        id="name"
                        name="name"
                        value={form.data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        isFocused={true}
                        onChange={(e) => form.setData('name', e.target.value)}
                        required
                    />
                    <InputError message={form.errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={form.data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => form.setData('email', e.target.value)}
                        required
                    />
                    <InputError message={form.errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={form.data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => form.setData('password', e.target.value)}
                        required
                    />
                    <InputError message={form.errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel
                        htmlFor="password_confirmation"
                        value="Confirm Password"
                    />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={form.data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) =>
                            form.setData('password_confirmation', e.target.value)
                        }
                        required
                    />
                    <InputError
                        message={form.errors.password_confirmation}
                        className="mt-2"
                    />
                </div>

                <div className="mt-4 flex items-center justify-end">
                    <Link
                        href={route('login')}
                        className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                    >
                        Already registered?
                    </Link>

                    <PrimaryButton className="ms-4" disabled={form.processing}>
                        Register
                    </PrimaryButton>
                </div>
            </form>

            {/* OAuth Register Buttons */}
            <div className="mt-8 space-y-2">
                <button
                    type="button"
                    onClick={() => handleOAuthRegister('google')}
                    disabled={oauthLoading}
                    className="w-full rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700 transition disabled:opacity-60"
                >
                    {oauthLoading ? 'Redirecting…' : 'Register with Google'}
                </button>

                <button
                    type="button"
                    onClick={() => handleOAuthRegister('github')}
                    disabled={oauthLoading}
                    className="w-full rounded bg-gray-800 px-4 py-2 text-white hover:bg-gray-900 transition disabled:opacity-60"
                >
                    {oauthLoading ? 'Redirecting…' : 'Register with GitHub'}
                </button>
            </div>
        </GuestLayout>
    );
}
