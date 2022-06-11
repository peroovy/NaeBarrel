<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function upload(UploadedFile $file, string ...$folders): string
    {
        $upload_to = "uploads/" . join("/", $folders);

        $file->store($upload_to, "public");

        return Storage::url($upload_to . "/" . $file->hashName());
    }
}
