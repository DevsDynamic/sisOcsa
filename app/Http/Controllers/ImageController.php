<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class ImageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function showUploadForm()
    {
        return view('upload');
    }

    public function uploadImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imageName = time().'.'.$request->image->extension();
        $path = $request->image->storeAs('images', $imageName, 'public');

        $image = new Image;
        $image->filename = $imageName;
        $image->path = $path;
        $image->save();

        // Extraer texto de la imagen
        $ocr = new TesseractOCR(storage_path('app/public/' . $path));
        $text = $ocr->run();
return $text;
        // Guardar el texto extraído en la base de datos si es necesario
        $image->text_content = $text;
        $image->save();

        return back()->with('success', 'Imagen subida exitosamente.')->with('image', $imageName)->with('text', $text);
    }
}
