@extends('layouts.app')
@section('content')
<div id="viewdetail" class="detail-2">
   <div class="lang-info">
      <h1 class="m-bottom hidden-print center">Thông tin thẻ<br> {{ $o->fullname }} - {{ $o->phapdanh }} <br><span>{{ $o->hephai }}</span></h1>
   </div>
   <div class="clearfix"></div>
   <div id="test_in" class="cards">
      @include('official._card')
   </div>
</div>
@if ($o->pdf_url)
<div class="hidden-print file_pdf">
   <a href="{{ $o->pdf_url }}">đơn cấp giấy chứng nhận</a>
</div>
@endif
@include('official._card_css')
@vite('resources/js/card.js')
<script>
   // Thu thẻ (1068px) vừa chiều ngang màn hình, chủ yếu cho điện thoại quét QR.
   (function () {
      var box = document.getElementById('test_in');
      function fit() { box.style.zoom = Math.min(1, (document.documentElement.clientWidth - 16) / 1068); }
      fit(); window.addEventListener('resize', fit);
   })();
</script>
@endsection
