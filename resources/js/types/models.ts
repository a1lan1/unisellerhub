import type { MarketplaceEnum, OrderStatusEnum } from '@/types/enums'

export type Organization = {
  id: number;
  name: string;
  slug: string;
};

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

export type ProductListing = {
  id: number;
  marketplace: MarketplaceEnum;
  product_id: number;
  product: Product;
  external_id: string;
  vendor_code: string;
  price: number;
  old_price: number;
  discount: number;
  commission_percent: string;
  logistic_cost: number;
  status: string;
  last_synced_at: string;
};

export type Warehouse = {
  id: number;
  name: string;
  marketplace: MarketplaceEnum;
  external_id: string;
  address: string;
  organization_id: number;
  organization: Organization;
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
  listing: ProductListing;
  warehouse: Warehouse
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

