import type { OrderStatusEnum } from '@/types'

export const getStatusColor = (status: OrderStatusEnum | string) => {
  switch (status.toLowerCase()) {
  case 'pending': return 'success'
  case 'completed': return 'success'
  case 'processing': return 'warning'
  case 'shipped': return 'info'
  case 'paid': return 'info'
  case 'cancelled': return 'error'
  case 'failed': return 'error'
  default: return 'grey'
  }
}

export const statusOptions = [
  { title: 'All Statuses', value: null },
  { title: 'Pending', value: 'pending' },
  { title: 'Processing', value: 'processing' },
  { title: 'Shipped', value: 'shipped' },
  { title: 'Completed', value: 'completed' },
  { title: 'Cancelled', value: 'cancelled' },
  { title: 'Failed', value: 'failed' }
]
