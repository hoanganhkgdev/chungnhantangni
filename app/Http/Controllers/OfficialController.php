<?php

namespace App\Http\Controllers;

use App\Models\Official;

class OfficialController extends Controller
{
    public function show(string $part, string $slug)
    {
        preg_match('/-(\d+)$/', $slug, $m);

        // Chỉ tra theo id (như hệ thống cũ) nên QR cũ luôn mở được dù alias/tên đã đổi.
        $official = Official::active()->with('part')->findOrFail((int) $m[1]);

        return view('official.show', ['o' => $official, 'title' => $official->id.'-'.$official->phapdanh]);
    }
}
