<!DOCTYPE html>
<html>
    <head>
        <title>ReadStats - Analyze your reading habits</title>
        <link href="{{ url('/') }}/css/readstats.css" rel="stylesheet">
        <link href="{{ url('/') }}/css/bargraph.css" rel="stylesheet">
    </head>
<body>
    <div class="wrap">
        <div class="content">
            @yield('content')
        </div>
    </div>
    @include('layouts.footer')
</body>
</html>
