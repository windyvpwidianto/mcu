<x-mail::message>
# Request Akses Login

Halo Admin,

Terdapat permintaan login baru dengan detail sebagai berikut:

- **Nama:** {{ $name }}
- **Email:** {{ $email }}

<x-mail::button :url="route('people.details',$id_user)">
Lihat Detail User
</x-mail::button>

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>
