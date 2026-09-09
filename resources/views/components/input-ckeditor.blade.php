@props([
    'name',
    'label' => null,
    'required' => false,
])
<fieldset class="fieldset mb-4">
    @if($label)
    <x-form.label :label="$label" :required="$required" />
    @endif

    <div wire:ignore>
        <textarea id="ckeditor-{{ $name }}"></textarea>
    </div>

    <!-- hidden input buat Livewire binding -->
    <input type="hidden" id="{{ $name }}" name="{{ $name }}" wire:model.live="{{ $name }}">

    <x-label-error :messages="$errors->get($name)" />

    @once
    @push('scripts')
    <script>
        document.addEventListener("livewire:navigated", () => {
            // Inisialisasi semua ckeditor yang ada
            document.querySelectorAll("textarea[id^='ckeditor-']").forEach(el => {
                if (el._ckeditorInstance) return; // hindari duplikat

                ClassicEditor
                    .create(el)
                    .then(editor => {
                        el._ckeditorInstance = editor;

                        // Hubungkan ke hidden input + Livewire
                        const hiddenInput = document.getElementById(el.id.replace('ckeditor-', ''));
                        editor.model.document.on('change:data', () => {
                            hiddenInput.value = editor.getData();
                            @this.set(hiddenInput.id, editor.getData());
                        });
                    })
                    .catch(error => console.error(error));
            });
        });

    </script>
    @endpush
    @endonce
</fieldset>
