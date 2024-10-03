<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MUsers extends Model
{
    use HasFactory;
    protected $primaryKey = "user_id";
    protected $fillable = [
        "user_id", "role_id", "nip", "name", "email", "phone", "password", "status", "photo",
        "created_at", "created_by", "updated_at", "updated_by"
    ];

    public static function getUserFromEmail($email){
        return DB::table('m_users as u')
        ->join('m_roles as r', 'u.role_id', '=', 'r.role_id')
        ->select('u.user_id', 'u.name', 'u.email', 'u.password', 'u.role_id', 'r.role_name', 'u.photo')
        ->where('u.email', $email)
        ->first();
    } 

    public static function getUserFromUserId($user_id){
        return DB::table('m_users as u')
        ->join('m_roles as r', 'u.role_id', '=', 'r.role_id')
        ->select('u.user_id', 'u.name', 'u.email', 'u.password', 'u.role_id', 'r.role_name', 'u.photo', 'u.nip', 'u.phone')
        ->where('u.user_id', $user_id)
        ->first();
    }

    public static function getUserFromEmailPhoneNip($email, $phone, $nip){
        return DB::table('m_users as u')
        ->select('u.user_id', 'u.email', 'u.phone', 'u.nip')
        ->where('u.email', $email)
        ->orWhere('u.phone', $phone)
        ->orWhere('u.nip', $nip)
        ->get();
    }

    public static function getRoleFromRoleId($role_id){
        return DB::table('m_roles as r')
        ->select('r.role_id', 'r.role_name')
        ->where('r.role_id', $role_id)
        ->first();
    }

    public static function validateEmailByUserId($user_id, $email){
        return DB::table('m_users as u')
        ->select('u.user_id')
        ->where('u.email', $email)
        ->where('u.user_id', '<>' , $user_id)
        ->first();
    }

    public static function validateNipByUserId($user_id, $nip){
        return DB::table('m_users as u')
        ->select('u.user_id')
        ->where('u.nip', $nip)
        ->where('u.user_id', '<>' , $user_id)
        ->first();
    }

    public static function validatePhoneByUserId($user_id, $phone){
        return DB::table('m_users as u')
        ->select('u.user_id')
        ->where('u.phone', $phone)
        ->where('u.user_id', '<>' , $user_id)
        ->first();
    }


    public static function deleteUser($user_id, $who){
        return DB::table('m_users as u')->where('user_id', $user_id)
                ->update([
                    'status' => 0, 
                    'updated_by' => $who,
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
    }

    public static function getUsers($param){
        $query = DB::table('m_users as u')
                ->join('m_roles as r', 'u.role_id', '=', 'r.role_id')
                ->select('u.user_id', 'u.role_id', 'u.nip', 'u.name', 'u.email', 'u.phone', 'u.photo', 'u.status', 'r.role_name')
                ->where('u.status', $param->status);

        if($param->userId){
            $query = $query->where('u.user_id', $param->userId);
        }
        
        if($param->roleId && $param->roleId != 'null'){
            $query = $query->where('r.role_id', $param->roleId);
        }

        if($param->name && $param->name != 'null'){
            $query = $query->whereRaw('LOWER(u.name) LIKE ?', ['%' . strtolower($param->name) . '%']);
        }

        if($param->email && $param->email != 'null'){
            $query = $query->whereRaw('LOWER(u.email) LIKE ?', ['%' . strtolower($param->email) . '%']);
        }

        if($param->orderBy){
            $dir = 'asc';
            if($param->dir && ($param->dir == 'asc' || $param->dir == 'desc')){
                $dir = $param->dir;
            }

            $query = $query->orderBy($param->orderBy, $dir);
        }

        // dd($param->status);
        // dd($query->toSql());

        $query = $query->paginate($param->perPage);

        return $query;
    }

    public static function getMetaUsers(){
        $totalUsers = DB::table('m_users')->count(); // Total users with status 1 or 0
        $activeUsers = DB::table('m_users')->where('status', 1)->count(); // Users with status 1
        $inactiveUsers = DB::table('m_users')->where('status', 0)->count(); // Users with status 0

        return [
            'meta_users' => [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'inactive_users' => $inactiveUsers
            ]
        ];
    }
}
