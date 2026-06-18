<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'GDTC AP Tracker')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <header class="sticky top-0 z-30 border-b border-slate-200 bg-white/95 shadow-sm backdrop-blur">
        <div class="mx-auto flex max-w-7xl items-center gap-3 px-4 py-3">
            <img src="{{ asset('images/logo.jpg') }}" alt="GDTC" class="h-11 w-16 rounded-xl object-contain" onerror="this.style.display='none';this.nextElementSibling.style.display='grid'">
            <span class="hidden h-11 w-11 place-items-center rounded-xl bg-blue-700 font-black text-white">GD</span>
            <div class="min-w-0 flex-1">
                <div class="truncate text-lg font-black leading-tight text-blue-800">GDTC AP Tracker</div>
                <div class="truncate text-xs font-medium text-slate-500">Quản lý thi công WiFi AP</div>
            </div>
            @yield('header-actions')
        </div>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-5 pb-28">
        @if (session('success'))
            <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800">
                <div class="font-black">Vui lòng kiểm tra lại</div>
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
