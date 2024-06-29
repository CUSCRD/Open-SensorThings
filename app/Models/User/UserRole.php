<?php


namespace App\Models\User;


use App\Constant\TablesName;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    protected $table=TablesName::User_Role;
    protected $fillable=[
        'userId',
        'roleId'
    ];
//    public function hasManyUser(){
//        return $this->hasMany('App\Models\User','type','id');
//    }
}
