<?php





namespace App\Http\Controllers\Admin;

use App\Post;
use App\Tag;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use Illuminate\Support\Facades\Storage;

use App\Mail\CreatePostMail;
use Illuminate\Support\Facades\Mail;

use App\Category;








class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = [
            'posts' => Post::with('category')->paginate(3),
        ];

        $tags = Tag::all();

        return view('admin.posts.index', $data, compact('tags'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // // $data = [
        // //     'categories' => Category::All(),
            
        // // ];

        $categories = Category::all();
        $tags = Tag::all();


        return view('admin.posts.create', compact('categories', 'tags'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $request->all();

        // validazione data

        $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $newPost = new Post();

        // if img is uploaded in the input 
        if (array_key_exists('image', $data) ) {
            $cover_url = Storage::put('post_covers' , $data['image']);
            $data['cover'] = $cover_url;
        }

        
        $newPost->fill($data);
        $newPost->save();

       if (array_key_exists('tags', $data)){
        $newPost->tags()->sync($data['tags']);
    
       }
       
    //    invio mail
       $mail = new CreatePostMail();
       $email_utente = Auth::user()->email;
       Mail::to($email_utente)->send($mail);

        return redirect()->route('admin.posts.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $singolo_post = Post::findOrFail($id);


        return response()->view('admin.posts.show', compact('singolo_post'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $post = Post::findOrFail($id);

        $categories = Category::All();

        $tags = Tag::all();

        return response()->view('admin.posts.edit', compact('post', 'categories', 'tags'));
       
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
        $data = $request->all();

        $singoloPost = Post::findOrFail($id);

        $singoloPost->update($data);

        if(array_key_exists('tags', $data)){
            $singoloPost->tags()->sync($data['tags']);

        }else {
            $singoloPost->tags()->sync([]);
        }

        return redirect()->route('admin.posts.show', $singoloPost->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $singoloPost = Post::findOrFail($id);

        if ($singoloPost->cover) {
            Storage::delete($singoloPost->cover);
        }


        $singoloPost->tags()->sync([]);
        $singoloPost->delete();
        

        

        return redirect()->route('admin.posts.index');
    }
}
