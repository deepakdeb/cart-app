import { configureStore } from '@reduxjs/toolkit';
// import { cartApi } from './services/cartApi';
import { cartApi } from './services/cartApi';
import { productApi } from './services/productApi';
import cartReducer from './slices/cartSlice';
import authReducer from './slices/authSlice';

export const store = configureStore({
  reducer: {
    auth: authReducer,
    cart: cartReducer,
    [cartApi.reducerPath]:    cartApi.reducer,
    [productApi.reducerPath]: productApi.reducer,
  },
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware().concat(cartApi.middleware, productApi.middleware),
});

export type RootState = typeof store.getState extends () => infer R ? R : never;
export type AppDispatch = typeof store.dispatch;