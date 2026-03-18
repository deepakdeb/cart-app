import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { useGetCartQuery } from '@/store/services/cartApi';
import { setCart } from '@/store/slices/cartSlice';

export function useLoadCart() {
  const dispatch = useAppDispatch();
  const user     = useAppSelector((s) => s.auth.user);
  const { data } = useGetCartQuery(undefined, { skip: !user });

  useEffect(() => {
    if (data) dispatch(setCart(data));
  }, [data]);
}