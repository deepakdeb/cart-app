import { useEffect } from 'react';
import { useAppDispatch, useAppSelector } from '@/store/hooks';
import { useGetCartQuery } from '@/store/services/cartApi';
import { setCart, setLoading } from '@/store/slices/cartSlice';

export function useLoadCart() {
  const dispatch = useAppDispatch();
  const user     = useAppSelector((s) => s.auth.user);
  const { data, isLoading } = useGetCartQuery(undefined, { skip: !user });

  useEffect(() => {
    dispatch(setLoading(isLoading));
  }, [isLoading, dispatch]);

  useEffect(() => {
    if (data) dispatch(setCart(data));
  }, [data, dispatch]);
}