<?php

namespace App\Http\Controllers;

use App\Events\VerificationMail;
use App\Events\WelcomeMailEvent;
use App\Mail\WelcomeMail;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts = Post::where('user_id', auth()->user()->id);
        if (request()->has('status')) {
            $posts->where('status', request('status'));
        }
        if (request()->has('search')) {
            $posts->where('title', 'LIKE', "%" . Request('search') . "%");
        }
        $posts = $posts->simplepaginate(5)->withQueryString();
        return view("post.index", [
            "data" => $posts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("post.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    
        $request->validate([
            'title' => 'required|max:100|unique:posts,title',
            'description' => 'required',
            'status' => 'required',
            'post_image' => 'image|max:2048|nullable'
        ]);
        if ($request->hasFile('post_image')) {

            // get filename with extention

            $filenameWithExt = $request->file('post_image')->getClientOriginalName();
            //get just file name

            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

            //get just  ext 

            $extension = $request->file('post_image')->getClientOriginalExtension();

            //filename to store 
            $fileNameToStore = $filename . "_" . time() . "." . $extension;
            // upload image
            $path = $request->file('post_image')->storeAs('public/post_image', $fileNameToStore);
        } else {
            $fileNameToStore = "noimage.jpg";
        }


        $post = new Post();

    
        $post->title = $request->title;
        $post->description = $request->description;
        $post->status = $request->status;
        $post->post_image = $fileNameToStore;
        $post->user_id = Auth::user()->id;
        $post->save();

        
        return redirect()->back()->with('message', 'Post Add successfully');
    }

    /**php 
     * Display the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function show(Post $post)
    {
        //
        return view('post.show', compact("post", "post"));
    }

    /**php 
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {

        // $data = Post::find($post);

        // return "$data";

        return view('post.edit', compact("post", "post"));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Post $post)
    {
        // dd($post);

        $request->validate([
            'title' => "required|max:100|unique:posts,title,$post->id",
            'description' => 'required',
            'status' => 'required',
            'post_image' => 'image|max:2048|nullable'

        ]);
        if ($request->hasFile('post_image')) {

            // get filename with extention

            $filenameWithExt = $request->file('post_image')->getClientOriginalName();
            //get just file name

            $filename = pathinfo($filenameWithExt, PATHINFO_FILENAME);

            //get just  ext 

            $extension = $request->file('post_image')->getClientOriginalExtension();

            //filename to store 
            $fileNameToStore = $filename . "_" . time() . "." . $extension;
            // upload image
            $path = $request->file('post_image')->storeAs('public/post_image', $fileNameToStore);

            Storage::delete('public/post_image/' . $post->post_image);
        }



        $post = Post::find($post->id);

        $post->title = $request->title;
        $post->description = $request->description;
        $post->status = $request->status;
        if ($request->hasFile('post_image')) {
            $post->post_image = $fileNameToStore;
        }
        $post->save();

        return redirect()->route('post.index')->with('message', 'Post Update successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Post  $post
     * @return \Illuminate\Http\Response
     */
    public function destroy(Post $post)
    {
        // dd($post);
        $post->delete();
        if ($post->post_image != 'noimage.jpg') {
            Storage::delete('public/post_image/' . $post->post_image);
        }
        return redirect()->route('post.index')->with('message', 'post  Delete successfully');
    }
}
