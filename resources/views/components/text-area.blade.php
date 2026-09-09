@props(['value', 'name', 'error'])
<textarea
    {{ $attributes->class([
        'textarea textarea-bordered textarea-xs resize-y px-3  border shadow-sm border-slate-300 placeholder-slate-400
                focus:outline-none focus:border-accent focus:ring-accent focus:ring-0 block w-full  sm:text-sm font-semibold text-base-content ',
        'border-rose-500 ring-1 ring-rose-500 textarea textarea-bordered textarea-xs  px-3  border shadow-sm border-slate-300 placeholder-slate-400' => $error,
    ]) }}
    @isset($name) name="{{ $name }}" @endif
    @isset($value) value="{{ $value }}" @endif
    {{ $attributes }}></textarea>