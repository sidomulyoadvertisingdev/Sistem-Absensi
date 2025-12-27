@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="login-container h-full w-full flex items-center justify-center p-4 overflow-auto">
    <div class="w-full max-w-6xl flex bg-white rounded-2xl card-shadow overflow-hidden">

        {{-- LEFT --}}
        <div class="hidden lg:flex lg:w-1/2 items-center justify-center relative">
            {{-- Background Image --}}
            <div class="absolute inset-0">
                <img
                    src="{{ asset('images/employee.jpg') }}"
                    alt="Employee Illustration"
                    class="w-full h-full object-cover"
                >
                {{-- Overlay --}}
                <div class="absolute inset-0 bg-blue-900/40"></div>
            </div>

            {{-- Text --}}
            <div class="relative text-center text-white px-10">
                <h2 class="text-4xl font-bold mb-4">
                    Kelola Tim Anda
                </h2>
                <p class="text-lg opacity-90">
                    Sistem manajemen karyawan yang modern dan efisien
                </p>
            </div>
        </div>

        {{-- RIGHT --}}
        <div class="w-full lg:w-1/2 p-8 sm:p-12">
            <div class="max-w-md mx-auto">

                {{-- HEADER --}}
                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full mb-4 bg-blue-100 text-blue-600">
                        <svg class="w-8 h-8" viewBox="0 0 40 40" fill="none">
                            <rect x="8" y="12" width="24" height="20" rx="2"
                                  stroke="currentColor" stroke-width="2.5"/>
                            <path d="M14 18h12M14 22h8"
                                  stroke="currentColor" stroke-width="2.5"
                                  stroke-linecap="round"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold mb-2">
                        Employee Management System
                    </h1>
                    <p class="text-gray-500">
                        Selamat datang kembali
                    </p>
                </div>

                {{-- FORM --}}
                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Email
                        </label>
                        <input type="email" name="email"
                               class="input-field w-full px-4 py-3 border-2 rounded-lg focus:outline-none focus:border-blue-600"
                               value="{{ old('email') }}"
                               required autofocus>
                        @error('email')
                            <small class="text-red-600">{{ $message }}</small>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-2">
                            Password
                        </label>
                        <input type="password" name="password"
                               class="input-field w-full px-4 py-3 border-2 rounded-lg focus:outline-none focus:border-blue-600"
                               required>
                        @error('password')
                            <small class="text-red-600">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="mr-2">
                            Ingat saya
                        </label>
                        <a href="#" class="text-blue-600 font-semibold hover:underline">
                            Lupa password?
                        </a>
                    </div>

                    <button type="submit"
                        class="login-button w-full py-3 rounded-lg text-white font-semibold text-lg bg-blue-600">
                        Masuk
                    </button>
                </form>

                {{-- FOOTER --}}
                <div class="mt-8 text-center text-sm text-gray-500">
                    © {{ date('Y') }} Sidomulyo Advertising. Semua hak dilindungi.
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
