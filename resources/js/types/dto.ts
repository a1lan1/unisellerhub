import type { MoneyData } from '@/types'

export interface SearchResultItem {
  type: string;
  id: number;
  title: string;
  subtitle: string;
  url: string;
}

export interface AbcItem {
  sku: string;
  name: string;
  revenue: MoneyData;
  share: number;
  group: 'A' | 'B' | 'C';
}

export interface ProfitabilityItem {
  id: number;
  marketplace: string;
  sku: string;
  name: string;
  price: MoneyData;
  cost_price: MoneyData;
  commission_percent: number;
  logistic_cost: MoneyData;
  profit: MoneyData;
  margin: number;
}
