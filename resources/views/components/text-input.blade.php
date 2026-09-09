@props(['error', 'id', 'type', 'disabled' => false])

<input @disabled($disabled) {{ $attributes->class([
        'w-full text-base-content max-w-lg pika-single font-semibold border shadow-sm input input-bordered input-xs placeholder-slate-400 focus:outline-none focus:border-accent focus:ring-accent focus:ring-0',
        'w-full max-w-lg pika-single font-semibold border shadow-sm input input-bordered input-xs placeholder-slate-400 outline-none border-rose-500 ring-rose-500 ring-0' => $error,
    ]) }} @isset($type) type="{{ $type }}" @endif @isset($id) id="{{ $id }}" @endif />
