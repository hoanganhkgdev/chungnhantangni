@php $qrUrl = rtrim(config('app.url'), '/') . $o->public_path; @endphp
      <div class="card-matsau">
         <img src="/uploads/official/the-mat-sau.png" />
         <table class="table-1">
            <tbody>
               <tr>
                  <td>
                     <img style="position: absolute; margin-top: 38px; margin-left: 840px;" width="180" height="180" data-qr="{{ $qrUrl }}" src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrUrl) }}" />
                     <ul class="ul-1">
                        <li>Chứng nhận: <span>{{ $o->phapdanh }}</span></li>
                        <li>Thế danh: <span>{{ $o->fullname }}</span></li>
                        <li>Ngày/Nơi sinh: <span>{{ $o->ngaysinh }} - {{ $o->noisinh }}</span></li>
                        <li>CCCD Số: <span>{{ $o->cccd }}</span> - Cấp ngày: <span>{{ $o->ngaycapcccd }}</span></li>
                        <li><span class="tk">Thọ Tỳ kheo (ni) giới: </span><span style="text-transform: capitalize">{{ $o->thogioi }} - {{ $o->part?->title }}</span></li>
                        <li>Nơi tu học: <b>{!! $o->address_html !!}</b></li>
                     </ul>
                  </td>
               </tr>
            </tbody>
         </table>
         <table class="table-2">
            <tbody>
               <tr>
                  <td width="40%">
                     <ul class="ul-3">
                        <li>
                           <img src="{{ $o->image_url }}" title="{{ $o->fullname }}" />
                        </li>
                     </ul>
                  </td>
                  <td>
                     <ul class="ul-4">
                        <li style="font-style: italic; font-size: 32px;">Ngày {{ $o->ngaycap_ngay }} Tháng {{ $o->ngaycap_thang }} Năm {{ $o->ngaycap_nam }}</li>
                        <li><span>TM. BAN THƯỜNG TRỰC</span></li>
                        <li><span>CHỦ TỊCH</span></li>
                     </ul>
                  </td>
               </tr>
            </tbody>
         </table>
         <img src="/uploads/official/con-dau-05.png" />
      </div>
      <div class="card-mattruoc">
         <img src="/uploads/official/the-mat-truoc-01.png" />
         <table class="table-3">
            <tbody>
               <tr>
                  <td>
                     <ul class="ul-5">
                        <li class="cn"><span>{{ $o->card_title }}</span></li>
                     </ul>
                  </td>
               </tr>
            </tbody>
         </table>
         <table class="table-4">
            <tbody>
               <tr>
                  <td width="50%">
                     <ul class="ul-6">
                        <li>{{ $o->sothe }}</li>
                     </ul>
                  </td>
                  <td>
                     <ul class="ul-7">
                        <li>{{ $o->sochungnhan }}/CNTN.{{ $o->ngaycap_nam }}</li>
                     </ul>
                  </td>
               </tr>
            </tbody>
         </table>
      </div>
