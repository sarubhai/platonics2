<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Comment;
use App\Category;
use App\Page;
use Auth;

class CommentController extends Controller
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
            'parent_id' => 'nullable|integer|exists:comments,id',
            'user_id' => 'bail|required|integer|exists:users,id',
            'commentable_id' => 'required|integer',
            'commentable_type' => 'required|max:30',
            'body' => 'required',
            'vote' => 'nullable|integer',
            'offensive_index' => 'nullable|integer',
        ];
    }

    /**
     * Returns a view to show the listing of all
     * the comments.
     */
    public function adminHome()
    {
        return view('admin.comments.home');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->request->add(['user_id' => Auth::id()]);

        $request->validate($this->getRules());

        $input = $request->input();
        $model = null;
        if ($input['commentable_type'] == 'App\Category')
            $model = Category::FindOrFail($input['commentable_id']);
        else
            $model = Page::FindOrFail($input['commentable_id']);

        $comment = $model->comments()->create($input);
        
        return response()->json($comment, 201);
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
        $comment = Comment::FindOrFail($id);
        $comment->fill($input)->save();
        
        return response()->json($comment, 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $comment = Comment::FindOrFail($id);
        $comment->delete();
        
        return response()->json($comment->id);
    }
}
