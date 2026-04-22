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
  colors: () => ['#9333ea', '#2563eb', '#dc2626', '#ca8a04', '#16a34a']
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
        size: '70%',
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
