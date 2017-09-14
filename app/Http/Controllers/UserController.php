<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $parameters = $request->all();
        if ( isset($parameters['query']) && $parameters['query'] != '' ) {
            $query = $parameters['query'];
            $users = User::where('email', 'like', '%'.$query.'%')
                ->orWhere('full_name', 'like', '%'.$query.'%')
                ->orWhere('metadata', 'like', '%'.$query.'%')
                ->get();
        } else {
            $users = User::all();
        }
        return response()->json($users->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];
        try {
            $user = new User();
            $input = $user->trimData($request->all());
            if(!$user->isValid($input)) {
                $code = 422;
                $response['errors'] = $user->errors;
            } else {
                $code = 201;
                $input['key'] = bin2hex(openssl_random_pseudo_bytes(32));
                // The password should be stored hashed with a salt value
                $input['password'] = \Hash::make($input['password'].User::$SALT);

                $result = $user->curlExternalService($input['email'], $input['key']);
                if ($result['success'] && $result['data']['email'] == $input['email']) {
                    $input['account_key'] = $result['data']['account_key'];
                    \Log::info("user updated: ".$input['email']);
                }

                $userId = $user->insertGetId($input);
                $user = User::find($userId);

                $response = $user->toArray();
            }
        } catch (\Exception $e) {
            $code = 500;
            $response['errors'] = $e->getMessage();
        }

        return response()->json($response, $code);
    }
}
