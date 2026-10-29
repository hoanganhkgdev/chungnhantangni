<?php

namespace App\Http\Controllers;

use App\Models\Official;
use App\Support\TrinhKy;
use Illuminate\Http\Request;

class PrintController extends Controller
{
    /** In một hoặc nhiều thẻ: /admin-in-the?ids=1,2,3 (chỉ cho người đã đăng nhập admin). */
    public function __invoke(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $ids = collect(explode(',', (string) $request->query('ids')))->map(fn ($i) => (int) $i)->filter()->unique()->take(200);
        $items = Official::with('part')->whereIn('id', $ids)->orderBy('id')->get();
        abort_if($items->isEmpty(), 404);

        return view('official.print', ['items' => $items]);
    }

    /** Xuất file Word trình ký: /trinh-ky?ids=1,2,3 */
    public function trinhKy(Request $request)
    {
        abort_unless(auth()->check(), 403);

        $ids = collect(explode(',', (string) $request->query('ids')))->map(fn ($i) => (int) $i)->filter()->unique()->take(500);
        $items = Official::with('part')->whereIn('id', $ids)->orderBy('id')->get();
        abort_if($items->isEmpty(), 404);

        $name = $items->count() === 1 ? 'trinh-ky-'.$items->first()->slug.'.docx' : 'trinh-ky-'.$items->count().'-the.docx';

        return response()->download(TrinhKy::make($items), $name)->deleteFileAfterSend();
    }
}
