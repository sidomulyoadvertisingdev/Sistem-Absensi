<?php

return [
    'required' => ':attribute wajib diisi.',
    'image' => ':attribute harus berupa gambar.',
    'max' => [
        'numeric' => ':attribute tidak boleh lebih dari :max.',
        'file' => 'Ukuran :attribute tidak boleh lebih dari :max kilobyte.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
        'array' => ':attribute tidak boleh lebih dari :max item.',
    ],
    'attributes' => [
        'title' => 'judul',
        'content' => 'isi pengumuman',
        'image' => 'gambar',
    ],
];
