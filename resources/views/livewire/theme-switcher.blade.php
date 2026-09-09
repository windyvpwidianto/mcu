<div class="fab">
    <div tabindex="0" role="button" class="btn btn-xs btn-circle btn-info">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-paintbrush-vertical-icon lucide-paintbrush-vertical">
            <path d="M10 2v2" />
            <path d="M14 2v4" />
            <path d="M17 2a1 1 0 0 1 1 1v9H6V3a1 1 0 0 1 1-1z" />
            <path d="M6 12a1 1 0 0 0-1 1v1a2 2 0 0 0 2 2h2a1 1 0 0 1 1 1v2.9a2 2 0 1 0 4 0V17a1 1 0 0 1 1-1h2a2 2 0 0 0 2-2v-1a1 1 0 0 0-1-1" />
        </svg>
    </div>

    <div class="fab-close">
        Tutup <span class="btn btn-circle btn-xs btn-error">✕</span>
    </div>

    @foreach($themes as $theme)
    <div>
        <div class="tooltip tooltip-left" data-tip={{ $theme }}>

            <button
                wire:click="setTheme('{{ $theme }}')"
                class="btn btn-xs btn-circle {{ session('theme') === $theme ? 'btn-primary' : '' }}">
                {{ strtoupper(substr($theme, 0, 1)) }}
            </button>
        </div>
    </div>
    @endforeach
</div>
