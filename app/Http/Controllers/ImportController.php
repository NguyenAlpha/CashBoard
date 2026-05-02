<?php

namespace App\Http\Controllers;

use App\Helpers\StoreContext;
use App\Jobs\ProcessImportJob;
use App\Models\ImportBatch;
use App\Services\ImportService;
use App\Services\XlsxParser\XlsxParserFactory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ImportController extends Controller
{
    public function index(): View
    {
        $batches = ImportBatch::where('store_id', StoreContext::id())
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('import.index', compact('batches'));
    }

    public function upload(Request $request, ImportService $service): RedirectResponse|View
    {
        $request->validate([
            'file'   => ['required', 'file', 'mimes:csv,xlsx,xls', 'max:10240'],
            'source' => ['required', 'in:bank_qr,wallet,card'],
        ]);

        $file       = $request->file('file');
        $ext        = strtolower($file->getClientOriginalExtension());
        $sourceType = $ext === 'csv' ? 'csv' : 'xlsx';

        // Lưu file vào private storage
        $path = $file->storeAs(
            'imports',
            now()->format('Ymd_His') . '_' . $file->getClientOriginalName(),
            'local'
        );

        $batch = ImportBatch::create([
            'store_id'    => StoreContext::id(),
            'filename'    => $path,
            'source_type' => $sourceType,
            'status'      => 'pending',
        ]);

        // Chỉ đọc 15 dòng đầu — đủ để detect metadata ngân hàng mà không load cả file
        try {
            $previewRows = $service->readRows(storage_path('app/private/' . $path), $sourceType, limit: 15);
        } catch (\Throwable $e) {
            $batch->update(['status' => 'failed', 'error_log' => [['message' => $e->getMessage()]]]);

            return redirect()->route('import.index')
                ->with('error', 'Không đọc được file: ' . $e->getMessage());
        }

        // Nếu nhận dạng được format ngân hàng: lưu class parser vào column_mapping,
        // dispatch job xử lý nền, bỏ qua trang mapping thủ công
        $parser = (new XlsxParserFactory())->detect($previewRows);
        if ($parser !== null) {
            $batch->update([
                'column_mapping' => [
                    'auto_parser' => get_class($parser), // ImportService dùng để khởi tạo đúng parser
                    'source'      => $request->input('source'),
                ],
            ]);
            ProcessImportJob::dispatch($batch->id);

            return redirect()->route('import.index')
                ->with('success', 'Đã nhận dạng định dạng ' . $parser->bankLabel() . ', đang xử lý...');
        }

        // Không nhận dạng được: hiển thị trang mapping thủ công
        // previewRows[0] là dòng header của file (chuẩn: dòng 1 = tên cột)
        return view('import.map', [
            'batch'   => $batch,
            'headers' => $previewRows[0] ?? [],
            'source'  => $request->input('source'),
        ]);
    }

    public function map(Request $request, ImportBatch $batch): RedirectResponse
    {
        $this->authorizeBatch($batch);

        $request->validate([
            'source'       => ['required', 'in:bank_qr,wallet,card'],
            'col_amount'   => ['required', 'string'],
            'col_date'     => ['required', 'string'],
            'col_ref'      => ['nullable', 'string'],
            'col_note'     => ['nullable', 'string'],
        ]);

        $batch->update([
            'column_mapping' => [
                'source'       => $request->input('source'),
                'amount'       => $request->input('col_amount'),
                'transacted_at'=> $request->input('col_date'),
                'reference_id' => $request->input('col_ref') ?: null,
                'note'         => $request->input('col_note') ?: null,
            ],
        ]);

        ProcessImportJob::dispatch($batch->id);

        return redirect()->route('import.index')
            ->with('success', 'Đang xử lý file import. Kết quả sẽ hiển thị sau ít phút.');
    }

    public function destroy(ImportBatch $batch): RedirectResponse
    {
        $this->authorizeBatch($batch);

        $batch->transactions()->delete();
        $batch->delete();

        return redirect()->route('import.index')
            ->with('success', 'Đã xoá lịch sử import và ' . $batch->imported_count . ' giao dịch liên quan.');
    }

    private function authorizeBatch(ImportBatch $batch): void
    {
        if ($batch->store_id !== StoreContext::id()) {
            abort(403);
        }
    }
}
