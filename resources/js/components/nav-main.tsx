import { Link } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';
import { useTranslation } from '@/hooks/use-translation';

export type NavGroup = {
    title: NavItem['title'];
    items: NavItem[];
    defaultOpen?: boolean;
};

export function NavMain({ groups }: { groups: NavGroup[] }) {
    const { isCurrentUrl } = useCurrentUrl();
    const { t } = useTranslation();

    return (
        <>
            {groups.map((group) => (
                <Collapsible
                    key={group.title}
                    defaultOpen={group.defaultOpen ?? true}
                    className="group/collapsible"
                >
                    <SidebarGroup className="px-2 py-0">
                        <CollapsibleTrigger asChild>
                            <SidebarGroupLabel className="hover:bg-sidebar-accent hover:text-sidebar-accent-foreground cursor-pointer justify-between">
                                <span>{t(group.title)}</span>
                                <ChevronDown
                                    className="transition-transform group-data-[state=closed]/collapsible:-rotate-90"
                                    aria-hidden="true"
                                />
                            </SidebarGroupLabel>
                        </CollapsibleTrigger>
                        <CollapsibleContent>
                            <SidebarMenu>
                                {group.items.map((item) => (
                                    <SidebarMenuItem key={item.title}>
                                        <SidebarMenuButton
                                            asChild
                                            isActive={isCurrentUrl(item.href)}
                                            tooltip={{
                                                children: t(item.title),
                                            }}
                                        >
                                            <Link
                                                href={item.href}
                                                prefetch={
                                                    item.prefetch ?? 'hover'
                                                }
                                            >
                                                {item.icon && <item.icon />}
                                                <span>{t(item.title)}</span>
                                            </Link>
                                        </SidebarMenuButton>
                                    </SidebarMenuItem>
                                ))}
                            </SidebarMenu>
                        </CollapsibleContent>
                    </SidebarGroup>
                </Collapsible>
            ))}
        </>
    );
}
