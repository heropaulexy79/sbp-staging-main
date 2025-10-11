<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    //

    public function store(Request $request)
    {

        $request->validate([
            "file" => "required|file|mimetypes:image/png,image/jpeg,image/gif,image/webp"
        ]);

        // dd($request->files);
        $file = $request->file('file');

        // media
        $savedFile = Storage::disk("public")->put("/", $file);


        return response()->json([
            "name" => $file->getClientOriginalName(),
            "url" => Storage::url($savedFile),
        ]);
    }


    public function destroy(Request $request)
    {
        // // dd($request->files);
        $url = $request->input('url');

        $trimmedPath = str_replace("/storage", "", $url);

        if (Storage::disk('public')->exists($trimmedPath)) {
            Storage::disk('public')->delete($trimmedPath);
        }

        return response()->json([
            "message" => [
                "status" => "success",
                "message" => "File has been deleted",

            ]
        ]);
    }
}
