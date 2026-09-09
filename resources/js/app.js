import "toastify-js/src/toastify.css";
import Toastify from "toastify-js";
window.Toastify = Toastify;

import flatpickr from "flatpickr";
import monthSelectPlugin from "flatpickr/dist/plugins/monthSelect/index";
window.monthSelectPlugin = monthSelectPlugin.default || monthSelectPlugin;
import "flatpickr/dist/flatpickr.min.css";
import "flatpickr/dist/plugins/monthSelect/style.css";
window.flatpickr = flatpickr;

import * as echarts from 'echarts';
window.echarts = echarts;

import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
window.ClassicEditor = ClassicEditor;

import Anchor from '@alpinejs/anchor'

// --- BAGIAN PERUBAHAN ---
// Jangan import Livewire/Alpine dari vendor secara manual jika menggunakan bootstrap default
// Gunakan event 'livewire:init' untuk mendaftarkan plugin Alpine

document.addEventListener('livewire:init', () => {
    window.Alpine.plugin(Anchor)
    // Anda tidak perlu memanggil Livewire.start() secara manual
    // kecuali Anda mematikan auto-inject di config/livewire.php
})

// --- END BAGIAN PERUBAHAN ---

if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/sw.js', { scope: '/' }).then(function (registration) {
    }).catch(function (registrationError) {
    });
}
