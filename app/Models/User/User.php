<?php

namespace App\Models\User;

use App\Constant\TablesName;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;

class User extends Authenticatable
{
    protected $primaryKey = 'id';
    protected $fillable = [
        'username',
        'password',
        'displayname',
        'phone'
    ];
    protected $hidden = [
        'password', 'remember_token',
    ];
    protected $table = TablesName::Users;
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role', TablesName::User_Role, 'userId', 'roleId');
    }
    public static function checkToken($token)
    {
        $user = User::all()->where('remember_token', '=', $token->token)->toArray();
        if (count($user) === 1) {
            return true;
        }
        return false;
    }
    public static function checkHeaderToken($token)
    {
        $user = User::all()->where('remember_token', '=', $token->header('token'))->toArray();
        if (count($user) === 1) {
            return true;
        }
        return false;
    }

    public static function getCurrentUserID($request)
    {
        if (!User::checkHeaderToken($request)) {
            return response()->json([
                'message' => 'Token is required'
            ], 422);
        }

        $user = DB::table(TablesName::Users)
            ->where('remember_token', $request->header('token'))
            ->get(['id'])
            ->firstOrFail();

        return $user->id;
    }

    public static function getCurrentUser($request)
    {
        if (!User::checkToken($request)) {
            return response()->json([
                'message' => 'Token is required'
            ], 422);
        }

        $user = JWTAuth::parseToken()->authenticate();
        return $user;
    }
    public static function joinTo($nametable, $first, $operation, $second)
    {
        $query = DB::table(TablesName::Users)
            ->join($nametable, $first, $operation, $second)
            ->select($nametable . '.*')
            ->get();
        dd($query->toArray());
    }

    public static function select($nametable, $first, $operation, $second)
    {
        $query = DB::table($nametable)
            ->select()
            ->where($first, $operation, $second)
            ->get();
        return ($query->toArray());
    }
}
