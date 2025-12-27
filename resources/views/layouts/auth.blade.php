<!doctype html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Login')</title>

    {{-- Tailwind CDN (boleh diganti Vite kalau mau) --}}
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body { box-sizing: border-box; }

        .login-container {
            background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 50%, #2563eb 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .card-shadow {
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .input-field {
            transition: all 0.3s ease;
        }

        .input-field:focus {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .login-button {
            transition: all 0.3s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
    </style>

    @stack('styles')
</head>
<body class="h-full">
    @yield('content')

    @stack('scripts')
</body>
</html>
