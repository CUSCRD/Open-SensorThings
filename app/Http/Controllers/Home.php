<?php


namespace App\Http\Controllers;

use App\Constant\TablesName;
use App\Constant\UserRolesFixedData;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Home extends Controller
{
    public function index()
    {
        return view('home');
    }

    public static function minOrMod(string $username): bool
    {
        $count = DB::table(TablesName::Users)->join(TablesName::User_Role, TablesName::User_Role . ".userId", '=', TablesName::Users . ".id")
            ->where(TablesName::Users . ".username", '=', $username)
            ->whereIn(TablesName::User_Role . ".roleId", [UserRolesFixedData::ADMIN['id'], UserRolesFixedData::MOD['id']])
            ->get();
        if (count($count) > 0) {
            return true;
        }
        return false;
    }
}
