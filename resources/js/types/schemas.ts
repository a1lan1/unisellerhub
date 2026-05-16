import { z } from 'zod'
import { MarketplaceEnum } from './enums'

export const baseConnectionSchema = z.object({
  marketplace: z.enum(MarketplaceEnum),
  name: z.string().min(2, 'Name must be at least 2 characters')
})

export const wbCredentialsSchema = z.object({
  token: z.string().min(10, 'Token is too short')
})
export type WbCredentials = z.infer<typeof wbCredentialsSchema>;

export const ozonCredentialsSchema = z.object({
  client_id: z.string().min(1, 'Client ID is required'),
  api_key: z.string().min(1, 'API Key is required')
})
export type OzonCredentials = z.infer<typeof ozonCredentialsSchema>;

export const yandexCredentialsSchema = z.object({
  api_key: z.string().min(1, 'API Key is required'),
  campaign_id: z.string().min(1, 'Campaign ID is required'),
  business_id: z.string().min(1, 'Business ID is required')
})
export type YandexCredentials = z.infer<typeof yandexCredentialsSchema>;

export const msCredentialsSchema = z.object({
  ms_token: z.string().min(10, 'MoySklad Token is too short')
})
export type MsCredentials = z.infer<typeof msCredentialsSchema>;

export const avitoCredentialsSchema = z.object({
  client_id: z.string().min(1, 'Client ID is required'),
  client_secret: z.string().min(1, 'Client Secret is required')
})
export type AvitoCredentials = z.infer<typeof avitoCredentialsSchema>;

// Unified connection schema using discriminated union
export const marketplaceConnectionSchema = z.discriminatedUnion('marketplace', [
  baseConnectionSchema.extend({
    marketplace: z.literal(MarketplaceEnum.WB),
    credentials: wbCredentialsSchema
  }),
  baseConnectionSchema.extend({
    marketplace: z.literal(MarketplaceEnum.OZON),
    credentials: ozonCredentialsSchema
  }),
  baseConnectionSchema.extend({
    marketplace: z.literal(MarketplaceEnum.YANDEX),
    credentials: yandexCredentialsSchema
  }),
  baseConnectionSchema.extend({
    marketplace: z.literal(MarketplaceEnum.MOYSKLAD),
    credentials: msCredentialsSchema
  }),
  baseConnectionSchema.extend({
    marketplace: z.literal(MarketplaceEnum.AVITO),
    credentials: avitoCredentialsSchema
  })
])

export type MarketplaceConnectionForm = z.infer<typeof marketplaceConnectionSchema>;

export const reviewFormSchema = z.object({
  rating: z.number().min(1, 'Rating is required'),
  comment: z.string().min(1, 'Comment is required'),
  selectedLocationId: z.number().min(1, 'Location is required')
})

export type ReviewForm = z.infer<typeof reviewFormSchema>;

export const reviewPayloadSchema = z.object({
  location_id: z.number().min(1, 'Location ID is required'),
  rating: z.number().min(1, 'Rating is required'),
  text: z.string().min(1, 'Text is required'),
  source: z.string(),
  author_name: z.string(),
  external_id: z.string(),
  published_at: z.string()
})

export type ReviewPayload = z.infer<typeof reviewPayloadSchema>;
