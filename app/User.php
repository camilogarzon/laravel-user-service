<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    public static $SALT = "0b1db7f3c44b33a80381fc481dadeb2d";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'email', 'phone_number', 'full_name', 'password', 'key', 'account_key', 'metadata'];

    /**
     * The attributes that are accepted in API
     *
     * @var array
     */
    public $apiFields = [ 'email', 'phone_number', 'full_name', 'password', 'metadata' ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'id',
    ];

    /**
     * Rules to validate input
     *
     * @var array
     */
    public static $RULES = [
        'email'=>'required|max:200|email|unique:users',
        'phone_number'=>'required|max:20|unique:users',
        'full_name'=>'max:200',
        'password'=>'required|max:100',
        'metadata'=>'max:2000',
    ];

    /**
     * @var null
     */
    public $errors = [];

    /**
     * @param $data
     * @return array
     */
    public function trimData($data){
        $trimData = [];
        foreach($data as $key => $val){
            if(in_array($key, $this->apiFields)){
                $trimData[$key] = $val;
            }
        }
        return $trimData;
    }

    /**
     * @param $data
     * @param $rules
     * @return mixed
     */
    public function isValid($data) {
        $validation = \Validator::make($data, self::$RULES);
        $isValid = $validation->passes();
        if(!$isValid){
            $errors = $validation->messages()->toArray();
            foreach ($errors as $error) {
                $this->errors[] = $error[0];
            }
        }
        return $isValid;

    }

}
