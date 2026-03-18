import { createApi, fetchBaseQuery } from '@reduxjs/toolkit/query/react';
import type { RootState } from '../index';
import type { CartItem } from '../slices/cartSlice';

export const cartApi = createApi({
  reducerPath: 'cartApi',
  baseQuery: fetchBaseQuery({
    // Added a fallback to avoid undefined baseUrl
    baseUrl: process.env.NEXT_PUBLIC_API_URL || 'http://localhost:8000/api', 
    prepareHeaders: (headers, { getState }) => {
      // Accessing the token from your auth slice
      const token = (getState() as RootState).auth.user?.idToken;
      if (token) {
        headers.set('Authorization', `Bearer ${token}`);
      }
      return headers;
    },
  }),
  tagTypes: ['Cart'],
  endpoints: (builder) => ({
    getCart: builder.query<CartItem[], void>({
      query: () => '/cart',
      // Ensuring res.data exists before mapping
      transformResponse: (res: { data: CartItem[] }) => res.data ?? [],
      providesTags: ['Cart'],
    }),

    addToCart: builder.mutation<CartItem, { product_id: number; quantity: number }>({
      query: (body) => ({ 
        url: '/cart', 
        method: 'POST', 
        body 
      }),
      invalidatesTags: ['Cart'],
    }),

    batchSync: builder.mutation<{ data: CartItem[] }, { items: { product_id: number; quantity: number }[] }>({
      query: (body) => ({ 
        url: '/cart/batch', 
        method: 'POST', 
        body 
      }),
      invalidatesTags: ['Cart'],
    }),

    deleteCartItem: builder.mutation<void, number>({
      query: (id) => ({ 
        url: `/cart/${id}`, 
        method: 'DELETE' 
      }),
      invalidatesTags: ['Cart'],
    }),
  }),
});

export const {
  useGetCartQuery,
  useAddToCartMutation,
  useBatchSyncMutation,
  useDeleteCartItemMutation,
} = cartApi;