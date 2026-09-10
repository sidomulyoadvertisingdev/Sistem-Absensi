<!doctype html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login')</title>

    {{-- Tailwind CDN (boleh diganti Vite kalau mau) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Palet biru — selaras dengan tema aplikasi */
            --brand-50:  #eff4ff;
            --brand-100: #dbe6ff;
            --brand-500: #3b82f6;
            --brand-600: #2563eb;
            --brand-700: #1d4ed8;
            --brand-ink: #101828;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #e9e9ec;
            -webkit-font-smoothing: antialiased;
        }

        .auth-card { box-shadow: 0 30px 80px -20px rgba(16, 24, 40, .28); }

        .field { transition: border-color .2s ease, box-shadow .2s ease; }
        .field:focus {
            border-color: var(--brand-600);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, .12);
            outline: none;
        }

        .btn-brand {
            background: var(--brand-600);
            transition: transform .15s ease, box-shadow .2s ease, background .2s ease;
            box-shadow: 0 12px 24px -8px rgba(37, 99, 235, .55);
        }
        .btn-brand:hover { background: var(--brand-700); transform: translateY(-1px); }
        .btn-brand:active { transform: translateY(0); }

        /* Checkbox biru */
        .chk {
            appearance: none; -webkit-appearance: none;
            width: 20px; height: 20px;
            border: 2px solid #cbd5e1;
            border-radius: 6px;
            display: inline-grid; place-content: center;
            cursor: pointer;
            transition: background .15s ease, border-color .15s ease;
        }
        .chk::before {
            content: "";
            width: 10px; height: 10px;
            transform: scale(0);
            transition: transform .12s ease-in-out;
            box-shadow: inset 1em 1em #fff;
            clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
        }
        .chk:checked { background: var(--brand-600); border-color: var(--brand-600); }
        .chk:checked::before { transform: scale(1); }

        .illustration-panel {
            /* Selaras dengan gradient di dalam GIF absensi-full-body-scan */
            background: linear-gradient(145deg, #3f7bf1 0%, #4048d8 55%, #3f36b6 100%);
        }
    </style>

    @stack('styles')
</head>
<body class="h-full">
    @yield('content')

    @stack('scripts')
</body>
</html>
