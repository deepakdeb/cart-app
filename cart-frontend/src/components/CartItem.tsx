'use client';
import { useAppDispatch } from '@/store/hooks';
import { incrementItem, decrementItem, removeItem } from '@/store/slices/cartSlice';
import type { CartItem as CartItemType } from '@/store/slices/cartSlice';

export default function CartItem({ item }: { item: CartItemType }) {
  const dispatch = useAppDispatch();

  return (
    <div className="flex items-center gap-4 p-4 bg-white rounded-xl border border-gray-100">
      <img src={item.product.image} alt={item.product.name} className="w-16 h-16 object-cover rounded-lg" />
      <div className="flex-1">
        <h4 className="font-medium text-gray-800">{item.product.name}</h4>
        <p className="text-indigo-600 font-semibold">${item.product.price}</p>
      </div>
      <div className="flex items-center gap-2">
        <button
          onClick={() => dispatch(decrementItem(item.product_id))}
          className="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-lg transition"
        >−</button>
        <span className="w-8 text-center font-semibold">{item.quantity}</span>
        <button
          onClick={() => dispatch(incrementItem(item.product_id))}
          className="w-8 h-8 rounded-lg bg-gray-100 hover:bg-gray-200 font-bold text-lg transition"
        >+</button>
      </div>
      <span className="font-semibold w-20 text-right">
        ${(item.product.price * item.quantity).toFixed(2)}
      </span>
      <button
        onClick={() => dispatch(removeItem(item.product_id))}
        className="text-red-400 hover:text-red-600 text-xl transition"
      >✕</button>
    </div>
  );
}