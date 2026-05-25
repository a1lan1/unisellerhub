export enum OrderStatusEnum {
  PENDING = 'pending',
  PAID = 'paid',
  PROCESSING = 'processing',
  SHIPPED = 'shipped',
  COMPLETED = 'completed',
  CANCELLED = 'cancelled',
  FAILED = 'failed',
}

export enum MarketplaceEnum {
  WB = 'wb',
  OZON = 'ozon',
  MOYSKLAD = 'ms',
  YANDEX = 'yandex',
  AVITO = 'avito',
}

export enum IdempotencyOperation {
  SyncAll = 'syncAll',
  SyncProducts = 'syncProducts',
  SyncOrders = 'syncOrders',
  SyncInventory = 'syncInventory',
}
