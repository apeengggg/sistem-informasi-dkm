<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Utils\ResponseUtil;
use App\Models\MUsers;
use App\Models\MRoles;
use App\Utils\StringUtil;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Storage;

class UserApiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        try{
            $validator = Validator::make($request->all(), [
                'userId' => 'min:1|string',
                'roleId' => 'min:1|string',
                'nip' => 'min:1|max:50|string',
                'email' => 'string',
                'orderBy' => 'string|required|in:user_id,role_id,nip,name,email,phone,role_name',
                'dir' => 'min:3|max:3|string|in:asc,desc|required',
                'perPage' => 'numeric|required',
                'status' => 'numeric|in:1,0',
            ],[
                'userId.min' => 'User Id Minimal 1 Character',
                'userId.string' => 'User Id Must Be String',
                'roleId.min' => 'Role Id Minimal 1 Character',
                'roleId.string' => 'Role Id Must Be String',
                'nip.min' => 'NIP Minimal 1 Character',
                'nip.max' => 'NIP Maximal 50 Character',
                'nip.string' => 'NIP Must Be String',
                'email.string' => 'Email Must Be String',
                'orderBy.string' => 'Order By Must Be String',
                'orderBy.in' => 'Order Is Not Valid Column',
                'orderBy.required' => 'Order is Required',
                'dir.min' => 'Dir Minimal 3 Character',
                'dir.max' => 'Dir Maximal 3 Character',
                'dir.string' => 'Dir Must Be String',
                'dir.in' => 'Dir Value Is Not Valid Value',
                'dir.required' => 'Dir is Required',
                'perPage.number' => 'PerPage Must Be Number',
                'perPage.required' => 'PerPage is Required',
                'status.in' => 'Status Is Not Valid Value',
            ]);
    
            //Send failed response if request is not valid
            if ($validator->fails()) {
                $errorMessages = StringUtil::ErrorMessage($validator);
                return ResponseUtil::BadRequest($errorMessages);
            }
    
            $results = MUsers::getUsers($request);
            foreach($results as $r){
                $r->photo = env('APP_URL').'/storage/'.$r->photo;
            }
            $meta_user = MUsers::getMetaUsers();

