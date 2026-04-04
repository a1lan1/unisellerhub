import type { App } from 'vue'
import axios from './axios'
import './echo'
import pinia from './pinia'
import vuetify from './vuetify'

export function registerPlugins(app: App) {
  app
    .use(axios)
    .use(pinia)
    .use(vuetify)
}
