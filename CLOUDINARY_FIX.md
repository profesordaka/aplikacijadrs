# Popravka za Cloudinary PDF Upload

## Šta je bilo problem?

1. **Neusklađena polja**: Kontroler je koristio `pdf_url` polje, a model je koristio `uputstvo_file`
2. **Pogrešan pristup na Dashboard-u**: PDF je pokušavao pristupiti kao lokalnom fajlu (`asset('storage/...')`) umjesto kao Cloudinary URL-u
3. **Nedostaju kolone u bazi**: Baza nije imala `uputstvo_file` i `image_url` kolone

## Izvršena rješenja

### 1. Nova migracija
- Datoteka: `database/migrations/2026_01_26_000000_add_file_columns_to_fakulteti_table.php`
- Dodaje kolone: `uputstvo_file`, `image_url`

### 2. Ispravljen Model (Fakultet.php)
- Dodani novi fields u `$fillable`: `'uputstvo_file', 'image_url'`

### 3. Ispravljen Kontroler (FakultetController.php)
- `store()` metoda: Koristi `'uputstvo_file'` umjesto `'pdf_url'` pri uploadu
- `update()` metoda: Isto, koristi `'uputstvo_file'`
- `destroy()` metoda: Provjerava i breba `uputstvo_file` sa Cloudinary-ja

### 4. Ispravljena View (fakultet/index.blade.php)
- ADD i EDIT modalnih: Input polje za PDF sada koristi `name="uputstvo_file"` sa `accept=".pdf"`

### 5. Ispravljena View (mobility/show.blade.php)
- Uklonjen `asset('storage/...')` 
- Koristi direktan URL iz baze: `{{ $mobilnost->fakultet->uputstvo_file }}`
- Sada će direktno koristiti Cloudinary URL

## Kako deploati?

### Na lokalnoj mašini:
```bash
php artisan migrate
```

### Na Render-u:
1. Push promjene na GitHub
2. Render će automatski pokrenuti migracije
3. Ili ručno u Render Shell-u:
```bash
php artisan migrate
```

## Testiranje

1. Idi na Fakulteti stranicu
2. Klikni "Izmijeni" na fakultetu
3. Uploduj PDF fajl
4. Provjeri da li je PDF učitan na Cloudinary (trebao bi biti dostupan)
5. Idi na Dashboard -> Pogledaj -> Klikni na mobiliteti -> Trebao bi videti "!" sa PDF linkkom

## Očekivani rezultat

✅ PDF će biti uploadovan na Cloudinary
✅ PDF će biti dostupan na Dashboard-u sa direktnim linkom
✅ "!" simbol će pokazati PDF umjesto lokalnog fajla
