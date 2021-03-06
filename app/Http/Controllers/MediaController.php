<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Media;
use Auth;

class MediaController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     */
    public function __construct()
    {
        $this->middleware('permissions');
    }
    
    private function getRules() 
    {
        return [
            'user_id' => 'bail|required|integer|exists:users,id',
            'base_path' => 'required|max:255',
            'filename' => 'required|max:30',
            'name' => 'required|max:60',
            'type' => 'required|max:10',
            'size' => 'nullable|integer',
            'optimized' => 'in:Y,N|max:1',
        ];
    }

    /**
     * Returns a view to show the listing of all
     * the media.
     */
    public function adminHome()
    {
        return view('admin.media.home');
    }

    /**
     * Returns a view to be used by both create and
     * edit purpose for a single media.
     */
    private function form($id = null)
    {
        return view('admin.media.form', compact('id'));
    }

    /**
     * Returns an empty view for single media
     * creation purpose
     */
    public function create()
    {
        return $this->form();
    }

    /**
     * Returns a view for a single media
     * edit purpose
     */
    public function edit($media)
    {
        return $this->form($media);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $media = Media::all();

        return response()->json([
            'length' => count($media),
            'data' => $media
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->input();
        $uploadedFile = request()->file('file');
        $name = $input['name'];
        
        try {
            $media = Media::storeMedia($uploadedFile, $name);
            return response()->json($media, 201);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $media = Media::FindOrFail($id);
        
        return response()->json($media);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->request->add(['user_id' => Auth::id()]);
        
        $request->validate($this->getRules());

        $input = $request->input();      
        $media = Media::FindOrFail($id);
        $media->fill($input)->save();

        return response()->json($media, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $media = Media::FindOrFail($id);
        try {
            Media::destroyMedia($id);
            return response()->json($media->name);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()]);
        }
    }

    /**
     * Display the Absolute Path of the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function absolute($id)
    {
        $media = Media::FindOrFail($id);
        $path = $media->absolutePath();

        return response()->json(['path' => $path]);
    }

    /**
     * Display the Relative Path of the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function relative($id)
    {
        $media = Media::FindOrFail($id);
        $path = $media->relativePath();
        
        return response()->json(['path' => $path]);
    }

    /**
     * Optimization the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function optimize($id)
    {
        $media = Media::FindOrFail($id);
        $status = $media->optimize();
        //$status = Media::optimizeAll();
        
        return response()->json($status);
    }
}
