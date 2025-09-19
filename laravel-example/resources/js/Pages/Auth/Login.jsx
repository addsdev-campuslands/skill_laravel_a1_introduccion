import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm, router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { supabase } from '@/lib/supabase';

export default function Login({ status, canResetPassword }) {
    const form = useForm({
        email: '',
        password: '',
        remember: false,
        provider: null,
    });

    const [oauthLoading, setOauthLoading] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        form.post(route('login'), {
            onFinish: () => form.reset('password'),
        });
    };

    const handleOAuthLogin = async (provider) => {
        setOauthLoading(true);
        const { error } = await supabase.auth.signInWithOAuth({
            provider,
            options: {
                redirectTo: `${window.location.origin}/login`, // vuelve a /login
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
    
                // 🔹 Seteamos los datos mínimos
                form.setData({
                    email: user.email,
                    password: 'oauth-generated', // marcador especial
                    remember: true,
                    provider: user.app_metadata?.provider || null,
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
            <Head title="Log in" />

            {status && (
                <div className="mb-4 text-sm font-medium text-green-600">
                    {status}
                </div>
            )}

            <form onSubmit={submit}>
                <div>
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={form.data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        isFocused={true}
                        onChange={(e) => form.setData('email', e.target.value)}
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
                        autoComplete="current-password"
                        onChange={(e) => form.setData('password', e.target.value)}
                    />
                    <InputError message={form.errors.password} className="mt-2" />
                </div>

                <div className="mt-4 block">
                    <label className="flex items-center">
                        <Checkbox
                            name="remember"
                            checked={form.data.remember}
                            onChange={(e) =>
                                form.setData('remember', e.target.checked)
                            }
                        />
                        <span className="ms-2 text-sm text-gray-600">
                            Remember me
                        </span>
                    </label>
                </div>

                <div className="mt-4 flex items-center justify-end">
                    {canResetPassword && (
                        <Link
                            href={route('password.request')}
                            className="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                        >
                            Forgot your password?
                        </Link>
                    )}

                    <PrimaryButton className="ms-4" disabled={form.processing}>
                        Log in
                    </PrimaryButton>
                </div>
            </form>

            {/* OAuth Login Buttons */}
            <div className="mt-8 space-y-2">
                <button
                    type="button"
                    onClick={() => handleOAuthLogin('google')}
                    disabled={oauthLoading}
                    className="w-full rounded bg-red-600 px-4 py-2 text-white hover:bg-red-700 transition disabled:opacity-60"
                >
                    {oauthLoading ? 'Redirecting…' : 'Login with Google'}
                </button>

                <button
                    type="button"
                    onClick={() => handleOAuthLogin('github')}
                    disabled={oauthLoading}
                    className="w-full rounded bg-gray-800 px-4 py-2 text-white hover:bg-gray-900 transition disabled:opacity-60"
                >
                    {oauthLoading ? 'Redirecting…' : 'Login with GitHub'}
                </button>
            </div>
        </GuestLayout>
    );
}
