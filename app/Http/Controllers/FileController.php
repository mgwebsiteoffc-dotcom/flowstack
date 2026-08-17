<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\File;
use App\Models\FileFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
 public function index(Request $request)
 {
 $tenant = app('currentTenant');

 $folderId = $request->input('folder_id');
 $clientId = $request->input('client_id');
 $search = $request->input('search');
 $type = $request->input('type');

 $currentFolder = $folderId ? FileFolder::find($folderId) : null;
 $clientId = $clientId ?? $currentFolder?->client_id;

 $foldersQuery = FileFolder::withCount('files');

 if ($clientId) {
 $foldersQuery->where('client_id', $clientId);
 } else {
 $foldersQuery->whereNull('client_id');
 }

 $folders = $foldersQuery->orderBy('name')->get();

 $filesQuery = File::with('folder', 'client', 'uploader');

 if ($folderId) {
 $filesQuery->where('folder_id', $folderId);
 } elseif ($clientId) {
 $filesQuery->where('client_id', $clientId);
 } else {
 $filesQuery->whereNull('folder_id');
 }

 if ($search) {
 $filesQuery->where('original_name', 'like', "%{$search}%");
 }

 if ($type) {
 $filesQuery->where('extension', $type);
 }

 $files = $filesQuery->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

 $breadcrumbs = $this->breadcrumbs($currentFolder);

 $clients = Client::orderBy('company_name')->get();

 // Internal folders (no client).
 $internalFolders = FileFolder::whereNull('client_id')->orderBy('name')->get();

 $storageUsed = (int) File::sum('file_size');
 $storageLimit = (int) ($tenant->max_storage_gb ?? 10) * 1024 * 1024 * 1024;

 return view('files.index', compact(
 'folders', 'files', 'currentFolder', 'breadcrumbs', 'clients',
 'internalFolders', 'storageUsed', 'storageLimit', 'search', 'type'
 ));
 }

 public function upload(Request $request)
 {
 $tenant = app('currentTenant');

 $request->validate([
 'files' => ['required', 'array', 'max:10'],
 'files.*' => ['file', 'max:10240'],
 'folder_id' => ['nullable', 'exists:file_folders,id'],
 'client_id' => ['nullable', 'exists:clients,id'],
 'lead_id' => ['nullable', 'exists:leads,id'],
 'project_id' => ['nullable', 'exists:projects,id'],
 ]);

 $folder = $request->input('folder_id') ? FileFolder::find($request->input('folder_id')) : null;
 $clientId = $request->input('client_id') ?? $folder?->client_id;
 $leadId = $request->input('lead_id');
 $projectId = $request->input('project_id');

 foreach ($request->file('files') as $file) {
 $extension = strtolower($file->getClientOriginalExtension());

 if (! in_array($extension, File::ALLOWED_EXTENSIONS, true)) {
 return back()->with('error', 'File type .'.$extension.' is not allowed.');
 }

 // Validate the real MIME type too (extension spoofing guard).
 if (! $this->mimeAllowed($extension, $file->getMimeType())) {
 return back()->with('error', $file->getClientOriginalName().' content does not match its .'.$extension.' extension.');
 }

 $maxSize = $extension === 'mp4' ? File::MAX_VIDEO_SIZE : File::MAX_SIZE;

 if ($file->getSize() > $maxSize) {
 return back()->with('error', $file->getClientOriginalName().' exceeds the '.($maxSize / 1024 / 1024).' MB limit.');
 }

 $storedName = Str::uuid().'.'.$extension;
 $path = $leadId
 ? 'tenants/'.$tenant->id.'/leads/'.$leadId
 : ($projectId
 ? 'tenants/'.$tenant->id.'/projects/'.$projectId
 : 'tenants/'.$tenant->id.'/clients/'.($clientId ?? 'general').'/'.($folder?->name ?? 'root'));

 $storedPath = $file->storeAs($path, $storedName, 'tenant');

 File::create([
 'tenant_id' => $tenant->id,
 'folder_id' => $folder?->id,
 'client_id' => $clientId,
 'lead_id' => $leadId,
 'project_id' => $projectId,
 'original_name' => $file->getClientOriginalName(),
 'stored_name' => $storedName,
 'file_path' => $storedPath,
 'file_size' => $file->getSize(),
 'mime_type' => $file->getMimeType(),
 'extension' => $extension,
 'uploaded_by' => auth()->id(),
 ]);
 }

 return back()->with('success', 'Files uploaded.');
 }

 public function download(File $file)
 {
 if (! Storage::disk('tenant')->exists($file->file_path)) {
 abort(404, 'File no longer exists.');
 }

 return Storage::disk('tenant')->download($file->file_path, $file->original_name);
 }

 /**
 * Inline preview for images and PDFs (served through the controller, never
 * from the webroot).
 */
 public function preview(File $file)
 {
 if (! $file->isImage() && ! $file->isPdf()) {
 abort(415, 'This file type cannot be previewed.');
 }

 if (! Storage::disk('tenant')->exists($file->file_path)) {
 abort(404, 'File no longer exists.');
 }

 return Storage::disk('tenant')->response($file->file_path, $file->original_name, [
 'Content-Type' => $file->mime_type ?? 'application/octet-stream',
 'Content-Disposition' => 'inline',
 ]);
 }

 public function update(Request $request, File $file)
 {
 $validated = $request->validate([
 'original_name' => ['required', 'string', 'max:255'],
 'folder_id' => ['nullable', 'exists:file_folders,id'],
 'is_shared_with_client' => ['sometimes', 'boolean'],
 ]);

 $file->update($validated);

 return back()->with('success', 'File updated.');
 }

 /**
 * Create a share link with optional expiry.
 */
 public function share(Request $request, File $file)
 {
 $validated = $request->validate([
 'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:365'],
 'is_shared_with_client' => ['sometimes', 'boolean'],
 ]);

 $file->update([
 'share_token' => Str::random(64),
 'share_expires_at' => $validated['expires_in_days'] ? now()->addDays($validated['expires_in_days']) : null,
 'is_shared_with_client' => $request->boolean('is_shared_with_client', $file->is_shared_with_client),
 ]);

 return back()->with('success', 'Share link created: '.$file->shareUrl());
 }

 public function unshare(File $file)
 {
 $file->update(['share_token' => null, 'share_expires_at' => null]);

 return back()->with('success', 'Share link removed.');
 }

 public function destroy(File $file)
 {
 Storage::disk('tenant')->delete($file->file_path);
 $file->delete();

 return back()->with('success', 'File deleted.');
 }

 /**
 * Upload a new version (old version kept as parent_file).
 */
 public function uploadVersion(Request $request, File $file)
 {
 $request->validate(['file' => ['required', 'file', 'max:10240']]);

 $uploaded = $request->file('file');
 $extension = strtolower($uploaded->getClientOriginalExtension());

 if ($extension !== $file->extension) {
 return back()->with('error', 'New version must be the same file type (.'.$file->extension.').');
 }

 $storedName = Str::uuid().'.'.$extension;
 $storedPath = $uploaded->storeAs(dirname($file->file_path), $storedName, 'tenant');

 $old = $file->toArray();

 $file->update([
 'original_name' => $uploaded->getClientOriginalName(),
 'stored_name' => $storedName,
 'file_path' => $storedPath,
 'file_size' => $uploaded->getSize(),
 'mime_type' => $uploaded->getMimeType(),
 'version' => $file->version + 1,
 'parent_file_id' => $file->parent_file_id ?? $file->id,
 ]);

 // Keep the previous version row as history.
 $previous = File::create([
 'tenant_id' => $old['tenant_id'],
 'folder_id' => $old['folder_id'],
 'client_id' => $old['client_id'],
 'original_name' => $old['original_name'].' (v'.$old['version'].')',
 'stored_name' => $old['stored_name'],
 'file_path' => $old['file_path'],
 'file_size' => $old['file_size'],
 'mime_type' => $old['mime_type'],
 'extension' => $old['extension'],
 'version' => $old['version'],
 'parent_file_id' => $file->id,
 'uploaded_by' => auth()->id(),
 ]);

 return back()->with('success', 'Version '.$file->version.' saved. Previous version kept in history.');
 }

 public function storeFolder(Request $request)
 {
 $validated = $request->validate([
 'name' => ['required', 'string', 'max:255'],
 'client_id' => ['nullable', 'exists:clients,id'],
 'parent_folder_id' => ['nullable', 'exists:file_folders,id'],
 ]);

 $parent = $validated['parent_folder_id'] ?? null ? FileFolder::find($validated['parent_folder_id']) : null;

 FileFolder::create([
 'tenant_id' => app('currentTenant')->id,
 'client_id' => $validated['client_id'] ?? $parent?->client_id,
 'parent_folder_id' => $parent?->id,
 'name' => $validated['name'],
 'path' => trim(($parent?->path ?? 'clients/'.($validated['client_id'] ?? 'internal')).'/'.$validated['name'], '/'),
 'is_system_folder' => false,
 'created_by' => auth()->id(),
 ]);

 return back()->with('success', 'Folder created.');
 }

 /**
 * Public shared-file endpoint (token based, optional expiry).
 */
 public function shared(string $token)
 {
 $file = File::withoutGlobalScopes()->where('share_token', $token)->first();

 if (! $file) {
 abort(404, 'Share link not found.');
 }

 if ($file->share_expires_at && $file->share_expires_at->isPast()) {
 abort(410, 'This share link has expired.');
 }

 if (! Storage::disk('tenant')->exists($file->file_path)) {
 abort(404, 'File no longer exists.');
 }

 return Storage::disk('tenant')->download($file->file_path, $file->original_name);
 }

 /**
 * Extension -> allowed MIME prefixes. application/octet-stream is accepted
 * because some servers/proxies normalise unknown types to it.
 */
 protected function mimeAllowed(string $extension, ?string $mime): bool
 {
 if (! $mime || $mime === 'application/octet-stream') {
 return true;
 }

 $map = [
 'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
 'gif' => ['image/gif'], 'svg' => ['image/svg+xml'], 'webp' => ['image/webp'],
 'pdf' => ['application/pdf'],
 'doc' => ['application/msword'],
 'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
 'xls' => ['application/vnd.ms-excel'],
 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
 'csv' => ['text/csv', 'text/plain'],
 'txt' => ['text/plain'],
 'mp4' => ['video/mp4'],
 'zip' => ['application/zip', 'application/x-zip-compressed'],
 ];

 $allowed = $map[$extension] ?? [];

 if (empty($allowed)) {
 return true;
 }

 foreach ($allowed as $candidate) {
 if (str_starts_with($mime, $candidate)) {
 return true;
 }
 }

 return false;
 }

 protected function breadcrumbs(?FileFolder $folder): array
 {
 $crumbs = [];

 while ($folder) {
 array_unshift($crumbs, $folder);
 $folder = $folder->parent;
 }

 return $crumbs;
 }
}
