<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  series: number[];
  labels: string[];
  height?: number | string;
  colors?: string[];
  title?: string;
}

const props = withDefaults(defineProps<Props>(), {
  height: 350,
  colors: () => [
    '#42A5F5',
    '#66BB6A',
    '#FFA726',
    '#26C6DA',
    '#FF7043',
    '#5C6BC0',
    '#D4E157',
    '#8D6E63'
  ],
  title: undefined
})

const chartOptions = computed(() => ({
  chart: {
    type: 'donut',
    fontFamily: 'inherit'
  },
  colors: props.colors,
  labels: props.labels,
  title: {
    text: props.title,
    align: 'left',
    style: { fontSize: '16px', fontWeight: 'bold' }
  },
  legend: { position: 'bottom' },
  plotOptions: {
    pie: {
      donut: {
        size: '60%',
        labels: {
          show: true,
          total: {
            show: true,
            label: 'Total',
            formatter: (w: any) => w.globals.seriesTotals.reduce((a: number, b: number) => a + b, 0)
          }
        }
      }
    }
  },
  dataLabels: { enabled: true }
}))
</script>

<template>
  <div class="base-donut-chart">
    <apexchart
      :height="height"
      type="donut"
      :options="chartOptions"
      :series="series"
    />
  </div>
</template>
