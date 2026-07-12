## Deskripsi Pull Request
Jelaskan perubahan apa saja yang dibuat di PR ini.

## Hubungan dengan Issue
Fixes # [Isi dengan nomor Issue]

## Checklist Pengujian
- [ ] Berhasil menjalankan php artisan migrate di lokal.
- [ ] Endpoint API mengembalikan format response wrapper standar asdos:
  - Sukses: { success: true, message: "...", data: [...] }
  - Error: { success: false, message: "..." }