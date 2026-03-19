import { createSlice, PayloadAction } from '@reduxjs/toolkit';

export interface CartItem {
  id: number;
  product_id: number;
  quantity: number;
  product: {
    id: number;
    name: string;
    price: number;
    image: string;
    description: string;
  };
}

interface CartState {
  items: CartItem[];
  pendingSync: boolean;
  loading: boolean;
  lastLocalUpdate: number;
}

const initialState: CartState = {
  items: [],
  pendingSync: false,
  loading: true,
  lastLocalUpdate: Date.now(),
};

const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {
    setCart(state, action: PayloadAction<CartItem[]>) {
      state.items = action.payload;
      state.loading = false;
      state.lastLocalUpdate = Date.now();
    },
    setLoading(state, action: PayloadAction<boolean>) {
      state.loading = action.payload;
    },
    incrementItem(state, action: PayloadAction<number>) {
      const item = state.items.find((i) => i.product_id === action.payload);
      if (item) {
        item.quantity += 1;
        state.pendingSync = true;
        state.lastLocalUpdate = Date.now();
      }
    },
    decrementItem(state, action: PayloadAction<number>) {
      const item = state.items.find((i) => i.product_id === action.payload);
      if (item) {
        item.quantity = Math.max(0, item.quantity - 1);
        if (item.quantity === 0) {
          state.items = state.items.filter((i) => i.product_id !== action.payload);
        }
        state.pendingSync = true;
        state.lastLocalUpdate = Date.now();
      }
    },
    removeItem(state, action: PayloadAction<number>) {
      state.items = state.items.filter((i) => i.product_id !== action.payload);
      state.pendingSync = true;
      state.lastLocalUpdate = Date.now();
    },
    addItem(state, action: PayloadAction<CartItem>) {
      const existing = state.items.find((i) => i.product_id === action.payload.product_id);
      if (existing) {
        existing.quantity += action.payload.quantity;
      } else {
        state.items.push(action.payload);
      }
      state.pendingSync = true;
      state.lastLocalUpdate = Date.now();
    },
    setPendingSync(state, action: PayloadAction<boolean>) {
      state.pendingSync = action.payload;
    },
  },
});

export const {
  setCart,
  setLoading,
  incrementItem,
  decrementItem,
  removeItem,
  addItem,
  setPendingSync,
} = cartSlice.actions;

export default cartSlice.reducer;