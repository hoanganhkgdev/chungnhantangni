<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Chứng Nhận Tăng Ni' }} - Chứng Nhận Tăng Ni GHPGVN</title>
    <link rel="icon" type="image/png" href="/favicon.png?v={{ filemtime(public_path('favicon.png')) }}">
    <link rel="shortcut icon" href="/favicon.ico?v={{ filemtime(public_path('favicon.ico')) }}">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v={{ filemtime(public_path('apple-touch-icon.png')) }}">
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        body { background: #f7f3ea; }
        .site-header { text-align: center; padding: 15px 0; }
        .site-header img { max-width: 100%; height: auto; }
        .site-footer { text-align: center; padding: 20px; color: #777; }
    </style>
</head>
<body>
<div class="container">
    <div class="site-header">
        <img src="/images/logo.png?v={{ filemtime(public_path('images/logo.png')) }}" style="height: 130px; width: auto;" alt="Chứng Nhận Tăng Ni GHPGVN">
    </div>
    @yield('content')
    <div class="site-footer">Copyright &copy; Chứng Nhận Tăng Ni. All rights reserved.</div>
</div>
</body>
</html>
