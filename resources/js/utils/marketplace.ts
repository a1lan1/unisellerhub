export const marketplaceOptions = [
  { title: 'All Systems', value: null },
  { title: 'Wildberries', value: 'wb' },
  { title: 'Ozon', value: 'ozon' },
  { title: 'MoySklad', value: 'ms' },
  { title: 'Yandex Market', value: 'yandex' },
  { title: 'Avito', value: 'avito' }
]

export const getMarketplaceColor = (marketplace: string) => {
  switch (marketplace?.toLowerCase()) {
  case 'wb': return 'purple'
  case 'ozon': return 'blue'
  case 'ms': return 'red'
  case 'yandex': return 'yellow-darken-3'
  case 'avito': return 'green'
  default: return 'grey'
  }
}

export const getMarketplaceLabel = (value: string | null) => {
  return marketplaceOptions.find(o => o.value === value)?.title || value || 'Unknown'
}
