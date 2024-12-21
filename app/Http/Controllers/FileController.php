<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class FileController extends Controller
{
    // ファイルアップロード
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 最大100MB
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $filePath = $uploadedFile->store('uploads', 's3');

        // ZIP圧縮（オプション）
        $zipPath = null;
        
        $zip = new ZipArchive();
        $zipName = Str::random(10) . '.zip';
        $zipFullPath = storage_path('app/uploads/' . $zipName);

        if ($zip->open($zipFullPath, ZipArchive::CREATE) === TRUE) {
            $zip->addFile($uploadedFile->getRealPath(), $originalName);
            $zip->close();
            $zipPath = Storage::disk('s3')->putFile('uploads/zips', new \Illuminate\Http\File($zipFullPath));
            unlink($zipFullPath);
        }

        // ダウンロードリンクの生成
        $downloadLink = Str::random(32);

        $file = File::create([
            'original_name' => $originalName,
            'file_path' => $filePath,
            'zip_path' => $zipPath,
            'download_link' => $downloadLink,
        ]);

        return response()->json([
            'message' => 'File uploaded successfully',
            'download_link' => url('/api/download/' . $downloadLink),
        ]);
    }

    // ファイルダウンロード
    public function download($link)
    {
        // データベースからリンクに対応するファイル情報を取得
        $file = File::where('download_link', $link)->first();

        // ファイルが見つからない場合は404エラー
        if (!$file) {
            abort(404, 'File not found');
        }

        // S3ストレージにファイルが存在しない場合
        if (!Storage::disk('s3')->exists($file->file_path)) {
            abort(404, 'File not found on storage');
        }

        // S3から一時リンクを生成
        $url = Storage::disk('s3')->temporaryUrl($file->file_path, now()->addMinutes(5));

        return redirect($url);
    }
}
