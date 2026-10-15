<?php

namespace App\Http\Controllers;

use App\Models\Official;
use App\Models\Part;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Tương thích với /api_public.php của NukeViet (cùng action, tham số và cấu trúc JSON)
 * để các client đang gọi API cũ không phải sửa.
 */
class ApiPublicController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $cors = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'GET, POST, OPTIONS',
            'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
            'Access-Control-Max-Age' => '3600',
        ];

        if ($request->isMethod('OPTIONS')) {
            return response()->json(null, 200, $cors);
        }

        [$status, $message, $data] = match ($request->query('action', 'list')) {
            'list' => $this->list($request),
            'detail' => $this->detail($request),
            'search' => $this->search($request),
            'categories' => $this->categories(),
            'jobtitles' => ['success', 'Lấy chức danh thành công', []],
            'parts' => $this->parts(),
            'info' => $this->info(),
            default => ['error', 'Action không hợp lệ. Sử dụng action=info để xem danh sách actions có sẵn.', null],
        };

        return response()->json(
            ['status' => $status, 'message' => $message, 'data' => $data, 'timestamp' => time()],
            $status === 'error' ? 400 : 200,
            $cors,
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
        );
    }

    private function paginate($query, Request $request, string $message): array
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = max(1, (int) $request->query('per_page', 20));
        $total = (clone $query)->count();
        $items = $query->ordered()->with('part')->forPage($page, $perPage)->get()->map(fn ($o) => $this->format($o))->all();

        return ['success', $message, [
            'items' => $items,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int) ceil($total / $perPage),
            ],
        ]];
    }

    private function list(Request $request): array
    {
        $query = Official::active()->when((int) $request->query('part_id'), fn ($q, $id) => $q->where('part_id', $id));

        return $this->paginate($query, $request, 'Lấy danh sách thành công');
    }

    private function search(Request $request): array
    {
        $q = trim((string) $request->query('q', ''));
        $partId = (int) $request->query('part_id', 0);

        if ($q === '' && $partId === 0) {
            return ['error', 'Vui lòng nhập từ khóa tìm kiếm hoặc chọn chủ đề', null];
        }

        $query = Official::active()
            ->when($q !== '', fn ($query) => $query->where(fn ($w) => $w
                ->where('ho', 'like', "%$q%")
                ->orWhere('ten', 'like', "%$q%")
                ->orWhere('phapdanh', 'like', "%$q%")
                ->orWhere('sothe', 'like', "%$q%")))
            ->when($partId, fn ($query) => $query->where('part_id', $partId));

        return $this->paginate($query, $request, 'Tìm kiếm thành công');
    }

    private function detail(Request $request): array
    {
        $id = (int) $request->query('id', 0);
        if ($id <= 0) {
            return ['error', 'ID không hợp lệ', null];
        }

        $official = Official::active()->with('part')->find($id);
        if (! $official) {
            return ['error', 'Không tìm thấy dữ liệu', null];
        }

        return ['success', 'Lấy chi tiết thành công', $this->format($official) + ['custom_fields' => []]];
    }

    private function categories(): array
    {
        $counts = Official::active()->selectRaw('part_id, count(*) c')->groupBy('part_id')->pluck('c', 'part_id');

        $data = Part::where('status', 1)->orderBy('weight')->get()->map(fn ($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'alias' => $p->alias,
            'parentid' => $p->parent_id,
            'weight' => $p->weight,
            'count' => (int) ($counts[$p->id] ?? 0),
        ])->all();

        return ['success', 'Lấy danh mục thành công', $data];
    }

    private function parts(): array
    {
        $data = Part::where('status', 1)->orderBy('weight')->get()->map(fn ($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'alias' => $p->alias,
            'note' => $p->note,
            'status' => (int) $p->status,
        ])->all();

        return ['success', 'Lấy danh sách parts thành công', $data];
    }

    private function info(): array
    {
        return ['success', 'API đang hoạt động', [
            'version' => '1.0',
            'available_actions' => [
                'list' => 'Lấy danh sách officials (params: page, per_page, part_id)',
                'detail' => 'Lấy chi tiết một official (params: id)',
                'search' => 'Tìm kiếm officials (params: q, page, per_page, part_id)',
                'categories' => 'Lấy danh sách categories',
                'jobtitles' => 'Lấy danh sách job titles',
                'parts' => 'Lấy danh sách parts',
                'info' => 'Thông tin API',
            ],
        ]];
    }

    private function format(Official $o): array
    {
        return [
            'id' => $o->id,
            'sothe' => $o->sothe,
            'sochungnhan' => $o->sochungnhan,
            'phapdanh' => $o->phapdanh,
            'ho' => $o->ho,
            'ten' => $o->ten,
            'ngaysinh' => $o->ngaysinh,
            'noisinh' => $o->noisinh,
            'cccd' => $o->cccd,
            'ngaycapcccd' => $o->ngaycapcccd,
            'thogioi' => $o->thogioi,
            'diachi' => $o->diachi,
            'ngaycap_ngay' => $o->ngaycap_ngay,
            'ngaycap_thang' => $o->ngaycap_thang,
            'ngaycap_nam' => $o->ngaycap_nam,
            'image' => $o->image ?: null,
            'part_id' => $o->part_id,
            'part' => $o->part ? ['id' => $o->part->id, 'title' => $o->part->title, 'alias' => $o->part->alias] : null,
            'hephai' => $o->hephai,
            'jobtitle' => [],
            'addtime' => $o->created_at?->timestamp,
            'edittime' => $o->updated_at?->timestamp,
            'weight' => 0,
            'prior' => $o->prior,
            'status' => (int) $o->status,
        ];
    }
}
