import type { MarketplaceEnum, OrderStatusEnum } from '@/types/enums'

export type MarketplaceConnection = {
  id: number;
  marketplace: MarketplaceEnum;
  name: string;
  credentials: Record<string, any>;
  is_active: boolean;
  last_synced_at?: string;
};

export type Product = {
  id: number;
  name: string;
  sku: string;
  marketplace: MarketplaceEnum;
  external_id: string;
  price: number;
  formatted_price: string;
  status: string;
  last_synced_at?: string;
};

export type InventoryItem = {
  id: number;
  product_name: string;
  sku: string;
  marketplace: MarketplaceEnum;
  warehouse_name: string;
  quantity: number;
  reserved: number;
  listing_id: number;
};

export type OrderItem = {
  product_name: string;
  sku: string;
  quantity: number;
  price: number;
  formatted_price: string;
};

export type Order = {
  id: number;
  external_id: string;
  marketplace: MarketplaceEnum;
  status: OrderStatusEnum;
  total_price: number;
  formatted_total_price: string;
  order_date: string;
  items: OrderItem[];
};

export type ActivityLog = {
  id: number;
  description: string;
  properties: {
    type?: string;
    marketplace?: MarketplaceEnum | string;
    [key: string]: any;
  };
  created_at: string;
  human_time: string;
};
