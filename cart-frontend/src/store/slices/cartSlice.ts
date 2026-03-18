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
}

const initialState: CartState = { items: [], pendingSync: false };

const cartSlice = createSlice({
  name: 'cart',
  initialState,
  reducers: {
    setCart(state, action: PayloadAction<CartItem[]>) {
      state.items = action.payload;
    },
    incrementItem(state, action: PayloadAction<number>) {
      const item = state.items.find((i) => i.product_id === action.payload);
      if (item) { item.quantity += 1; state.pendingSync = true; }
    },
    decrementItem(state, action: PayloadAction<number>) {
      const item = state.items.find((i) => i.product_id === action.payload);
      if (item) {
        item.quantity = Math.max(0, item.quantity - 1);
        if (item.quantity === 0) {
          state.items = state.items.filter((i) => i.product_id !== action.payload);
        }
        state.pendingSync = true;
      }
    },
    removeItem(state, action: PayloadAction<number>) {
      state.items       = state.items.filter((i) => i.product_id !== action.payload);
      state.pendingSync = true;
    },
    addItem(state, action: PayloadAction<CartItem>) {
      const existing = state.items.find((i) => i.product_id === action.payload.product_id);
      if (existing) {
        existing.quantity += action.payload.quantity;
      } else {
        state.items.push(action.payload);
      }
      state.pendingSync = true;
    },
    setPendingSync(state, action: PayloadAction<boolean>) {
      state.pendingSync = action.payload;
    },
  },
});

export const {
  setCart, incrementItem, decrementItem,
  removeItem, addItem, setPendingSync,
} = cartSlice.actions;
export default cartSlice.reducer;