<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ChevronRight, ChevronsUpDown, LogOut, Power } from '@lucide/vue';
import { computed, ref } from 'vue';
import AdminController from '@/actions/App/Http/Controllers/AdminController';
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import ConfirmDialog from '@/Components/Admin/ConfirmDialog.vue';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/Components/ui/collapsible';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    SidebarRail,
    useSidebar,
} from '@/Components/ui/sidebar';
import { adminNavigation, isNavActive } from '@/Layouts/adminNavigation';
import type { NavEntry } from '@/Layouts/adminNavigation';

const page = usePage();
const { isMobile, setOpenMobile } = useSidebar();

const user = computed(() => page.props.auth.user);
const isAdmin = computed(() => user.value?.role === 'Administrateur');

const sections = computed(() =>
    adminNavigation.filter((section) => user.value !== null && section.roles.includes(user.value.role)),
);

const entryActive = (entry: NavEntry): boolean => {
    if (entry.href) {
        return isNavActive(entry.href, page.url);
    }

    return entry.children?.some((child) => isNavActive(child.href, page.url, entry.children)) ?? false;
};

const initials = computed(() =>
    (user.value?.name ?? '?')
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join(''),
);

const closeOnMobile = (): void => {
    if (isMobile.value) {
        setOpenMobile(false);
    }
};

const maintenanceOpen = ref(false);
const maintenanceBusy = ref(false);

const toggleMaintenance = (): void => {
    router.visit(AdminController.maintenance(), {
        onStart: () => (maintenanceBusy.value = true),
        onFinish: () => {
            maintenanceBusy.value = false;
            maintenanceOpen.value = false;
        },
    });
};

const logout = (): void => {
    router.visit(AuthenticatedSessionController.destroy());
};
</script>

<template>
    <Sidebar collapsible="icon">
        <SidebarHeader class="gap-0 p-0">
            <Link
                :href="AdminController.dashboard()"
                class="flex items-center gap-3 px-4 pt-5 pb-4 outline-none group-data-[collapsible=icon]:justify-center group-data-[collapsible=icon]:px-0"
                @click="closeOnMobile"
            >
                <img
                    src="/images/logo.svg"
                    alt=""
                    class="size-9 shrink-0"
                />
                <span class="leading-none group-data-[collapsible=icon]:hidden">
                    <span class="block font-display text-[1.05rem] font-semibold text-sidebar-accent-foreground">
                        Dimanche à Bamako
                    </span>
                    <span class="mt-1 block text-xs text-sidebar-foreground/70">Back-office</span>
                </span>
            </Link>
            <!-- La frise bogolan : seule décoration du back-office. -->
            <div
                aria-hidden="true"
                class="motif-bogolan mx-4 h-3.5 opacity-40 group-data-[collapsible=icon]:mx-2"
            />
        </SidebarHeader>

        <SidebarContent class="pt-2 [scrollbar-color:var(--sidebar-border)_transparent] [scrollbar-width:thin]">
            <SidebarGroup v-for="section in sections" :key="section.title">
                <SidebarGroupLabel class="text-sidebar-foreground/55">{{ section.title }}</SidebarGroupLabel>
                <SidebarMenu>
                    <template v-for="entry in section.entries" :key="entry.title">
                        <Collapsible
                            v-if="entry.children"
                            as-child
                            :default-open="entryActive(entry)"
                            class="group/collapsible"
                        >
                            <SidebarMenuItem>
                                <CollapsibleTrigger as-child>
                                    <SidebarMenuButton
                                        :tooltip="entry.title"
                                        :is-active="entryActive(entry)"
                                        class="nav-entry"
                                    >
                                        <component :is="entry.icon" />
                                        <span>{{ entry.title }}</span>
                                        <ChevronRight
                                            class="ml-auto transition-transform duration-200 group-data-[state=open]/collapsible:rotate-90"
                                        />
                                    </SidebarMenuButton>
                                </CollapsibleTrigger>
                                <CollapsibleContent>
                                    <SidebarMenuSub class="border-sidebar-border">
                                        <SidebarMenuSubItem v-for="child in entry.children" :key="child.title">
                                            <SidebarMenuSubButton
                                                as-child
                                                :is-active="isNavActive(child.href, page.url, entry.children)"
                                                class="data-[active=true]:text-sidebar-primary"
                                            >
                                                <Link :href="child.href" @click="closeOnMobile">
                                                    {{ child.title }}
                                                </Link>
                                            </SidebarMenuSubButton>
                                        </SidebarMenuSubItem>
                                    </SidebarMenuSub>
                                </CollapsibleContent>
                            </SidebarMenuItem>
                        </Collapsible>

                        <SidebarMenuItem v-else-if="entry.href">
                            <SidebarMenuButton
                                as-child
                                :tooltip="entry.title"
                                :is-active="entryActive(entry)"
                                class="nav-entry"
                            >
                                <Link :href="entry.href" @click="closeOnMobile">
                                    <component :is="entry.icon" />
                                    <span>{{ entry.title }}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    </template>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter v-if="user">
            <SidebarMenu>
                <SidebarMenuItem>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <SidebarMenuButton
                                size="lg"
                                class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                            >
                                <span
                                    class="grid size-8 shrink-0 place-items-center rounded-md bg-sidebar-primary font-display text-sm font-semibold text-sidebar-primary-foreground"
                                >
                                    {{ initials }}
                                </span>
                                <span class="grid flex-1 text-left leading-tight">
                                    <span class="truncate font-medium text-sidebar-accent-foreground">{{ user.name }}</span>
                                    <span class="truncate text-xs text-sidebar-foreground/70">
                                        {{ isAdmin ? 'Administrateur' : 'Secrétaire' }}
                                    </span>
                                </span>
                                <ChevronsUpDown class="ml-auto size-4" />
                            </SidebarMenuButton>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent
                            :side="isMobile ? 'bottom' : 'right'"
                            align="end"
                            :side-offset="8"
                            class="w-60"
                        >
                            <DropdownMenuLabel class="font-normal">
                                <span class="block font-medium">{{ user.name }}</span>
                                <span class="block truncate text-xs text-muted-foreground">{{ user.email }}</span>
                            </DropdownMenuLabel>
                            <template v-if="isAdmin">
                                <DropdownMenuSeparator />
                                <DropdownMenuItem @select="maintenanceOpen = true">
                                    <Power />
                                    {{ page.props.maintenance ? 'Rouvrir la boutique' : 'Passer en maintenance' }}
                                </DropdownMenuItem>
                            </template>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem @select="logout">
                                <LogOut />
                                Se déconnecter
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarFooter>
        <SidebarRail />
    </Sidebar>

    <ConfirmDialog
        v-model:open="maintenanceOpen"
        :title="page.props.maintenance ? 'Rouvrir la boutique ?' : 'Passer la boutique en maintenance ?'"
        :description="
            page.props.maintenance
                ? 'Les clients pourront de nouveau consulter la boutique et commander.'
                : 'Les clients verront une page de maintenance et ne pourront plus commander. Vous gardez l’accès grâce au lien secret affiché ensuite.'
        "
        :confirm-label="page.props.maintenance ? 'Rouvrir la boutique' : 'Passer en maintenance'"
        :tone="page.props.maintenance ? 'default' : 'destructive'"
        :busy="maintenanceBusy"
        @confirm="toggleMaintenance"
    />
</template>

<style scoped>
/* Entrée active : repère or sur le bord gauche, comme un fil de couture. */
.nav-entry[data-active='true'] {
    box-shadow: inset 3px 0 0 var(--sidebar-primary);
}
</style>
