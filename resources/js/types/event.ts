export interface FlashMessage {
  success?: string;
  error?: string;
  message?: string;
}

export interface Notification {
  id: string
  title: string
  message: string
  type: 'info' | 'success' | 'warning' | 'error'
  action_url?: string
  icon?: string
  read_at: string | null
  created_at?: string
}
