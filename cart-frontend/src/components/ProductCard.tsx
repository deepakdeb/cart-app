'use client';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { addItem } from '@/store/slices/cartSlice';
import type { Product } from '@/store/services/productApi';
import type { CartItem } from '@/store/slices/cartSlice';

export default function ProductCard({ product }: { product: Product }) {
  const dispatch = useAppDispatch();
  const cartItems = useAppSelector((s) => s.cart.items);

  const inCart = cartItems.some((i) => i.product_id === product.id);

  const handleAdd = () => {
    // Create cart item locally for immediate UI update
    const cartItem: CartItem = {
      id: Date.now(), // Temporary ID, will be replaced by backend
      product_id: product.id,
      quantity: 1,
      product: product,
    };
    dispatch(addItem(cartItem));
  };

  return (
    <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition">
      <img src={product.image} alt={product.name} className="w-full h-48 object-cover" />
      <div className="p-4">
        <h3 className="font-semibold text-gray-800">{product.name}</h3>
        <p className="text-sm text-gray-500 mt-1 line-clamp-2">{product.description}</p>
        <div className="flex items-center justify-between mt-4">
          <span className="text-lg font-bold text-indigo-600">${product.price}</span>
          <button
            onClick={handleAdd}
            disabled={inCart}
            className={`px-4 py-2 rounded-xl text-sm font-medium transition ${
              inCart
                ? 'bg-green-100 text-green-700 cursor-default'
                : 'bg-indigo-600 text-white hover:bg-indigo-700'
            }`}
          >
            {inCart ? '✓ In Cart' : 'Add to Cart'}
          </button>
        </div>
      </div>
    </div>
  );
}