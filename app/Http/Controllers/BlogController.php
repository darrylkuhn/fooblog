<?php namespace Fooblog\Http\Controllers;

use Fooblog\Http\Requests;
use Fooblog\Http\Controllers\Controller;
use Fooblog\Blog;
use Illuminate\Http\Request;
use Input;

class BlogController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $count = Input::get('count', 10);

        $blogs = Blog::query()->take($count)->get();

        return response()->json( $blogs->toArray() );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        $blog = new Blog;
        $blog->user_id = \Auth::user()->id; 
        $blog->text = Input::get('text');
        $blog->save();

        return response()
            ->json( $blog->toArray() )
            ->setStatusCode( 201 );
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $blog = Blog::findorfail( $id );

        return response()->json( $blog->toArray() );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $blog = Blog::findorfail($id);
        $blog->text = Input::get('text');
        $blog->save();
        
        return response()->json( $blog->toArray() );
    }
}
