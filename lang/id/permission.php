<?php

return [

    // General
    'permission' => 'Perizinan',
    'permissions' => 'Kelola Perizinan',
    'permission_label' => 'Perizinan Pengguna',

    // Table & Form Labels
    'permission_name' => 'Nama Perizinan',
    'guard_name' => 'Guard',

    // Placeholders
    'permission_placeholder' => 'Masukkan nama model, misal User, Post, dll.',

    // Meta
    'created_at' => 'Dibuat Saat',
    'updated_at' => 'Terakhir Diperbarui',

    // Actions
    'create_permission' => 'Tambah Perizinan',
    'edit_permission' => 'Ubah Perizinan',
    'delete_permission' => 'Hapus Perizinan',

    // Form Sections
    'permission_information' => 'Informasi Perizinan',
    'permission_information_desc' => 'Digunakan untuk mengatur hak akses sistem.',
    'helper_text_permission' =>
        'Masukkan nama izin dengan format seperti <strong>View Any User</strong>, <strong>Create User</strong>, atau <strong>Delete User</strong>.
        <br><strong>Catatan:</strong> Gantilah kata <strong>"User"</strong> dengan entitas yang sesuai, misalnya <strong>View Any Order</strong> atau <strong>Create Product</strong>.
        <br><strong>Wajib:</strong> Setiap entitas harus memiliki <strong>7 izin</strong> berikut dengan format yang <u>persis</u> seperti di bawah ini:<br>
        <ul>
            <li><strong>View Any</strong> - Melihat semua data</li>
            <li><strong>View</strong> - Melihat detail data</li>
            <li><strong>Create</strong> - Menambahkan data baru</li>
            <li><strong>Update</strong> - Mengubah data</li>
            <li><strong>Delete</strong> - Menghapus data</li>
            <li><strong>Restore</strong> - Mengembalikan data yang terhapus</li>
            <li><strong>Force Delete</strong> - Menghapus data secara permanen</li>
        </ul>
        <br><strong>Penting:</strong> Gunakan format yang benar agar sistem dapat mengenali izin dengan tepat.',
];
