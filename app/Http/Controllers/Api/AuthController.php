<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MUsers;
use App\Models\MPermissions;
use App\Utils\ResponseUtil;
use App\Utils\PermissionUtil;
use Illuminate\Support\Facades\Hash;
use App\Utils\JwtUtil;
use App\Utils\StringUtil;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    protected $jwtUtil;

    public function __construct(JwtUtil $jwtUtil)
    {
        $this->jwtUtil = $jwtUtil;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'email' => 'required|email'  ,
                'password' => 'required|string|min:4'
            ],[
                'email.required' => 'Email is Required',
                'password.required' => 'Password is Required',
                'password.string' => 'Password Must Be String',
                'password.min' => 'Password Must Be At Least 8 Character'
            ]);
    
            //Send failed response if request is not valid
            if ($validator->fails()) {
                $errorMessages = StringUtil::ErrorMessage($validator);
                return ResponseUtil::BadRequest($errorMessages);
            }

            $results = MUsers::getUserFromEmail($request->email);
            if($results == null){
                return ResponseUtil::Unauthorized("Login failed, either your User Id isn't registered in our system or your password is incorrect");
            }
            
            $compare = Hash::check($request->password, $results->password);
            if(!$compare){
                return ResponseUtil::Unauthorized("Login failed, either your User Id isn't registered in our system or your password is incorrect");
            }
            
            $permission = MPermissions::getPermissionById($results->role_id);
            // echo $permission;
            if(!$permission){
                return ResponseUtil::Unauthorized("Login failed, either your User Id isn't registered in our system or your password is incorrect");
            }
            
            $object_permission = PermissionUtil::createObjectPermission($permission);
            
            $payload_data = [
                'user_id' => $results->user_id,
                'role_id' => $results->role_id
            ];
            
            $results = [
                'user_id' => $results->user_id,
                'name' => $results->name,
                'foto_profile' => env('APP_URL').'/storage/'.$results->photo,
                'role_id' => $results->role_id,
                'role_name' => $results->role_name,
                'permission' => $object_permission,
                'token' => $this->jwtUtil->generateToken($payload_data, $results->role_id),
            ];


            return ResponseUtil::Ok("Successfully Login", $results);
        }catch(\Exception $e){
            return ResponseUtil::InternalServerError($e);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
