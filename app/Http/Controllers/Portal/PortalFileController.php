<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PortalFileController extends Controller
{
 /**
 * Files explicitly shared with the client (is_shared_with_client = true).
 */
 public function index()
 {
 $client = auth('portal')->user()->client;

 $files = $client->files()
 ->where('is_shared_with_client', true)
 ->latest()
 ->paginate(20);

 return view('portal.files', compact('files'));
 }

 public function download(File $file)
 {
 $client = auth('portal')->user()->client;

 if ($file->client_id !== $client->id || ! $file->is_shared_with_client) {
 abort(403);
 }

 if (! Storage::disk('tenant')->exists($file->file_path)) {
 abort(404, 'File no longer exists.');
 }

 return Storage::disk('tenant')->download($file->file_path, $file->original_name);
 }
}
