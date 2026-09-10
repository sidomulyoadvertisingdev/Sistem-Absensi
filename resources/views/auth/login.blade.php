@extends('layouts.auth')

@section('title', 'Masuk')

@section('content')
<div class="min-h-full w-full flex items-center justify-center p-4 sm:p-6 lg:p-10">
    <div class="auth-card w-full max-w-[1180px] bg-white rounded-[28px] overflow-hidden grid lg:grid-cols-2">

        {{-- ================= LEFT : FORM ================= --}}
        <div class="px-8 sm:px-12 lg:px-16 py-12 lg:py-14 flex flex-col">

            {{-- Brand --}}
            <div class="flex items-center gap-2.5 mb-10 lg:mb-14">
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg" style="background: var(--brand-600);">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none">
                        <path d="M12 2c-2.8 0-5 2.4-5 5.4v3.1c-1 .5-1.7 1.6-1.7 2.9v4.3C5.3 20.5 6.9 22 8.9 22h6.2c2 0 3.6-1.5 3.6-3.3v-4.3c0-1.3-.7-2.4-1.7-2.9V7.4C17 4.4 14.8 2 12 2Z" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M9.5 12.5c0-1.4 1.1-2.5 2.5-2.5s2.5 1.1 2.5 2.5M12 13v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </span>
                <span class="text-lg font-bold tracking-tight" style="color: var(--brand-ink);">SiHR</span>
            </div>

            <div class="max-w-[380px] w-full mx-auto lg:mx-0 my-auto">
                {{-- Heading --}}
                <h1 class="text-[38px] sm:text-[44px] leading-[1.1] font-extrabold tracking-tight" style="color: var(--brand-ink);">
                    Halo,<br>Selamat Datang
                </h1>
                <p class="mt-3 text-[15px] text-gray-500">
                    Senang bertemu Anda lagi — masuk untuk melanjutkan
                </p>

                {{-- Alert error --}}
                @if (isset($errors) && $errors->any())
                    <div class="mt-5 rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Form --}}
                <form method="POST" action="{{ route('login') }}" class="mt-7">
                    @csrf

                    <input
                        type="email"
                        name="email"
                        placeholder="Alamat email"
                        value="{{ old('email') }}"
                        required autofocus
                        class="field w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-[15px] text-gray-800 placeholder-gray-400 shadow-sm"
                    >

                    <input
                        type="password"
                        name="password"
                        placeholder="Kata sandi"
                        required
                        class="field mt-4 w-full rounded-xl border border-gray-200 bg-white px-4 py-3.5 text-[15px] text-gray-800 placeholder-gray-400 shadow-sm"
                    >

                    <div class="mt-4 flex items-center justify-between">
                        <label class="flex items-center gap-2.5 text-[13px] text-gray-500 select-none">
                            <input type="checkbox" name="remember" class="chk" {{ old('remember') ? 'checked' : '' }}>
                            Ingat saya
                        </label>
                        <a href="#" class="text-[13px] font-medium text-gray-600 hover:text-[color:var(--brand-600)]">
                            Lupa kata sandi?
                        </a>
                    </div>

                    <button type="submit"
                        class="btn-brand mt-8 inline-flex items-center justify-center rounded-xl px-10 py-3 text-[15px] font-semibold text-white">
                        Masuk
                    </button>
                </form>
            </div>
        </div>

        {{-- ================= RIGHT : ANIMASI ================= --}}
        <div class="hidden lg:block p-3">
            <div class="illustration-panel relative h-full w-full min-h-[600px] rounded-[22px] overflow-hidden">
                <img
                    src="{{ asset('images/absensi-full-body-scan.gif') }}"
                    alt="Animasi pemindaian absensi badan penuh"
                    class="absolute inset-0 h-full w-full object-cover object-center select-none pointer-events-none"
                    draggable="false"
                >
                {{-- Penyelaras warna: dorong hue GIF agar sama persis dgn tema biru --}}
                <div class="absolute inset-0 mix-blend-color pointer-events-none" style="background: var(--brand-600); opacity: .16;"></div>
                <div class="absolute inset-0 pointer-events-none" style="background: linear-gradient(150deg, rgba(59,130,246,.10), rgba(67,56,202,.16));"></div>
            </div>
        </div>

    </div>
</div>
@endsection
