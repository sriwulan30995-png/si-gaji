@props([
    'user' => filament()->auth()->user(),
])

<div class="flex items-center justify-center">
    <div
        class="group relative grow rounded-2xl p-3 transition-all duration-500 backdrop-blur-xl bg-white/30 dark:bg-slate-900/30 border border-white/40 dark:border-white/10 shadow-[0_8px_32px_rgba(0,0,0,0.15)] hover:shadow-[0_12px_40px_rgba(0,0,0,0.25)]">

        {{-- Glass highlight --}}
        <div
            class="pointer-events-none absolute inset-0 rounded-2xl bg-linear-to-br from-white/40 via-white/10 to-transparent dark:from-white/10 dark:via-white/5 opacity-70">
        </div>

        <div class="relative z-10 flex items-center gap-4">
            {{-- Avatar --}}
            <x-filament::avatar
                :src="filament()->getUserAvatarUrl($user)"
                :alt="__('filament-panels::layout.avatar.alt', ['name' => filament()->getUserName($user)])"
                :attributes="\Filament\Support\prepare_inherited_attributes($attributes)->class([
                    'fi-user-avatar rounded-full w-10 h-10',
                ])" />

            {{-- User Info --}}
            <div class="flex flex-col gap-0.5">
                <h3 class="text-gray-900 dark:text-gray-100 font-semibold text-xs tracking-wide">
                    {{ $user->name }}
                </h3>
                <p class="text-gray-600 dark:text-gray-300 text-[10px]">
                    {{ $user->roles->first()->name ?? 'No Role Assigned' }}
                </p>
            </div>
        </div>
    </div>
</div>
