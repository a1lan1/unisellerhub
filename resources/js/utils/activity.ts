export const getActivityIcon = (type: string) => {
  switch (type) {
  case 'sync_start': return 'mdi-sync'
  case 'sync_success': return 'mdi-check-circle'
  case 'sync_error': return 'mdi-alert-circle'
  case 'order_new': return 'mdi-cart-plus'
  default: return 'mdi-information'
  }
}

export const getActivityIconColor = (type: string) => {
  switch (type) {
  case 'sync_start': return 'blue'
  case 'sync_success': return 'success'
  case 'sync_error': return 'error'
  case 'order_new': return 'warning'
  default: return 'grey'
  }
}
