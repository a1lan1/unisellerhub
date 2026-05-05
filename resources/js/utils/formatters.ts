export function formatCurrency(
  value: number | string,
  currency: string = 'RUB'
): string {
  let num: number

  num = Number(value) / 100

  if (isNaN(num)) {
    num = 0
  }

  return new Intl.NumberFormat('ru-RU', {
    style: 'currency',
    currency,
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

export function formatDate(dateString: string): string {
  const date = new Date(dateString)

  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  }).format(date)
}
