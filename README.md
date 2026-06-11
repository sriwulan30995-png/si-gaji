<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://i.imgur.com/Ue6oJFg.png" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Laravel Chezzy Starter Kit

**Laravel Chezzy** adalah starter kit berbasis **Laravel 12 + Filament 4** yang dirancang untuk mempercepat pengembangan aplikasi admin panel dengan pendekatan **command-driven** melalui Artisan.

Starter kit ini menyediakan serangkaian perintah `chezzy` untuk:
- Penamaan aplikasi
- Pembuatan model & migration
- Relasi Eloquent
- Resource Filament
- Policy
- Theme
- Migrasi & seeding data

## Persyaratan Sistem
Pastikan environment Anda memenuhi spesifikasi berikut:

- **PHP ≥ 8.3**
- **Composer**
- **Node.js & NPM**
- **Database** (MySQL / MariaDB)

> Jika PHP Anda belum sesuai, silakan unduh di:  
> https://windows.php.net/download/

Jika sudah silahkan modifikasi file `php.ini` kemudian cari `;extension=zip` lalu hilang kan tanda `;`, sehingga menjadi `extension=zip`.  

## Instalasi
### 1. Clone Repository
Jalankan perintah berikut untuk meng-clone repositori starter kit dari GitHub ke lokal Anda.
```bash
git clone https://github.com/ZanQuenChezzyy/LaravelChezzy.git
```
Perintah ini akan mengunduh seluruh kode dari repositori ke folder dengan nama `LaravelChezzy`.

### 2. Buka Project di VS Code
Setelah proses clone selesai, masuk ke dalam `direktori/folder` dengan perintah:
```bash
cd LaravelChezzy
code .
```
Anda sekarang berada di dalam folder proyek tersebut dan masuk ke `VS Code`.  

