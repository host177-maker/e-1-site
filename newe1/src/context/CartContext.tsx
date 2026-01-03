'use client';

import { createContext, useContext, useState, useEffect, ReactNode } from 'react';

export interface CartItem {
  id: number;
  productId: number;
  name: string;
  slug: string;
  image: string;
  price: number;
  oldPrice?: number;
  width?: number;
  height?: number;
  depth?: number;
  bodyColor?: string;
  profileColor?: string;
  filling?: string;
  quantity: number;
  includeAssembly: boolean;
  assemblyPrice: number;
}

interface CartContextType {
  items: CartItem[];
  addToCart: (item: Omit<CartItem, 'id' | 'quantity' | 'includeAssembly'>) => void;
  removeFromCart: (id: number) => void;
  updateQuantity: (id: number, quantity: number) => void;
  toggleAssembly: (id: number) => void;
  clearCart: () => void;
  itemCount: number;
  totalPrice: number;
  totalWithAssembly: number;
}

const CartContext = createContext<CartContextType | undefined>(undefined);

const STORAGE_KEY = 'e1_cart';

export function CartProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<CartItem[]>([]);
  const [isHydrated, setIsHydrated] = useState(false);

  // Load from localStorage on mount
  useEffect(() => {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored) {
      try {
        setItems(JSON.parse(stored));
      } catch (e) {
        console.error('Error parsing cart from localStorage:', e);
      }
    }
    setIsHydrated(true);
  }, []);

  // Save to localStorage when items change
  useEffect(() => {
    if (isHydrated) {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(items));
    }
  }, [items, isHydrated]);

  const addToCart = (item: Omit<CartItem, 'id' | 'quantity' | 'includeAssembly'>) => {
    setItems(prev => {
      // Check if same product with same params exists
      const existingIndex = prev.findIndex(
        i => i.productId === item.productId &&
          i.width === item.width &&
          i.height === item.height &&
          i.depth === item.depth &&
          i.bodyColor === item.bodyColor &&
          i.profileColor === item.profileColor &&
          i.filling === item.filling
      );

      if (existingIndex !== -1) {
        // Increase quantity
        const newItems = [...prev];
        newItems[existingIndex].quantity += 1;
        return newItems;
      }

      // Add new item
      return [...prev, {
        ...item,
        id: Date.now(),
        quantity: 1,
        includeAssembly: false,
      }];
    });
  };

  const removeFromCart = (id: number) => {
    setItems(prev => prev.filter(item => item.id !== id));
  };

  const updateQuantity = (id: number, quantity: number) => {
    if (quantity < 1) {
      removeFromCart(id);
      return;
    }
    setItems(prev => prev.map(item =>
      item.id === id ? { ...item, quantity } : item
    ));
  };

  const toggleAssembly = (id: number) => {
    setItems(prev => prev.map(item =>
      item.id === id ? { ...item, includeAssembly: !item.includeAssembly } : item
    ));
  };

  const clearCart = () => {
    setItems([]);
  };

  const itemCount = items.reduce((sum, item) => sum + item.quantity, 0);

  const totalPrice = items.reduce((sum, item) => sum + item.price * item.quantity, 0);

  const totalWithAssembly = items.reduce((sum, item) => {
    const itemTotal = item.price * item.quantity;
    const assemblyTotal = item.includeAssembly ? item.assemblyPrice * item.quantity : 0;
    return sum + itemTotal + assemblyTotal;
  }, 0);

  return (
    <CartContext.Provider
      value={{
        items,
        addToCart,
        removeFromCart,
        updateQuantity,
        toggleAssembly,
        clearCart,
        itemCount,
        totalPrice,
        totalWithAssembly,
      }}
    >
      {children}
    </CartContext.Provider>
  );
}

export function useCart() {
  const context = useContext(CartContext);
  if (context === undefined) {
    throw new Error('useCart must be used within a CartProvider');
  }
  return context;
}
