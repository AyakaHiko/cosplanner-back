<?php

namespace App\Http\Controllers;

use App\Services\Interfaces\IImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
   public function __construct(protected IImageService $imageService){
   }
   public function store(Request $request)
   {
       $request->validate([
           'title' => 'required',
           'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
           'path' => 'sometimes|string'
       ]);

       if (!$request->hasFile('image')) {
           return response()->json([
               'message' => 'No image file provided'
           ], 400);
       }

       $result = $this->imageService->upload(
           $request->file('image'),
           $request->title,
           $request->path ?? 'profile-photos'
       );

       if (!$result['success']) {
           return response()->json([
               'message' => 'Image upload failed',
               'error' => $result['error']
           ], 500);
       }

       return response()->json([
           'message' => 'Image uploaded successfully',
           'title' => $request->title,
           ...$result['data']
       ], 201);

   }
}