### 3. Install Dependensi & Build Aset
Laravel menggunakan Composer dan Node untuk mengelola dependensi. Jalankan perintah berikut di terminal Anda untuk menginstal semua dependensi dan mengkonfigurasi aplikasi Laravel secara otomatis:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
npm run build
```
`composer install` : Akan mengunduh dan menginstal semua dependensi yang tercantum di dalam file `composer.json`.  
`npm install` : Akan mengunduh dan menginstal semua dependensi yang tercantum di dalam file `package.json`.  
`cp .env.example .env` : Akan  menyalin file `.env.example` menjadi `.env` untuk konfigurasi aplikasi.  
`php artisan key:generate` : Akan menghasilkan key unik untuk enkripsi data Laravel dan otomatis menambahkannya ke file `.env`.  
`php artisan storage:link` : Akan membuat tautan antara direktori publik `public/storage` dengan penyimpanan file aplikasi `storage/app/public`.  
`npm run build` : Akan mengkompilasi aset secara lokal atau di environment yang terkontrol sebelum mengunggahnya ke server.

### 4. Artisan Command Chezzy
Laravel Chezzy menyediakan serangkaian custom Artisan command yang dirancang untuk mempercepat dan menyederhanakan alur kerja pengembangan aplikasi. Command–command ini disusun agar dapat dijalankan secara berurutan, mulai dari inisialisasi aplikasi hingga admin panel siap digunakan melalui Filament. Ikuti langkah-langkah berikut secara berurutan untuk hasil yang optimal.
### 4.1 Menentukan Nama Aplikasi
```bash
php artisan chezzy
```
Langkah pertama adalah menentukan **nama aplikasi**. Perintah ini akan menginisialisasi identitas aplikasi secara global, termasuk pengaturan `APP_NAME` pada file `.env` dan konfigurasi aplikasi terkait. Nama aplikasi yang ditentukan akan digunakan secara konsisten di seluruh sistem, seperti judul halaman, panel Filament, dan metadata aplikasi.
> **Jalankan perintah ini terlebih dahulu sebelum membuat model atau resource lainnya.**
### 4.2 Membuat Model & Migration
```bash
php artisan chezzy:model
```
Setelah nama aplikasi ditentukan, langkah berikutnya adalah membuat **Model Eloquent** dan **file Migration**. Command ini membantu Anda mendefinisikan struktur data tanpa perlu menulis boilerplate code secara manual. Model dan migration yang dihasilkan akan mengikuti konvensi Laravel, sehingga struktur database lebih konsisten dan mudah dikembangkan.
> **Jalankan perintah ini setiap kali Anda ingin menambahkan tabel atau entitas baru ke dalam aplikasi.**
### 4.3 Membuat Relasi Eloquent
```bash
php artisan chezzy:relation
```
Setelah model dibuat, perintah ini digunakan untuk mendefinisikan **relasi antar model Eloquent**, seperti `hasOne`, `hasMany`, `belongsTo`, atau `belongsToMany`. Command ini akan membantu menambahkan method relasi secara otomatis pada masing-masing model yang terlibat, sehingga hubungan antar tabel dapat langsung digunakan dalam query, Filament Resource, maupun business logic lainnya.
> **Jalankan perintah ini sebelum migrasi database agar relasi sesuai dengan struktur tabel yang akan dibuat.**
### 4.4 Migrasi Database & Seeder
```bash
php artisan chezzy:migration
```
Perintah ini berfungsi untuk menjalankan migrasi database sekaligus seeding data awal aplikasi. Command ini akan mengeksekusi seluruh migration yang telah dibuat dan mengisi data penting seperti user, role, dan permission, sehingga aplikasi dapat langsung digunakan tanpa konfigurasi tambahan.
> **Pastikan konfigurasi database di file `.env` sudah benar sebelum menjalankan perintah ini.**
### 4.5 Membuat Resource Filament
```bash
php artisan chezzy:resource
```
Perintah ini digunakan untuk membuat **Filament Resource** berdasarkan model yang telah tersedia. Resource yang dihasilkan mencakup fitur CRUD (Create, Read, Update, Delete) lengkap dan siap digunakan pada admin panel Filament. Command ini mempercepat pembuatan antarmuka manajemen data tanpa perlu konfigurasi manual yang kompleks.
> **Jalankan perintah ini setelah migrasi database selesai agar resource langsung terhubung dengan data.**

### 5. Jalankan aplikasi
```bash
composer run dev
```

### 6. Akses Filament Admin Panel
Anda dapat mengakses panel admin Filament melalui URL berikut:
```bash
http://127.0.0.1:8000
```
Gunakan kredensial berikut untuk mengakses Admin Panel:

#### Admin 
- email : `admin@starter.com`  
- password : `12345678`

#### User
- email : `user@starter.com`  
- password : `12345678`

## Fungsi Tambahan
### Membuat Policy untuk Model
```bash
php artisan chezzy:policy
```
Perintah `php artisan chezzy:policy` digunakan untuk membuat **Policy Laravel secara otomatis** berdasarkan model yang telah tersedia. Policy ini akan mengatur **izin akses dan hak CRUD** bagi setiap model, sehingga integrasi dengan Spatie Role & Permission menjadi lebih mudah dan konsisten. Dengan command ini, developer tidak perlu menulis file Policy manual satu per satu, sehingga workflow development menjadi lebih cepat dan aman.  
### Mengganti Tema
```bash
php artisan chezzy:theme
```
Perintah `php artisan chezzy:theme` memungkinkan developer untuk **mengubah tampilan tema admin panel Filament** secara cepat. Command ini akan men-generate konfigurasi tema yang sesuai pilihan, termasuk warna, font, dan komponen UI utama. Dengan begitu, branding aplikasi bisa langsung diterapkan tanpa harus mengubah file CSS/JS secara manual.
## Fitur Utama
- Laravel 12
- Filament 4 Admin Panel
- Spatie Role & Permission
- Command Artisan Custom (chezzy:*)
- Generator Model, Resource, Policy
- Theme Switche
- Struktur scalable & clean architecture

## Lisensi
Laravel Chezzy menggunakan lisensi MIT.
Laravel framework dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).
