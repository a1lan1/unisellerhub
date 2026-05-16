<script setup lang="ts">
import { computed } from 'vue'

interface Series {
   name: string;
   data: number[];
}

interface Props {
  series: Series[];
  categories: string[];
  height?: number | string;
  colors?: string[];
  title?: string;
  zoomEnabled?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
  height: 350,
  colors: () => [
    '#42A5F5',
    '#2563eb',
    '#42A5F5',
    '#66BB6A',
    '#FFA726',
    '#26C6DA',
    '#FF7043',
    '#5C6BC0',
    '#D4E157',
    '#8D6E63'
  ],
  title: undefined,
  zoomEnabled: false
})

const chartOptions = computed(() => ({
  chart: {
    type: 'line',
    toolbar: { show: false },
    zoom: { enabled: props.zoomEnabled },
    fontFamily: 'inherit'
  },
  colors: props.colors,
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 3 },
  title: {
    text: props.title,
    align: 'left',
    style: { fontSize: '16px', fontWeight: 'bold' }
  },
  grid: {
    borderColor: '#f1f1f1',
    row: { opacity: 0.5 }
  },
  xaxis: {
    categories: props.categories,
    labels: { style: { colors: '#94a3b8' } }
  },
  yaxis: {
    labels: { style: { colors: '#94a3b8' } }
  },
  legend: { position: 'top', horizontalAlign: 'right' },
  tooltip: { theme: 'light' }
}))
</script>

<template>
  <div class="base-line-chart">
    <apexchart
      :height="height"
      type="line"
      :options="chartOptions"
      :series="series"
    />
  </div>
</template>
