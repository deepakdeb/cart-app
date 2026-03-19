import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { useGetCartQuery } from '@/store/services/cartApi';
import { setCart, setLoading } from '@/store/slices/cartSlice';

export function useLoadCart() {
  const dispatch = useAppDispatch();
  const user = useAppSelector((s) => s.auth.user);
  const pendingSync = useAppSelector((s) => s.cart.pendingSync);
  const lastLocalUpdate = useAppSelector((s) => s.cart.lastLocalUpdate);

  const { data, isLoading, isFetching, fulfilledTimeStamp } = useGetCartQuery(undefined, { skip: !user });

  useEffect(() => {
    dispatch(setLoading(isLoading));
  }, [isLoading, dispatch]);

  useEffect(() => {
    if (!data || pendingSync || isFetching) return;

    // Don't overwrite local optimistic changes with stale cache data
    if ((fulfilledTimeStamp ?? 0) < lastLocalUpdate) return;

    const validItems = Array.isArray(data)
      ? data.filter((i) => i && typeof i.product_id === 'number' && typeof i.quantity === 'number')
      : [];

    dispatch(setCart(validItems));
  }, [data, fulfilledTimeStamp, pendingSync, isFetching, lastLocalUpdate, dispatch]);
}