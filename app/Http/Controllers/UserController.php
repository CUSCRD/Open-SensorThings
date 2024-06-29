<?php


namespace App\Http\Controllers;

use JWTAuth;
use Illuminate\Http\Response;
use App\Constant\TablesName;
use App\Constant\UserRolesFixedData;
use App\Models\User\User;
use App\Models\User\UserRole;
use App\Models\Account\Account;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function login_view()
    {
        return view('login');
    }

    public function adminlogin_view()
    {
        return view('adminlogin');
    }

    public function register_view()
    {

        return View::make('user.registerview');
    }
    public function checkLogin(Request $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        // $value = "token_smart";

        // if (Auth::attempt($credentials)) {
        //     setcookie("token_smart", $value, time() + 3600);
        //     return redirect()->route('index');
        // } else {
        //     return redirect()->back()->with('errors', ["Wrong password or Username"])->withInput();
        // }
        $jwt_token = null;
        if (!$jwt_token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email or Password',
            ], 401);
        }
        // get the user 
        $user = Auth::user();

        return response()->json([
            'success' => true,
            'token' => $jwt_token,
            'user' => $user
        ]);
    }

    public function checkAdminLogin(Request $request): RedirectResponse
    {
        $credentials = $request->only('username', 'password');
        $value = "token_smart";

        if (Auth::attempt($credentials)) {
            setcookie("token_smart", $value, time() + 3600);
            return redirect()->route('index');
        } else {
            return redirect()->back()->with('errors', ["Admin Wrong password or Username"])->withInput();
        }
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login');
    }

    public function getFormUser(): \Illuminate\Contracts\View\View
    {
        $strRole = 'select id as roleId, roleName from ' . TablesName::Roles;
        $roles = DB::select($strRole);
        return View::make('user.formUser')->with('roles', $roles);
    }

    public function getFormChangePwd()
    {
        return view('user.changePassword');
    }

    public function changePwd(Request  $request)
    {
        $user = Auth::user()['username'];
        logger($request['password']);
        $validationArray = [
            'password' =>  [
                'required'
            ],
            'newPassword' => 'min:3|required_with:rePassword|same:rePassword',
            'rePassword' => 'required'
        ];

        $dbPassword = DB::table(TablesName::Users)->where('username', '=', $user)->get("password")[0]->password;
        $result = Hash::check($request['password'], $dbPassword);
        if (!$result) {
            return redirect()->back()->with('errors', ['Sai mật khẩu']);
        } else {
            $validator = Validator::make($request->all(), $validationArray);
            if ($validator->fails()) {
                $response = $validator->messages()->all();
                return redirect()->back()->with('errors', $response)->withInput();
            } else {
                DB::table(TablesName::Users)->where('username', '=', $user)
                    ->update(['password' => Hash::make($request['newPassword'])]);
                return redirect('/');
            }
        }
    }
    public function changeRole(Request $request)
    {
        $strRole = 'select id as roleId, roleName from ' . TablesName::Roles;
        $roles = DB::select($strRole);
        $urDB = DB::table(TablesName::Users, 'u')->join(TablesName::User_Role . ' as ur', 'u.id', '=', 'ur.userId')
            ->where('u.username', '=', $request['u'])->get('ur.roleId');
        $ur = [];
        foreach ($urDB as $item) {
            array_push($ur, $item->roleId);
        }
        return View::make('user.changeRole')->with('roles', $roles)->with('userRoles', $ur);
    }

    public function changeRoleResult(Request $request)
    {
        $validationArray = [
            'roles' => 'required'
        ];
        $customMessages = [
            'roles.required' => 'Chọn ít nhất 1 vai trò'
        ];
        $validator = Validator::make($request->all(), $validationArray, $customMessages);
        if ($validator->fails()) {
            $response = $validator->messages()->all();
            return redirect()->back()->with('errors', $response)->withInput();
        } else {
            $user = User::all()->where('username', '=', $request['u']);
            $user = array_values($user->toArray());
            if (count($user) > 0) {
                $id = $user[0]['id'];
                //delete
                DB::table(TablesName::User_Role)->where('userId', '=', $id)->delete();
                //insert

                $roles = $request->input('roles');
                foreach ($roles as $role) {
                    $ur = new UserRole([
                        'userId' => $id,
                        'roleId' => $role
                    ]);
                    $ur->save();
                }
            }
            return redirect(route('userList'));
        }
    }

    public function deleteUser(Request $request)
    { {
            $user = $request['u'];
            $loginUser = Auth::user()['username'];
            if (Home::minOrMod($loginUser)) {
                DB::table(TablesName::Users)->where('username', '=', $user)->delete();
                return redirect(route('userList'));
            } else {
                return redirect('/');
            }
        }
    }

    public function registerUser(Request $request)
    {
        $validationArray = [
            'username' =>  [
                'required',
                'min:2',
                Rule::unique(TablesName::Users, 'username')
                    ->where('username', $request->username),
            ],
            'password' => 'min:3|required_with:r_pwd|same:r_pwd',
            'r_pwd' => 'required',
            'roles' => 'required'
        ];
        $customMessages = [
            //            'required' => 'Trường :attribute không được trống.',
            'username.min' => 'Tên tài khoản phải có ít nhất 2 ký tự',
            'username.unique' => 'Tên tài khoản đã được đăng ký, hãy chọn tên khác',
            'password.min' => 'Mật khẩu phải tối thiểu 3 ký tự',
            'password.same' => 'Mật khẩu xác nhận lại không đúng',
            'username.required' => 'Trường Tên tài khoản không được để trống',
            'password.required' => 'Trường mật khẩu không được để trống',
            'r_pwd.required' => 'Trường nhập lại mật khẩu không được để trống',
            'roles.required' => 'Chọn ít nhất 1 vai trò'
        ];

        $validator = Validator::make($request->all(), $validationArray, $customMessages);
        if ($validator->fails()) {
            $response = $validator->messages()->all();
            return redirect()->back()->with('errors', $response)->withInput();
        } else {
            $user = new User();
            $user->fill([
                'username' => $request->username,
                'password' => Hash::make($request->password)
            ]);
            $user->save();

            $user = User::all()->where('username', '=', $request->username);
            if (count($user) > 0) {

                $user = array_values($user->toArray());
                $id = $user[0]['id'];
                $roles = $request->input('roles');
                foreach ($roles as $role) {
                    $ur = new UserRole([
                        'userId' => $id,
                        'roleId' => $role
                    ]);
                    $ur->save();
                }
            }
            return response("Đã tạo thành công " . $request->username . "<br><a href='" . route('user_register_form') . "'>Đăng ký tài khoản</a>"
                . "<br><a href=' " . route('index') . "'>Quay về trang chủ</a>")->setStatusCode(200);
        }
    }
    public function registerForUser(Request $request)
    {
        $validationArray = [
            'username' =>  [
                'required',
                'min:4',
                Rule::unique(TablesName::Users, 'username')
                    ->where('username', $request->username),
            ],
            'password' => 'min:8', //|required_with:r_pwd|same:r_pwd',
            // 'r_pwd' => 'required',
            'phone' => [
                'required',
                'min:10',
                Rule::unique(TablesName::Users, 'phone')
                    ->where('phone', $request->phone),
            ],
            // 'r_pwd' => 'required',
            'displayname' => 'required',
        ];
        $customMessages = [
            //            'required' => 'Trường :attribute không được trống.',
            'username.min' => 'username must be 4 characters',
            'username.unique' => 'This username is existed',
            'username.required' => "username can not be empty",
            'password.min' => 'Password must be at least 8 characters',
            'password.same' => 'Retype Password is not true',
            'password.required' => "Password can not be empty",
            'displayname.required' => "Your name can not be empty",
            'phone.required' => "Phone can not be empty",
            // 'r_pwd.required' => "RetypePassword can not be empty",
        ];

        $validator = Validator::make($request->all(), $validationArray, $customMessages);
        if ($validator->fails()) {
            // $response = $validator->messages()->all();
            // return redirect()->back()->with('errors', $response)->withInput();
            return response()->json([
                $validator->messages()->all(),
            ])->setStatusCode(400);
        } else {
            $user = new User();
            $user->fill([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'displayname' => $request->displayname,
                'phone' => $request->phone
            ]);
            $res = $user->save();
            if (!$res)
                return redirect()->back()->with('errors', ["Cannot Register"])->withInput();
            $user = User::all()->where('username', '=', $request->username);
            if (count($user) > 0) {

                $user = array_values($user->toArray());
                $id = $user[0]['id'];
                $roles = array(3, 4, 5, 6);
                foreach ($roles as $role) {
                    $ur = new UserRole([
                        'userId' => $id,
                        'roleId' => $role
                    ]);
                    $ur->save();
                }
            }

            // return response("Successfully Register " . $request->username . "<br><a href='" . route('login') . "'>Login</a>"
            //     . "<br><a href=' " . route('register') . "'>Back</a>")->setStatusCode(200);
            return response()->json([
                'success' => true,
            ])->setStatusCode(200);
            // return $this->checkLogin($request);
        }
    }
    public function getUserList(): \Illuminate\Contracts\View\View
    {
        $strUsers = 'select username from ' . TablesName::Users;
        $users = DB::select($strUsers);
        return View::make('user.userList')->with('users', $users);
    }
}
