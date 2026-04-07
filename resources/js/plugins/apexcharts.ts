import ApexCharts from 'apexcharts'
import type { App } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties {
    $apexcharts: typeof ApexCharts;
  }
}

export default {
  install(app: App) {
    app.config.globalProperties.$apexcharts = ApexCharts
    app.use(VueApexCharts)
  }
}
