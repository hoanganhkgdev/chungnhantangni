<style>
   @media print {
   
         .hidden-print {
            display: none;
         }
         
         .cn{
            color: #ff0000 !important;
            text-shadow: -2px -2px 0 #ffffff, 2px -2px 0 #ffffff, -2px 2px 0 #ffffff, 2px 2px 0 #ffffff !important;
         }

   }
   
   @page {
         size: A5 landscape;
         margin: 0;
   }
   
   @font-face {
         font-family: avo;
         src: url('/fonts/Avo.ttf') format('truetype');
   }
   
   @font-face {
         font-family: utmavo;
         src: url('/fonts/UTMAvo.ttf') format('truetype');
   }
   

   /* Thẻ cố định 1068x680, căn giữa, thu nhỏ vừa màn hình bằng JS */
   .cards ul { list-style: none; }
   .card-matsau, .card-mattruoc { position: relative; width: 1068px; height: 680px; margin: 0 auto 40px; }
   .card-matsau img, .card-mattruoc img { position: absolute; }
   .card-matsau > img:first-child, .card-mattruoc > img:first-child { top: 0; left: 0; width: 1068px; height: 680px; }
   .card-matsau .table-1, .card-matsau .table-2, .card-mattruoc .table-3, .card-mattruoc .table-4 { left: 0; }
   .card-matsau .table-1 { top: 0; }
   .card-matsau .table-2 { top: 0; }
   .cards table { margin-bottom: 0; background: transparent; }
   .cards td { padding: 0; border: 0; }
   .cards { transform-origin: top center; }
   

   .center {
         text-align: center;
   }
   
   .isprint {
         padding: 0 !important;
   }
   
   /* Mặt Trước */
   .card-mattruoc {
         font-family: utmavo !important;
   }
   
   .card-mattruoc img {
         position: absolute;
   }
   
   .card-mattruoc .table-3 {
         position: absolute;  
         font-size: 26px;
         width: 1068px;
         margin-top: 480px;
   }
   
   .card-mattruoc .table-3 .ul-5 {
         text-align: center;
         padding: 0;
         margin: 0;
   }
   
   .card-mattruoc .table-3 .ul-5 li {
         font-weight: bold;
         color: #ff0000;
         font-size: 60px;
         text-shadow: -2px -2px 0 #ffffff, 2px -2px 0 #ffffff, -2px 2px 0 #ffffff, 2px 2px 0 #ffffff;
   }
   
   .card-mattruoc .table-4 {
         position: absolute;
         width:1068px;
         font-size: 36px;
         margin-top: 605px;
   }
   
   .card-mattruoc .table-4 .ul-6 {
         margin-left: 100px;
         font-family: utmavo !important;
         font-weight: bold;
   }
   
   .card-mattruoc .table-4 .ul-7 {
         margin-left: 130px;
         font-family: utmavo !important;
         font-weight: bold;
   }
   
   /* Mặt Sau */
   .card-matsau {
         font-family: Times New Roman !important;
   }
   
   .card-matsau img {
         position: absolute;
   }
   
   .card-matsau .table-1 {
         position: absolute;
         font-size: 26px;
         width: 1068px;
   }
   
   .card-matsau .table-1 span {
         text-transform:uppercase;
         font-size: 36px;
         font-weight: bold;
   }
   
   .card-matsau .table-1 .ul-1 {
         padding-left: 50px;
         padding-top: 93px;
         line-height: 40px;
         padding-right: 40px;
   }
   
   .card-matsau .table-1 .ul-2 {
         padding-left: 43px;
         margin-top: -142px;  
   }
   
   .card-matsau .table-2 {
         position: absolute;  
         width: 1068px;
         margin-top:380px
   }
   
   .card-matsau .table-2 img {
         width: 242px;
         height: 242px;
   }
   
   .card-matsau .table-2 .ul-3 {
         padding: 0;
         margin-top: -41px;
         margin-left: 52px;
   }
   
   .card-matsau .table-2 .ul-4 {
         padding: 0;
         text-align: center;
         font-size: 26px;
         margin-top: 13px;
   }
   
   .card-matsau .table-2 span {
         font-weight: bold;
   }
   
   .card-matsau b {
         font-size: 36px;
   }
   
   
   
   
   
   .file_pdf {
         text-align: center;
         padding: 30px;
         font-size: 20px;
         text-transform: uppercase;
   }
   
   .file_pdf a{
         color: brown;
         border: solid 1px;
         background: bisque;
         border-radius: 50px;
         padding: 15px;
   }
   
   .file_pdf a:hover{
         color: cornsilk;
   }
   
   .card-matsau .table-1 span.tk {
         text-transform: none;
         font-size: 26px;
         font-weight: normal;
   }
   
   
   

   /* In: mỗi mặt một trang A5 ngang (thẻ 1068px thu về ~794px), mặt trước in trước */
   .cards { display: flex; flex-direction: column; }
   @media print {
         body { background: #fff !important; }
         .cards { zoom: 0.7435 !important; }
         .card-mattruoc, .card-matsau { margin: 0 !important; break-after: page; page-break-after: always; }
         .cards .card-mattruoc { order: -1; }
   }
</style>
