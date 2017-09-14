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
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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


    public static function updateAccountKeys() {
        $users = self::whereNull('account_key')->get();
        \Log::info("updateAccountKeys");
        \Log::info("trying to update ".count($users)." users");
        foreach ($users as $user) {
            $result = $user->curlExternalService($user->email, $user->key);
            if ($result['success'] && $result['data']['email'] == $user->email) {
                $user->account_key = $result['data']['account_key'];
                $user->save();
                \Log::info("user updated: ".$user->email);
            }
        }
    }

    public function curlExternalService($email, $key) {

        $url = 'https://account-key-service.herokuapp.com/v1/account';

        $data = json_encode( [
            "email" => $email,
            "key" => $key
        ] );

        $httpHeader = [
            "Content-Type: application/json",
            "Content-Length: ".strlen($data)
        ];

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $httpHeader);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        $response = curl_exec($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        \Log::info("curlExternalService");
        \Log::info($response);


        $return = [
            'success' => false,
            'data' => [],
        ];

        if ($info['http_code'] == 200) {
            $return['success'] = true;
            $return['data'] = json_decode($response, true);
        } else {
            \Log::error($info);
        }

//        echo '<pre>';
////        print_r($response);
////        echo '****'.PHP_EOL;
////        print_r($info);
////        echo '****'.PHP_EOL;
////        print_r($error);
//        print_r($return);
//        die;

        return $return;
    }

}
