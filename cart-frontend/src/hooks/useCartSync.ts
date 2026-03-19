import { useEffect, useRef } from 'react';
import { useAppSelector, useAppDispatch } from '@/store/hooks';
import { useBatchSyncMutation } from '@/store/services/cartApi';
import { setPendingSync } from '@/store/slices/cartSlice';

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
      const payload = items
        .filter((i) => i.product_id && i.quantity > 0)
        .map((i) => ({
          product_id: i.product_id,
          quantity:   i.quantity,
        }));

      try {
        await batchSync({ items: payload }).unwrap();
        dispatch(setPendingSync(false));
      } catch (err) {
        console.error('Cart sync failed:', err);
        dispatch(setPendingSync(false));
      }
    }, DEBOUNCE_MS);

    return () => { if (timerRef.current) clearTimeout(timerRef.current); };
  }, [items, pendingSync, user]);
}