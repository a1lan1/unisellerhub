<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import {
  Store,
  ChevronRight,
  LayoutDashboard,
  MessageCircle,
  Activity
} from 'lucide-vue-next'
import { storeToRefs } from 'pinia'
import {
  Collapsible,
  CollapsibleContent,
  CollapsibleTrigger
} from '@/components/ui/collapsible'
import {
  SidebarGroup,
  SidebarGroupLabel,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem,
  SidebarMenuSub,
  SidebarMenuSubButton,
  SidebarMenuSubItem
} from '@/components/ui/sidebar'
import { useCurrentUrl } from '@/composables/useCurrentUrl'
import { useMarketplaceStore } from '@/stores/marketplace'
import { getMarketplaceLabel } from '@/utils/marketplace'

const marketplaceStore = useMarketplaceStore()
const { activeConnections } = storeToRefs(marketplaceStore)
const { isCurrentUrl } = useCurrentUrl()

const getMarketplaceUrl = (id: number) => `/marketplaces/${id}`
</script>

<template>
  <SidebarGroup v-if="activeConnections.length > 0">
    <SidebarGroupLabel>Marketplaces</SidebarGroupLabel>
    <SidebarMenu>
      <Collapsible
        v-for="conn in activeConnections"
        :key="conn.id"
        as-child
        class="group/collapsible"
      >
        <SidebarMenuItem>
          <CollapsibleTrigger as-child>
            <SidebarMenuButton
              :tooltip="getMarketplaceLabel(conn.marketplace)"
              :is-active="isCurrentUrl(getMarketplaceUrl(conn.id))"
            >
              <Store class="size-4" />
              <span>{{ getMarketplaceLabel(conn.marketplace) }}</span>
              <ChevronRight class="ml-auto size-4 transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90" />
            </SidebarMenuButton>
          </CollapsibleTrigger>
          <CollapsibleContent>
            <SidebarMenuSub>
              <SidebarMenuSubItem>
                <SidebarMenuSubButton as-child>
                  <Link :href="getMarketplaceUrl(conn.id)">
                    <LayoutDashboard class="size-4 mr-2" />
                    <span>Dashboard</span>
                  </Link>
                </SidebarMenuSubButton>
              </SidebarMenuSubItem>

              <!-- Platform-specific links -->
              <SidebarMenuSubItem v-if="conn.marketplace === 'avito'">
                <SidebarMenuSubButton as-child>
                  <Link :href="`${getMarketplaceUrl(conn.id)}/messenger`">
                    <MessageCircle class="size-4 mr-2" />
                    <span>Messenger</span>
                  </Link>
                </SidebarMenuSubButton>
              </SidebarMenuSubItem>

              <SidebarMenuSubItem>
                <SidebarMenuSubButton as-child>
                  <Link :href="`${getMarketplaceUrl(conn.id)}/logs`">
                    <Activity class="size-4 mr-2" />
                    <span>Sync Logs</span>
                  </Link>
                </SidebarMenuSubButton>
              </SidebarMenuSubItem>
            </SidebarMenuSub>
          </CollapsibleContent>
        </SidebarMenuItem>
      </Collapsible>
    </SidebarMenu>
  </SidebarGroup>
</template>
