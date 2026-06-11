<?php

return [

    'label' => 'Ubah Profil',

    'form' => [

        'email' => [
            'label' => 'Alamat email',
            'placeholder' => 'Masukkan Alamat Email',
        ],

        'name' => [
            'label' => 'Nama Lengkap',
            'placeholder' => 'Masukkan Nama Lengkap',
        ],

        'avatar' => [
            'label' => 'Foto Profil',
            'helper' => 'Format yang didukung: JPG, PNG, atau GIF.',
        ],

        'password' => [
            'label' => 'Kata sandi baru',
            'placeholder' => 'Kosongkan jika tidak ingin mengubah kata sandi',
        ],

        'password_confirmation' => [
            'label' => 'Konfirmasi kata sandi baru',
            'placeholder' => 'Masukkan kembali kata sandi baru',
        ],

        'actions' => [

            'save' => [
                'label' => 'Simpan',
            ],

        ],

    ],

    'notifications' => [

        'saved' => [
            'title' => 'Disimpan',
        ],

    ],

    'actions' => [

        'cancel' => [
            'label' => 'Kembali',
        ],

    ],

];
