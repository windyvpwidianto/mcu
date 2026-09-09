<fieldset class="mt-4 fieldset lg:col-span-2">
    <x-form.label label="Kunci Pembelajaran" required />
    <div x-data="ckeditorHelper('key_learning')" wire:ignore wire:key="select-key-learning">
        <div x-ref="editorElement" data-placeholder="Masukkan kunci pembelajaran..."></div>
    </div>
    <x-label-error :messages="$errors->get('key_learning')" />
</fieldset>