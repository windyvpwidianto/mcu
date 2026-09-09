<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('alert', (event) => {
            const data = event[0];

            // 1. Definisikan mapping warna berdasarkan tipe dari DaisyUI
            const colorMapping = {
                'success': 'var(--su)',
                'error': 'var(--er)',
                'warning': 'var(--wa)',
                'info': 'var(--in)',
                'primary': 'var(--p)'
            };

            const textColorMapping = {
                'success': 'var(--suc)',
                'error': 'var(--erc)',
                'warning': 'var(--wac)',
                'info': 'var(--inc)',
                'primary': 'var(--pc)'
            };

            // 2. Ambil warna berdasarkan tipe, default ke neutral jika tidak ada
            const bgColor = colorMapping[data['type']] || 'var(--n)';
            const textColor = textColorMapping[data['type']] || 'var(--nc)';

            Toastify({
                text: data['text'],
                duration: data['duration'] || 3000,
                close: data['close'] || true,
                gravity: "top",
                position: "right",
                stopOnFocus: true,
                style: {
                    // 3. Menggunakan oklch agar mendukung transparansi & tema DaisyUI modern
                    background: `oklch(var(${bgColor}))`,
                    color: `oklch(var(${textColor}))`,
                    borderRadius: "8px",
                    boxShadow: "0 10px 15px -3px rgba(0, 0, 0, 0.1)",
                }
            }).showToast();
        });
    });
</script>