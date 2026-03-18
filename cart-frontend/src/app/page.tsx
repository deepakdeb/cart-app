'use client';
import AuthGuard from '@/components/AuthGuard';
import Navbar from '@/components/Navbar';
import ProductCard from '@/components/ProductCard';
import { useGetProductsQuery } from '@/store/services/productApi';
import { useLoadCart } from '@/hooks/useLoadCart';
import { useCartSync } from '@/hooks/useCartSync';

export default function HomePage() {
  useLoadCart();
  useCartSync();
  const { data: products, isLoading, isError } = useGetProductsQuery();

  return (
    <AuthGuard>
      <Navbar />
      <main className="max-w-6xl mx-auto px-4 py-8">
        <h1 className="text-3xl font-bold text-gray-800 mb-8">Products</h1>
        {isLoading && <p className="text-gray-500">Loading products...</p>}
        {isError  && <p className="text-red-500">Failed to load products.</p>}
        <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          {products?.map((p) => <ProductCard key={p.id} product={p} />)}
        </div>
      </main>
    </AuthGuard>
  );
}