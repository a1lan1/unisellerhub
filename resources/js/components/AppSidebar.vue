<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import { FolderGit2, LayoutGrid, Package, Warehouse, ShoppingCart, BarChart3, Calculator } from 'lucide-vue-next'
import AppLogo from '@/components/AppLogo.vue'
import NavFooter from '@/components/NavFooter.vue'
import NavMain from '@/components/NavMain.vue'
import NavMarketplaces from '@/components/NavMarketplaces.vue'
import {
  Sidebar,
  SidebarContent,
  SidebarFooter,
  SidebarHeader,
  SidebarMenu,
  SidebarMenuButton,
  SidebarMenuItem
} from '@/components/ui/sidebar'
import { dashboard } from '@/routes'
import { abc as abcAnalytics, profitability as profitabilityAnalytics } from '@/routes/analytics'
import { dashboard as filament } from '@/routes/filament/admin/pages'
import { index as horizon } from '@/routes/horizon'
import { index as inventoryIndex } from '@/routes/inventory'
import { index as logViewer } from '@/routes/log-viewer'
import { index as ordersIndex } from '@/routes/orders'
import { index as productsIndex } from '@/routes/products'
import { defaultMethod as prometheus } from '@/routes/prometheus'
import type { NavItem } from '@/types'

const mainNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: dashboard(),
    icon: LayoutGrid
  },
  {
    title: 'Products',
    href: productsIndex(),
    icon: Package
  },
  {
    title: 'Orders',
    href: ordersIndex(),
    icon: ShoppingCart
  },
  {
    title: 'Inventory',
    href: inventoryIndex(),
    icon: Warehouse
  },
  {
    title: 'ABC Analysis',
    href: abcAnalytics(),
    icon: BarChart3
  },
  {
    title: 'Profitability',
    href: profitabilityAnalytics(),
    icon: Calculator
  }
]

const devNavItems: NavItem[] = [
  {
    title: 'Filament',
    href: filament(),
    icon: LayoutGrid
  },
  {
    title: 'Horizon',
    href: horizon(),
    icon: LayoutGrid
  },
  {
    title: 'Log Viewer',
    href: logViewer(),
    icon: LayoutGrid
  },
  {
    title: 'RabbitMQ',
    href: 'http://localhost:15672',
    icon: LayoutGrid
  },
  {
    title: 'Telescope',
    href: 'http://localhost:8585/telescope',
    icon: LayoutGrid
  },
  {
    title: 'Meilisearch',
    href: 'http://localhost:7700',
    icon: LayoutGrid
  },
  {
    title: 'Mailpit',
    href: 'http://localhost:8025',
    icon: LayoutGrid
  },
  {
    title: 'Grafana',
    href: 'http://localhost:3000/dashboards',
    icon: LayoutGrid
  },
  {
    title: 'Prometheus',
    href: 'http://localhost:9090',
    icon: LayoutGrid
  },
  {
    title: 'Prometheus Metrics',
    href: prometheus(),
    icon: LayoutGrid
  }
]

const footerNavItems: NavItem[] = [
  {
    title: 'Github',
    href: 'https://github.com/a1lan1/unisellerhub',
    icon: FolderGit2
  }
]
</script>

<template>
  <Sidebar
    collapsible="icon"
    variant="inset"
  >
    <SidebarHeader>
      <SidebarMenu>
        <SidebarMenuItem>
          <SidebarMenuButton
            size="lg"
            as-child
          >
            <Link :href="dashboard()">
              <AppLogo />
            </Link>
          </SidebarMenuButton>
        </SidebarMenuItem>
      </SidebarMenu>
    </SidebarHeader>

    <SidebarContent>
      <NavMain :items="mainNavItems" />
      <NavMarketplaces />
      <NavMain title="Dev" :items="devNavItems" />
    </SidebarContent>

    <SidebarFooter>
      <NavFooter :items="footerNavItems" />
    </SidebarFooter>
  </Sidebar>

  <slot />
</template>
