<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Users extends Model
{
    use HasFactory;
    protected $primaryKey = "user_id";
    protected $fillable = [
        "user_id", "role_id", "nip", "name", "email", "phone", "password", "status", "photo",
        "created_dt", "created_by", "updated_dt", "updated_by"
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
        ->select('u.user_id', 'u.name', 'u.email', 'u.password', 'u.role_id', 'r.role_name', 'u.photo')
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

    public static function deleteUser($user_id, $who){
        return DB::table('m_users as u')->where('user_id', $user_id)
                ->update([
                    'status' => 0, 
                    'updated_by' => $who,
                    'updated_dt' => date('Y-m-d H:i:s')
                ]);
    }

    public static function getUsers($param){
        $query = DB::table('m_users as u')
                ->join('m_roles as r', 'u.role_id', '=', 'r.role_id')
                ->select('u.user_id', 'u.role_id', 'u.nip', 'u.name', 'u.email', 'u.phone', 'u.photo', 'u.status', 'r.role_name')
                ->where('u.status', 1);

        if($param->userId){
            $query = $query->where('u.user_id', $param->userId);
        }
        
        if($param->roleId){
            $query = $query->where('r.role_id', $param->roleId);
        }

        if($param->name){
            $query = $query->whereRaw('LOWER(u.name) LIKE ?', ['%' . strtolower($param->name) . '%']);
        }

        if($param->email){
            $query = $query->whereRaw('LOWER(u.email) LIKE ?', ['%' . strtolower($param->email) . '%']);
        }

        if($param->orderBy){
            $dir = 'asc';
            if($param->dir && ($param->dir == 'asc' || $param->dir == 'desc')){
                $dir = $param->dir;
            }

            $query = $query->orderBy($param->orderBy, $dir);
        }

        $query = $query->paginate($param->perPage);

        return $query;
    }
}
