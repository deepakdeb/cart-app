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
        .filter((i) => i.product_id && i.quantity > 0) // Filter out invalid items
        .map((i) => ({
          product_id: i.product_id,
          quantity:   i.quantity,
        }));
      
      if (payload.length === 0) {
        dispatch(setPendingSync(false));
        return;
      }
      
      try {
        await batchSync({ items: payload }).unwrap();
        dispatch(setPendingSync(false));
      } catch (err) {
        console.error('Cart sync failed:', err);
        // Optionally revert pendingSync or show error
        dispatch(setPendingSync(false));
      }
    }, DEBOUNCE_MS);

    return () => { if (timerRef.current) clearTimeout(timerRef.current); };
  }, [items, pendingSync, user]);
}