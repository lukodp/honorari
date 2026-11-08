<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Obračun sudačkih honorara')</title>
    @include('partials.app-assets')
    @stack('page-styles')
</head>
<body style="--app-background-image: url({{ asset('images/background.png') }}); --app-header-background-image: url({{ asset('images/header-bg-obracun.png') }});" class="@yield('body-class')">
    @include('partials.app-header')

    <main class="app-main">
        @yield('content')
    </main>

    @include('partials.app-footer')
</body>
</html>
