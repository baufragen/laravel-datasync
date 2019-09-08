<!DOCTYPE html>
<html>
    <head>
        <title>DataSync Dashboard</title>
        <link rel="stylesheet" href="{{ asset('/vendor/datasync/app.css') }}">
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>
        <script src="{{ asset('/vendor/datasync/app.js') }}"></script>
    </body>
</html>