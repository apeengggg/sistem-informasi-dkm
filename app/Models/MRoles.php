<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MRoles extends Model
{
    use HasFactory;
    protected $primaryKey = "role_id";
    protected $fillable = [
        "role_id", "role_name",
        "created_at", "created_by", "updated_at", "updated_by"
    ];

    public static function getAll(){
        return DB::table('m_roles as r')
        ->select('r.role_id as value', 'r.role_name as title')
        ->get();
    } 
}
