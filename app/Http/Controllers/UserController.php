<?php namespace Fooblog\Http\Controllers;

use Fooblog\Http\Requests;
use Fooblog\Http\Controllers\Controller;

use Illuminate\Http\Request;

use Fooblog\User;
use Auth;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store()
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findorfail( self::getId($id) );

        return response()->json( $user->toArray() );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return Response
     */
    public function update($id)
    {
        $user = User::findorfail( self::getId($id) );

        if ( Input::has('name') )
        {
            $user->name = Input::get('name');
        }

        if ( Input::has('email') )
        {
            $user->email = Input::get('email');
        }

        if ( Input::has('password') )
        {
            $user->password = Input::get('password');
        }

        return response()->json( $user->toArray() );
    }

    private static function getId($id)
    {
        if ( $id == 'me' )
        {
            $id = \Auth::user()->id;
        }

        return $id;
    }

}
