import type { MarketplaceEnum, OrderStatusEnum } from './enums'

export interface BaseFilter {
  search?: string | null;
  semanticSearch?: string | null;
  marketplace?: MarketplaceEnum | null;
  sort?: string | null;
  direction?: 'asc' | 'desc' | null;
  page?: number;
  per_page?: number;
}

export interface OrderFilter extends BaseFilter {
  status?: OrderStatusEnum | null;
}

export type ProductFilter = BaseFilter

export type InventoryFilter = BaseFilter
