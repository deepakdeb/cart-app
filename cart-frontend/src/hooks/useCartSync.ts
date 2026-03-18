import { useEffect, useRef } from 'react';
import { useAppSelector, useAppDispatch } from '@/store/hooks';
import { useBatchSyncMutation } from '@/store/services/cartApi';
import { setCart, setPendingSync } from '@/store/slices/cartSlice';

const DEBOUNCE_MS = 1500;

export function useCartSync() {
  const dispatch     = useAppDispatch();
  const items        = useAppSelector((s) => s.cart.items);
  const pendingSync  = useAppSelector((s) => s.cart.pendingSync);
  const user         = useAppSelector((s) => s.auth.user);
  const [batchSync]  = useBatchSyncMutation();
  const timerRef     = useRef<NodeJS.Timeout | null>(null);

  useEffect(() => {
    if (!pendingSync || !user) return;

    if (timerRef.current) clearTimeout(timerRef.current);

    timerRef.current = setTimeout(async () => {
      const payload = items.map((i) => ({
        product_id: i.product_id,
        quantity:   i.quantity,
      }));
      try {
        const result = await batchSync({ items: payload }).unwrap();
        dispatch(setCart(result.data));
        dispatch(setPendingSync(false));
      } catch (err) {
        console.error('Cart sync failed:', err);
      }
    }, DEBOUNCE_MS);

    return () => { if (timerRef.current) clearTimeout(timerRef.current); };
  }, [items, pendingSync, user]);
}