'use client';
import { signOut } from 'firebase/auth';
import { auth } from '@/lib/firebase';
import { useAppSelector } from '@/store/hooks';
import Link from 'next/link';

export default function Navbar() {
  const user      = useAppSelector((s) => s.auth.user);
  const cartCount = useAppSelector((s) => s.cart.items.reduce((sum, i) => sum + i.quantity, 0));

  return (
    <nav className="bg-white border-b border-gray-100 sticky top-0 z-10">
      <div className="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
        <Link href="/" className="text-xl font-bold text-indigo-600">🛍 ShopCart</Link>
        <div className="flex items-center gap-4">
          <Link href="/cart" className="relative">
            <span className="text-2xl">🛒</span>
            {cartCount > 0 && (
              <span className="absolute -top-1 -right-2 bg-indigo-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">
                {cartCount}
              </span>
            )}
          </Link>
          {user && (
            <div className="flex items-center gap-3">
              {user.photoURL && <img src={user.photoURL} className="w-8 h-8 rounded-full" alt="Avatar" />}
              <span className="text-sm text-gray-600 hidden sm:inline">{user.displayName}</span>
              <button
                onClick={() => signOut(auth)}
                className="text-sm text-red-500 hover:text-red-700"
              >Sign out</button>
            </div>
          )}
        </div>
      </div>
    </nav>
  );
}