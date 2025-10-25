<!doctype html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>@yield('title', 'เข้าสู่ระบบ')</title>
    <link rel="icon" href="{{ asset('tailadmin-assets/images/favicon.ico') }}">
    <link href="{{ asset('tailadmin/style.css') }}" rel="stylesheet">
    @stack('styles')
    <style>
      body { min-height: 100vh; }
    </style>
    </head>
<body class="bg-gray-50 dark:bg-gray-900">
  <main class="min-h-screen flex items-center justify-center">
    @yield('content')
  </main>
  <script src="{{ asset('tailadmin/bundle.js') }}"></script>
  <script src="{{ asset('vendor/sweetalert2/sweetalert2.all.min.js') }}"></script>
  @if(session('status'))
    <script>
      window.addEventListener('DOMContentLoaded', function(){
        Swal.fire({
          icon: 'info',
          title: 'แจ้งเตือน',
          text: @json(session('status')),
          confirmButtonColor: '#3b82f6'
        });
      });
    </script>
  @endif
  @stack('scripts')
</body>
</html>
