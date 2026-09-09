<div class='overflow-y-scroll'>
    <flux:navlist variant="outline">
        <flux:navlist.group class="grid">
            @foreach ($Menus as $menu)
            {{-- Logika Filter tetap sama --}}
            @if ($menu->menu === 'Dashboard' && (auth()->guest() || !auth()->check()))
            @continue
            @endif
            @if ($menu->menu === 'Administrator' && (auth()->guest() || !auth()->user()->hasRole('administrator')))
            @continue
            @endif
            @if ($menu->menu === 'Laporan Insiden' && (auth()->guest() || !auth()->user()->hasRole('administrator')))
            @continue
            @endif
            @if ($menu->menu === 'Manhours' && (!auth()->check() || !auth()->user()?->can('viewAny', \App\Models\Manhour::class)))
            @continue
            @endif
            @if ($menu->menu === 'WPI' && !auth()->check())
            @continue
            @endif
            @if (
            $menu->menu === 'MCU Schedule' &&
            (auth()->guest() ||
            !auth()->user()->hasAnyRole(['administrator', 'Medical Staff'])))
            @continue
            @endif
            @if (
            $menu->menu === 'Event General' &&
            (auth()->guest() ||
            !auth()->user()->hasAnyRole(['administrator', 'Moderator'])))
            @continue
            @endif

            @php

            $menuEffects =
            'transition-all duration-300 ease-in-out hover:-translate-x-1 hover:shadow-lg hover:z-10 rounded-lg';
            @endphp

            {{-- LEVEL 1: Group dengan SubMenu --}}
            @if (count($menu->subMenus) > 0)
            <flux:navlist.group-list wire:key="menu-group-{{ $menu->id }}" expandable icon="{{ $menu->icon ?: 'ban' }}"
                route='{{ $menu->request_route }}' heading="{{ __($menu->menu) }}" class="grid">
                @foreach ($menu->subMenus as $submenu)
                {{-- LEVEL 2: Extra SubMenu --}}
                @if (count($submenu->ExtraSubMenu) > 0)
                <flux:navlist.group-list wire:key="submenu-group-{{ $submenu->id }}" expandable
                    route='{{ $submenu->request_route }}' heading="{{ __($submenu->menu) }}"
                    class="grid">
                    @foreach ($submenu->ExtraSubMenu as $xsubmenu)
                    <flux:menu.item wire:key="xsubmenu-item-{{ $xsubmenu->id }}"
                        :href="route($xsubmenu->route)"
                        :current="Request::is($xsubmenu->request_route)"
                        class="{{ $menuEffects }}">
                        {{ __($xsubmenu->menu) }}
                    </flux:menu.item>
                    @endforeach
                </flux:navlist.group-list>

                {{-- LEVEL 2: SubMenu Biasa --}}
                @else
                <flux:menu.item wire:key="submenu-item-{{ $submenu->id }}"
                    :href="$submenu->route ? route($submenu->route) : '#'"
                    :current="(($submenu->request_route!=null)? Request::is($submenu->request_route ):request()->routeIs($submenu->route ))"
                    icon="{{ $submenu->icon }}" class="{{ $menuEffects }}">
                    {{ __($submenu->menu) }}
                </flux:menu.item>
                @endif
                @endforeach
            </flux:navlist.group-list>

            {{-- LEVEL 1: Menu Single --}}
            @else
            <flux:navlist.item wire:key="menu-item-{{ $menu->id }}" icon="{{ $menu->icon ?: 'ban' }}"
                :href="$menu->route ? route($menu->route) : '#'" :current="Request::is($menu->request_route)"
                class="{{ $menuEffects }}">
                {{ __($menu->menu) }}
            </flux:navlist.item>
            @endif
            @endforeach
        </flux:navlist.group>
    </flux:navlist>
</div>