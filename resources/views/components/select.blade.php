@props(['value', 'name', 'error','disable','step'])
<select  @isset($step) disabled @endif  {{ $attributes->class([
    'select select-xs select-bordered w-full focus:ring-1 focus:border-info focus:ring-info focus:outline-hidden',
    'border-rose-500 ring-1 ring-rose-500 outline-none ' => $error,
    ]) }} >
    {{ $slot }}

</select>