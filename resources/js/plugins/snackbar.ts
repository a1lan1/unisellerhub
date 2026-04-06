import type { App } from 'vue'
import { useSnackbarStore } from '@/stores/snackbar'

interface SnackbarShortcuts {
  (options: Omit<SnackbarMessage, 'color'>, color: SnackbarColor): void;
  success(options: Omit<SnackbarMessage, 'color'>): void;
  info(options: Omit<SnackbarMessage, 'color'>): void;
  warning(options: Omit<SnackbarMessage, 'color'>): void;
  error(options: Omit<SnackbarMessage, 'color'>): void;
}

export type SnackbarColor = 'success' | 'info' | 'warning' | 'error';

export interface SnackbarMessage {
  text: string;
  color?: SnackbarColor;
  timeout?: number;
}

export const snackbar: SnackbarShortcuts = (options, color) => {
  const snackbarStore = useSnackbarStore()
  snackbarStore.showMessage({ ...options, color })
}

snackbar.success = (options) => snackbar(options, 'success')
snackbar.info = (options) => snackbar(options, 'info')
snackbar.warning = (options) => snackbar(options, 'warning')
snackbar.error = (options) => snackbar(options, 'error')

export default {
  install(app: App) {
    app.config.globalProperties.$snackbar = snackbar
  }
}