            return ResponseUtil::Ok("Successfully Get Data", $results, $meta_user);
        }catch(\Exception $e){
            return ResponseUtil::InternalServerError($e);
        }
    }

    public function getById(Request $request, $id)
    {   
        try{
            $validator = Validator::make($request->all(), [
                'userId' => 'min:1|string',
            ],[
                'userId.min' => 'User Id Minimal 1 Character',
                'userId.string' => 'User Id Must Be String'
            ]);
    
            //Send failed response if request is not valid
            if ($validator->fails()) {
                $errorMessages = StringUtil::ErrorMessage($validator);
                return ResponseUtil::BadRequest($errorMessages);
            }
    
            $results = MUsers::getUserFromUserId($id);
            $results->photo = env('APP_URL').'/storage/'.$results->photo;

            return ResponseUtil::Ok("Successfully Get Data", $results);
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
        try{
            $user_id =  Uuid::uuid4()->toString();
            if (!Storage::disk('public')->exists('foto-profile')) {
                Storage::disk('public')->makeDirectory('foto-profile');
            }

            $validator = Validator::make($request->all(), [
                'roleId' => 'max:50|string|required',
                'nip' => 'max:50|string|required',
                'name' => 'max:100|string|required',
                'email' => 'max:100|email|required',
                'phone' => 'max:12|string|required',
                'password' => 'min:4|string|required',
                'photo' => 'required|string',
            ],[
                'roleId.max' => 'Role Id Maximal 1 Character',
                'roleId.string' => 'Role Id Must Be String',
                'roleId.required' => 'Role Id Is Required',
                'nip.max' => 'NIP Maximal 50 Character',
                'nip.string' => 'NIP Must Be String',
                'nip.required' => 'NIP Is Required',
                'name.max' => 'Name Maximal 100 Character',
                'name.string' => 'Name Must Be String',
                'name.required' => 'Name Is Required',
                'email.max' => 'Email Maximal 100 Character',
                'email.email' => 'Email Must Be Valid Email',
                'email.required' => 'Email Is Required',
                'phone.max' => 'Phone Maximal 15 Character',
                'phone.string' => 'Phone Must Be A String',
                'phone.required' => 'Phone Is Required',
                'password.max' => 'Password Minimal 15 Character',
                'password.string' => 'Password Must Be A String',
                'photo.required' => 'Photo Is Required',
                'photo.string' => 'Photo Must Be A String',
            ]);

            if ($validator->fails()) {
                $errorMessages = StringUtil::ErrorMessage($validator);
                return ResponseUtil::BadRequest($errorMessages);
            }
            
            $mime_type = ['image/jpeg', 'image/jpg', 'image/png'];
            if(!in_array($request->photo_mime_type, $mime_type)){
                return ResponseUtil::BadRequest('File not allowed');
            }

            // Decode the Base64 string
            $files = base64_decode($request->photo, true);

            // Check for decoding errors
            if ($files === false) {
                return ResponseUtil::InternalServerError('Decoding base64 failed');
            }

            $fileSize = strlen($files);

            if($fileSize > 1048576){
                return ResponseUtil::BadRequest('File size more than 1MB');
            }

            $fileName = $user_id . "_" . $request->photo_name;
            Storage::put("public/foto-profile/{$fileName}", $files);
            
            $path = 'foto-profile/'.$fileName;

            $validatePhoneNumberFormat = StringUtil::validateIndonesianPhoneNumber($request->phone);
            if(!$validatePhoneNumberFormat){
                return ResponseUtil::BadRequest('Phone Number is Not Valid');
            }

            $validateEmailPhoneNip = MUsers::getUserFromEmailPhoneNip($request->email, $request->phone, $request->nip);
            if($validateEmailPhoneNip){
                return ResponseUtil::BadRequest('Bad Request');
            }

            $validateRoleId = MUsers::getRoleFromRoleId($request->role_id);
            if($validateRoleId){
                return ResponseUtil::BadRequest('Bad Request');
            }
            

            $data = [
                'user_id' => $user_id,
                'role_id' => $request->roleId,
                'nip' => $request->nip,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'status' => 1,
                'photo' => $path,
                'created_by' => 'SYS'
            ];

            MUsers::create($data);
            
            return ResponseUtil::Ok('Successfully created', null);
        }catch(\Exception $e){
            dd($e);
            return ResponseUtil::InternalServerError($e);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try{
            $validator = Validator::make($request->all(), [
                'userId' => 'max:50|string|required',
                'roleId' => 'max:50|string|required',
                'nip' => 'max:50|string|required',
                'name' => 'max:100|string|required',
                'email' => 'max:100|email|required',
                'phone' => 'max:12|string|required',
            ],[
                'userId.max' => 'User Id Maximal 1 Character',
                'userId.string' => 'User Id Must Be String',
                'userId.required' => 'User Id Is Required',
                'roleId.max' => 'Role Id Maximal 1 Character',
                'roleId.string' => 'Role Id Must Be String',
                'roleId.required' => 'Role Id Is Required',
                'nip.max' => 'NIP Maximal 50 Character',
                'nip.string' => 'NIP Must Be String',
                'nip.required' => 'NIP Is Required',
                'name.max' => 'Name Maximal 100 Character',
                'name.string' => 'Name Must Be String',
                'name.required' => 'Name Is Required',
                'email.max' => 'Email Maximal 100 Character',
                'email.email' => 'Email Must Be Valid Email',
                'email.required' => 'Email Is Required',
                'phone.max' => 'Phone Maximal 15 Character',
                'phone.string' => 'Phone Must Be A String',
                'phone.required' => 'Phone Is Required',
            ]);

            if ($validator->fails()) {
                $errorMessages = StringUtil::ErrorMessage($validator);
                return ResponseUtil::BadRequest($errorMessages);
            }
            
            $user = MUsers::where('user_id', $request->userId)->first();
            if(!$user){
                return ResponseUtil::BadRequest("Bad Request user");
            }

            if($user->email != $request->email){
                $user_email = MUsers::validateEmailByUserId($request->userId, $request->email);
                if($user_email)
                    return ResponseUtil::BadRequest('Bad Request Email');
            }

            if($user->phone != $request->phone){
                $validatePhoneNumberFormat = StringUtil::validateIndonesianPhoneNumber($request->phone);
                if(!$validatePhoneNumberFormat)
                    return ResponseUtil::BadRequest('Phone Number is Not Valid');

                $user_phone = MUsers::validatePhoneByUserId($request->userId, $request->phone);
                if($user_phone)
                    return ResponseUtil::BadRequest('Bad Request Phone');
            }

            $validateRoleId = MUsers::getRoleFromRoleId($request->roleId);
            if(!$validateRoleId)
                return ResponseUtil::BadRequest('Bad Request Role');

            $data = [
                'user_id' => $request->userId,
                'role_id' => $request->roleId,
                'nip' => $request->nip,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => 1,
                'updated_by' => 'SYS',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if($request->password){
                if(strlen($request->password) < 4){
                    return ResponseUtil::BadRequest('Password must be at least 4 character');
                }
                $data['password'] = bcrypt($request->password);
            }

            if($request->photo){
                $mime_type = ['image/jpeg', 'image/jpg', 'image/png'];
                if(!in_array($request->photo_mime_type, $mime_type)){
                    return ResponseUtil::BadRequest('File not allowed');
                }
    
                // Decode the Base64 string
                $files = base64_decode($request->photo, true);
        
                // Check for decoding errors
                if ($files === false) {
                    return ResponseUtil::InternalServerError('Decoding base64 failed');
                }
        
                $fileSize = strlen($files);
        
                if($fileSize > 1048576){
                    return ResponseUtil::BadRequest('File size more than 1MB');
                }
                $fileName = $request->userId . "_" . $request->photo_name;
                Storage::put("public/foto-profile/{$fileName}", $files);
                $path = 'foto-profile/'.$fileName;
    
                if (Storage::disk('public')->exists($user->photo)) {
                    Storage::disk('public')->delete($user->photo);
                }

                $data['photo'] = $path;
            }

            $user->update($data);
            
            return ResponseUtil::Ok('Successfully created', null);
        }catch(\Exception $e){
            dd($e);
            return ResponseUtil::InternalServerError($e);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {   
        try{
            $validator = Validator::make($request->all(), [
                'userId' => 'min:1|string|required',
            ],[
                'userId.min' => 'User Id Minimal 1 Character',
                'userId.string' => 'User Id Must Be String',
                'userId.required' => 'User Id Is Required',
            ]);
    
            //Send failed response if request is not valid
            if ($validator->fails()) {
                $errorMessages = StringUtil::ErrorMessage($validator);
                return ResponseUtil::BadRequest($errorMessages);
            }

            if($request->userId == $request->attributes->get('user_id')){
                return ResponseUtil::BadRequest('You cant delete your own account');
            }
            
            $get_user = MUsers::getUserFromUserId($request->userId);
            if(!$get_user){
                return ResponseUtil::BadRequest('Bad Request');
            }
            
            MUsers::deleteUser($request->userId, $request->attributes->get('user_id'));
            return ResponseUtil::Ok('Successfully Deleted', null);
        }catch(\Exception $e){
            return ResponseUtil::InternalServerError($e);
        }
    }
}
