<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gaji;

class GajiController extends Controller
{
    public function index()
    {
        $data = Gaji::with('user')->get();
        return view('admin.gaji.index', compact('data'));
    }
}
