'use client';
import AuthGuard from '@/components/AuthGuard';
import Navbar from '@/components/Navbar';
import CartItemComponent from '@/components/CartItem';
import { useAppSelector } from '@/store/hooks';
import { useLoadCart } from '@/hooks/useLoadCart';
import { useCartSync } from '@/hooks/useCartSync';
import Link from 'next/link';

export default function CartPage() {
  useLoadCart();
  useCartSync();
  const items = useAppSelector((s) => s.cart.items);
  const loading = useAppSelector((s) => s.cart.loading);
  const total = items.reduce((sum, i) => sum + i.product.price * i.quantity, 0);

  return (
    <AuthGuard>
      <Navbar />
      <main className="max-w-3xl mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold text-gray-800 mb-8">Your Cart</h1>
        {loading ? (
          <div className="text-center py-20">
            <div className="inline-block">
              <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            </div>
            <p className="text-gray-500 mt-4">Loading your cart...</p>
          </div>
        ) : items.length === 0 ? (
          <div className="text-center py-20 text-gray-400">
            <p className="text-5xl mb-4">🛒</p>
            <p className="text-lg">Your cart is empty.</p>
            <Link href="/" className="mt-4 inline-block text-indigo-600 hover:underline">Browse products →</Link>
          </div>
        ) : (
          <>
            <div className="flex flex-col gap-3">
              {items.map((item) => <CartItemComponent key={item.product_id} item={item} />)}
            </div>
            <div className="mt-8 border-t pt-6 flex justify-between items-center">
              <span className="text-xl font-semibold text-gray-700">Total</span>
              <span className="text-2xl font-bold text-indigo-600">${total.toFixed(2)}</span>
            </div>
            <button className="mt-6 w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
              Proceed to Checkout
            </button>
          </>
        )}
      </main>
    </AuthGuard>
  );
}