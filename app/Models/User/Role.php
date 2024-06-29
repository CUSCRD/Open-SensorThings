<?php


namespace App\Models\User;


use App\Constant\TablesName;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table=TablesName::Roles;
    protected $fillable=['roleName'];
    public function users(){
        return $this->belongsToMany('App\Models\User',TablesName::User_Role,'userId','roleId');
    }
}
