<aside class="w-72 h-screen bg-white border-r border-slate-200 sticky top-0 flex flex-col">
    {{-- Brand --}}
    <div class="px-6 py-5 border-b border-slate-200">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-slate-900 flex items-center justify-center text-white text-sm font-semibold">
                LU
            </div>
            <div class="leading-tight">
                <div class="font-semibold text-slate-900">LinkUp</div>
                <div class="text-xs text-slate-500">Social Network</div>
            </div>
        </div>
    </div>

    {{-- Nav (scrollable) --}}
    <nav class="px-3 py-4 space-y-1 overflow-y-auto">
        @php
            $itemBase = 'group flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition';
            $itemOff  = 'text-slate-700 hover:bg-slate-100';
            $itemOn   = 'bg-slate-900 text-white shadow-sm';
            $iconBase = 'h-5 w-5 opacity-80 group-hover:opacity-100';
        @endphp

        <a href="{{ route('dashboard') }}"
           class="{{ $itemBase }} {{ request()->routeIs('dashboard') ? $itemOn : $itemOff }}">
            <span class="{{ $iconBase }}">🏠</span>
            <span class="font-medium">Dashboard</span>
        </a>

        <a href="{{ route('friends.index') }}"
           class="{{ $itemBase }} {{ request()->routeIs('friends.*') ? $itemOn : $itemOff }}">
            <span class="{{ $iconBase }}">👥</span>
            <span class="font-medium">Friends</span>
        </a>

        <a href="{{ route('profile.edit') }}"
           class="{{ $itemBase }} {{ request()->routeIs('profile.*') ? $itemOn : $itemOff }}">
            <span class="{{ $iconBase }}">👤</span>
            <span class="font-medium">Profile</span>
        </a>

        <div class="my-3 border-t border-slate-200"></div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full {{ $itemBase }} text-red-600 hover:bg-red-50">
                <span class="{{ $iconBase }}">🚪</span>
                <span class="font-medium">Logout</span>
            </button>
        </form>
    </nav>

    {{-- Footer --}}
    <div class="mt-auto px-6 py-4 border-t border-slate-200">
        <div class="text-xs text-slate-500">Connecté</div>
        <div class="text-sm font-semibold text-slate-900">
            {{ auth()->user()->username }}
        </div>
    </div>
</aside>
