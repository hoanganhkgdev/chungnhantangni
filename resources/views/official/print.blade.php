<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Xuất ảnh thẻ ({{ $items->count() }})</title>
    <link rel="stylesheet" href="/css/bootstrap.min.css">
    <style>
        body { margin: 0; background: #f7f3ea; }
        .export-bar { padding: 12px; text-align: center; font: 15px sans-serif; background: #fff; border-bottom: 1px solid #ddd; position: sticky; top: 0; z-index: 5; }
        .export-bar button { padding: 8px 20px; margin-left: 10px; cursor: pointer; }
        .export-wrap { padding: 16px 0; }
    </style>
    @vite('resources/js/card.js')
</head>
<body>
<div class="export-bar">
    <span id="export-status">Đang chuẩn bị…</span>
    <button id="export-again" hidden>Tải lại</button>
</div>
<div class="export-wrap">
    <div id="export-root">
        @foreach ($items as $o)
            <div class="cards" data-card="{{ $o->sothe ?: $o->id }}">
                @include('official._card')
            </div>
        @endforeach
    </div>
</div>
@include('official._card_css')
</body>
</html>
