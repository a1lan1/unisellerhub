export interface Address {
  country: string;
  city: string;
  street: string;
  house_number: string;
  postal_code: string;
  full_address: string;
}

export interface Location {
  id: number;
  name: string;
  type: LocationType;
  address: Address;
  latitude: number;
  longitude: number;
  external_ids: Record<string, string> | null;
  reviews_count?: number;
  reviews_avg_rating?: string;
  created_at: string;
  updated_at: string;
}

export type LocationType = 'store' | 'pickup_point' | 'warehouse' | 'office';

export interface LocationForm {
  id?: number;
  name: string;
  type: LocationType;
  address: Address;
  latitude: number | null;
  longitude: number | null;
}

export type Sentiment = 'positive' | 'neutral' | 'negative' | null;

export interface Review {
  id: number;
  location_id: number;
  source: string;
  author_name: string;
  text: string | null;
  rating: number;
  sentiment: Sentiment;
  published_at: string;
  created_at: string;
}

export interface ReviewMetrics {
  average_rating: number;
  total_reviews: number;
  sentiment_distribution: {
    positive: number;
    neutral: number;
    negative: number;
  };
  source_distribution: Record<string, number>;
  rating_dynamics: { date: string; average_rating: number }[];
}

export interface ReviewFilters {
  location_id?: number;
  source?: string;
  sentiment?: string;
  page?: number;
}

export interface ResponseTemplate {
  id: number;
  title: string;
  body: string;
}
