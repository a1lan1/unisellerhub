<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import { MessageSquare, Send } from 'lucide-vue-next'
import { ref } from 'vue'
import { Card } from '@/components/ui/card'
import { dashboard } from '@/routes'
import type { MarketplaceConnection } from '@/stores/marketplace'

defineProps<{
  connection: MarketplaceConnection;
}>()

defineOptions({
  layout: {
    breadcrumbs: [
      { title: 'Dashboard', href: dashboard() },
      { title: 'Avito Messenger', href: '#' }
    ]
  }
})

// Mock Chat Data
const chats = ref([
  { id: 1, name: 'Ivan Ivanov', lastMessage: 'Is this item still available?', time: '10:45 AM', active: true, avatar: 'https://i.pravatar.cc/150?u=1' },
  { id: 2, name: 'Maria Petrova', lastMessage: 'Can you offer a discount?', time: '09:30 AM', active: false, avatar: 'https://i.pravatar.cc/150?u=2' },
  { id: 3, name: 'Alex Korolev', lastMessage: 'Thank you!', time: 'Yesterday', active: false, avatar: 'https://i.pravatar.cc/150?u=3' }
])

const activeChat = ref(chats.value[0])
const newMessage = ref('')
const messages = ref([
  { id: 1, text: 'Hello! Is this item still available?', time: '10:45 AM', isMine: false },
  { id: 2, text: 'Hi! Yes, it is.', time: '10:47 AM', isMine: true }
])

const sendMessage = () => {
  if (!newMessage.value.trim()) {
    return
  }

  messages.value.push({
    id: Date.now(),
    text: newMessage.value,
    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
    isMine: true
  })
  newMessage.value = ''
}
</script>

<template>
  <Head title="Avito Messenger" />

  <div class="h-[calc(100vh-100px)] flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <h1 class="text-2xl font-bold flex items-center gap-2">
        <MessageSquare class="text-blue-500" />
        Avito Messenger
      </h1>
      <v-chip
        color="info"
        label
        size="small"
        variant="flat"
      >
        Official API Connected
      </v-chip>
    </div>

    <Card
      border
      flat
      class="grow flex flex-row overflow-hidden"
    >
      <!-- Chat List -->
      <div class="w-80 border-r flex flex-col">
        <div class="px-4 pb-4">
          <v-text-field
            placeholder="Search chats..."
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="compact"
            hide-details
          />
        </div>

        <div class="overflow-y-auto grow">
          <div
            v-for="chat in chats"
            :key="chat.id"
            class="p-4 border-b cursor-pointer hover:bg-neutral-100 transition-colors flex items-center gap-3"
            :class="{ 'bg-primary glass border-l-4 border-l-blue-500': chat.id === activeChat.id }"
            @click="activeChat = chat"
          >
            <v-avatar size="40">
              <v-img
                :src="chat.avatar"
                alt="Avatar"
              />
            </v-avatar>
            <div class="grow min-w-0">
              <div class="flex justify-between items-start mb-1">
                <span class="font-weight-bold text-sm truncate">{{ chat.name }}</span>
                <span
                  class="text-[10px] text-neutral-500"
                  :class="{ 'text-white': chat.id === activeChat.id }"
                >{{ chat.time }}</span>
              </div>
              <p
                class="text-xs text-neutral-500 truncate"
                :class="{ 'text-white': chat.id === activeChat.id }"
              >
                {{ chat.lastMessage }}
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Active Chat Area -->
      <div class="grow flex flex-col">
        <!-- Chat Header -->
        <div class="p-4 border-b flex items-center justify-between">
          <div class="flex items-center gap-3">
            <v-avatar size="40">
              <v-img
                :src="activeChat.avatar"
                alt="Avatar"
              />
            </v-avatar>
            <div>
              <div class="font-weight-bold text-sm">
                {{ activeChat.name }}
              </div>
              <div class="text-[10px] text-green-500 flex items-center gap-1">
                <div class="w-1.5 h-1.5 rounded-full bg-green-500" />
                Online
              </div>
            </div>
          </div>
          <div class="flex gap-2">
            <v-btn
              icon="mdi-phone"
              variant="text"
              size="small"
              color="neutral-500"
            />
            <v-btn
              icon="mdi-dots-vertical"
              variant="text"
              size="small"
              color="neutral-500"
            />
          </div>
        </div>

        <!-- Messages List -->
        <div class="grow overflow-y-auto p-6 flex flex-col gap-4">
          <div
            v-for="msg in messages"
            :key="msg.id"
            class="max-w-[70%] p-3 rounded-lg text-sm shadow-sm"
            :class="msg.isMine ? 'ml-auto bg-primary rounded-tr-none' : 'bg-success rounded-tl-none'"
          >
            <div>{{ msg.text }}</div>
            <div class="text-[10px] mt-1 text-right opacity-70">
              {{ msg.time }}
            </div>
          </div>
        </div>

        <!-- Chat Input -->
        <div class="p-4 border-t">
          <div class="flex gap-2">
            <v-btn
              icon="mdi-plus"
              size="small"
            />
            <v-text-field
              v-model="newMessage"
              placeholder="Type your message..."
              variant="outlined"
              density="compact"
              hide-details
              @keyup.enter="sendMessage"
            >
              <template #append-inner>
                <v-btn
                  color="success"
                  size="small"
                  @click="sendMessage"
                >
                  <Send class="size-4" />
                </v-btn>
              </template>
            </v-text-field>
          </div>
        </div>
      </div>
    </Card>
  </div>
</template>
