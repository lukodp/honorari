@if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/icons.css', 'resources/css/app-shell.css'])
@endif
