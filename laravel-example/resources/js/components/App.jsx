import { useState, useEffect } from 'react';
import { createClient } from '@supabase/supabase-js';
import axios from '../lib/axios';

const supabase = createClient(import.meta.env.VITE_SUPABASE_URL, import.meta.env.VITE_SUPABASE_ANON_KEY);

const getInitialView = () => {
  const path = window.location.pathname;
  if (path === '/login') return 'login';
  if (path === '/dashboard') return 'dashboard';
  if (path === '/verify') return 'verify'; // nueva vista para 2FA
  return 'home';
};

export default function App() {
  const [view] = useState(getInitialView);
  const [user, setUser] = useState(null);
  const [pending2FA, setPending2FA] = useState(false);

  useEffect(() => {
    const checkSession = async () => {
      const { data } = await supabase.auth.getSession();
      const jwt = data?.session?.access_token;

      if (jwt) {
        try {
          const res = await axios.post('/auth/supabase/login', {}, {
            headers: {
              Authorization: `Bearer ${jwt}`,
            },
          });

          const result = res.data;

          if (result.requires_2fa) {
            setPending2FA(true);
            window.location.href = '/verify';
            return;
          }

          localStorage.setItem('access_token', result.access_token);
          setUser(result.user);

          if (window.location.pathname === '/oauth/callback') {
            window.location.href = '/dashboard';
          }
        } catch (err) {
          console.error('Error en login:', err);
        }
      }
    };

    checkSession();
  }, []);

  const loginWithProvider = async (provider) => {
    await supabase.auth.signInWithOAuth({
      provider,
      options: {
        redirectTo: window.location.origin + '/oauth/callback',
      },
    });
  };

  return (
    <div className="min-h-screen bg-gray-900 text-white flex flex-col items-center justify-center">
      {view === 'home' && (
        <>
          <h1 className="text-3xl font-bold mb-6">Bienvenido a tu App</h1>
          <a href="/login" className="bg-blue-600 px-4 py-2 rounded mb-2">Ir a Login</a>
        </>
      )}

      {view === 'login' && (
        <>
          <h2 className="text-2xl font-semibold mb-4">Login</h2>
          <button onClick={() => loginWithProvider('google')} className="bg-blue-600 px-4 py-2 rounded mb-2">
            Login con Google
          </button>
          <button onClick={() => loginWithProvider('github')} className="bg-gray-800 px-4 py-2 rounded">
            Login con GitHub
          </button>
        </>
      )}

      {view === 'verify' && pending2FA && (
        <>
          <h2 className="text-2xl font-semibold mb-4">Verificación 2FA</h2>
          <p>Ingresa tu código de verificación para continuar.</p>
          {/* Aquí puedes agregar un input y botón para enviar el código */}
        </>
      )}

      {view === 'dashboard' && user && (
        <>
          <h2 className="text-2xl font-semibold mb-4">Dashboard</h2>
          <p>Bienvenido, {user.name}</p>
        </>
      )}
    </div>
  );
}
