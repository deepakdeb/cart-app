'use client';

import { useState, useEffect } from 'react';
import { signInWithPopup } from 'firebase/auth';
import { FirebaseError } from 'firebase/app';
import { auth, googleProvider } from '@/lib/firebase';
import { useAppSelector } from '@/store/hooks';
import { useRouter } from 'next/navigation';

export default function LoginPage() {
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  
  const user = useAppSelector((state) => state.auth.user);
  const router = useRouter();

  // Redirect if user is already authenticated
  useEffect(() => {
    if (user) {
      router.replace('/');
    }
  }, [user, router]);

  const handleGoogleLogin = async () => {
    setIsLoading(true);
    setError(null);

    try {
      const result = await signInWithPopup(auth, googleProvider);
      const token = await result.user.getIdToken();
      
      if (token) {
        // use replace to prevent the user from clicking "back" into the login page
        router.replace('/');
      }
    } catch (err) {
      const firebaseError = err as FirebaseError;
      // Handle "User closed popup" specifically so it's not treated as a scary error
      if (firebaseError.code === 'auth/popup-closed-by-user') {
        setError('Login cancelled.');
      } else {
        setError('An unexpected error occurred. Please try again.');
        console.error('Login failed:', firebaseError.message);
      }
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <main className="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div className="w-full max-w-md bg-white p-8 md:p-12 rounded-2xl shadow-sm border border-gray-100 flex flex-col items-center gap-8">
        <header className="text-center space-y-2">
          <h1 className="text-3xl font-bold text-gray-900 tracking-tight">Welcome Back</h1>
          <p className="text-gray-500">Sign in to sync your cart and profile</p>
        </header>

        {error && (
          <div className="w-full p-3 text-sm text-red-600 bg-red-50 border border-red-100 rounded-lg text-center">
            {error}
          </div>
        )}

        <button
          onClick={handleGoogleLogin}
          disabled={isLoading}
          aria-busy={isLoading}
          className="w-full flex items-center justify-center gap-3 px-6 py-3.5 border border-gray-300 rounded-xl text-gray-700 font-semibold hover:bg-gray-50 active:scale-[0.98] transition-all disabled:opacity-50 disabled:cursor-not-allowed"
        >
          {isLoading ? (
            <span className="w-5 h-5 border-2 border-gray-300 border-t-gray-600 rounded-full animate-spin" />
          ) : (
            <img 
              src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/pwa/google.svg" 
              className="w-5 h-5" 
              alt="" 
            />
          )}
          {isLoading ? 'Connecting...' : 'Continue with Google'}
        </button>

        <footer className="text-xs text-gray-400 text-center">
          By continuing, you agree to our Terms of Service.
        </footer>
      </div>
    </main>
  );
}