<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return PostResource::collection(Post::with('author')->paginate(5));

        // $posts = Post::all();
        // return response()->json([
        //     'message' => 'List of posts_V1',
        //     'data' => $posts
        // ], 200);
        // return response()->json([
        //     'message' => 'List of posts_V1',
        //     'data' => [
        //         'id' => 1,
        //         'title' => 'Post Title',
        //         'body' => 'Post content.'
        //     ]        
        // ])
        // ->header('Test', 'Batex')
        // ->header('Test2', 'Don')
        // ->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        // $data = $request->all(); // ['title' => 'Post Title', 'content' => 'Post content.']
        // $data = $request->only(['title', 'body']);
        $data = $request->validated();
        // $data = $request->validate([
        //     'title' => 'required|string|max:255',
        //     'body' => 'required|string|min:2'
        // ]);
        // return $data;
        $data['author_id'] = 2; // Simulate authenticated user ID

        $post = Post::create($data);

        // return $data;
        return response()->json(
            // [
            // 'message' => 'Post created successfully',
            // 'id' => 1,
            // 'title' => $data['title'],
            // 'body' => $data['body']
            // ]
           new PostResource($post), 201);
        // ->setStatusCode(201)
        ;
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id)
    public function show(Post $post)
    {
        // $post = Post::find($id);
        // if (!$post) {
        //     return response()->json(['message' => 'Post not found'], 404);
        // }

        // $post = Post::findOrFail($post);

        return response()->json([
            // 'message' => 'Post details_V1',
            new PostResource($post)
            // 'id' => $post->id,
            // 'title' => $post->title,
            // 'body' => $post->body
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string|min:2'
        ]);

        $post->update($data);
        return new PostResource($post);
        // return response()->json([
        //     'message' => 'Post updated successfully',
        //     'id' => 1,
        //     'title' => 'Post Title',
        //     'body' => 'Post content.'
        //     ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete(Post::class);
        return response()->noContent();
        // return response()->json(['message' => 'Post deleted successfully']);
    }
}
