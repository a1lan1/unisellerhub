import type { Sentiment } from '@/types'

export const getSentimentColor = (sentiment: Sentiment): string => {
  switch (sentiment) {
  case 'positive':
    return 'success'
  case 'negative':
    return 'error'
  default:
    return 'grey'
  }
}

export const getSentimentIcon = (sentiment: Sentiment): string => {
  switch (sentiment) {
  case 'positive':
    return 'mdi-emoticon-happy-outline'
  case 'negative':
    return 'mdi-emoticon-sad-outline'
  case 'neutral':
    return 'mdi-emoticon-neutral-outline'
  default:
    return 'mdi-clock-outline'
  }
}
