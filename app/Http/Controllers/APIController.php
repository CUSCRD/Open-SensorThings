<?php

namespace App\Http\Controllers;


use Illuminate\Http\Response;
use App\Constant\TablesName;
use App\Constant\UserRolesFixedData;
use App\Http\Requests\ValidateRequest;
use App\Models\User\User;
use App\Models\User\UserRole;
use App\Models\Account\Account;
use App\Models\Thing\Thing;
use Exception;
use Illuminate\Auth\TokenGuard;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\ValidateController;

class APIController extends Controller
{
    public function register(Request $request)
    {
        $validationArray = [
            'username' =>  [
                'required',
                'min:4',
                Rule::unique(TablesName::Users, 'username')
                    ->where('username', $request->username),
            ],
            'password' => 'min:1',
            'phone' => [
                'required',
                'regex:/^(0?)(3[2-9]|5[6|8|9]|7[0|6-9]|8[0-6|8|9]|9[0-4|6-9])[0-9]{7}$/',
                Rule::unique(TablesName::Users, 'phone')
                    ->where('phone', $request->phone),
            ],
            'displayname' => 'required',
        ];
        $customMessages = [
            'username.min' => 'username must be 4 characters',
            'username.unique' => 'This username is existed',
            'username.required' => "username can not be empty",
            'password.min' => 'Password must be at least 8 characters',
            'password.same' => 'Retype Password is not true',
            'password.required' => "Password can not be empty",
            'displayname.required' => "Your name can not be empty",
            'phone.required' => "Phone can not be empty",
        ];
        $validator = Validator::make($request->all(), $validationArray, $customMessages);
        $isInvalid = $validator->fails();
        $isNotEncloseImage = $request->file('image');
        if ($isNotEncloseImage !== null) {
            $validator = ValidateController::validateImage($request);
            $isInvalid = $validator->fails();
        }
        if ($isInvalid) {
            return response()->json([
                $validator->messages()->all(),
            ])->setStatusCode(400);
        } else {
            $user = new User();
            $user->fill([
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'displayname' => $request->displayname,
                'phone' => $request->phone,
                'avatar' => $request->avatar ?? null
            ]);
            $res = $user->save();
            if (!$res)
                return redirect()->back()->with('errors', ["Cannot Register"])->withInput();
            $user = User::all()->where('username', '=', $request->username);
            if (count($user) === 1) {
                if ($isNotEncloseImage === false)
                    static::updateAvatar($request);
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
            return response()->json([
                'success' => true,
            ])->setStatusCode(200);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $jwt_token = null;
        if (!$jwt_token = Auth::attempt($credentials, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid username or Password',
            ], 401);
        }
        // get the user 
        $user = Auth::user();
        $jwt_token = Auth::user()->getRememberToken();
        // $jwt_token = Str::random(60);
        return response()->json([
            'success' => true,
            'token' => $jwt_token,
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        if (!User::checkToken($request)) {
            return response()->json([
                'message' => 'Sorry, somethong went wrong!!!',
                'success' => false,
            ], 422);
        } else {
            DB::table(TablesName::Users)
                ->where('remember_token', $request->token)
                ->update(['remember_token' => null]);

            return response()->json([
                'success' => true,
                'message' => 'User logged out successfully!!!'
            ], 200);
        }
    }

    public function getCurrentUser(Request $request)
    {
        if (!User::checkHeaderToken($request)) {
            return response()->json([
                'message' => 'Sorry, somethong went wrong!!!',
                'success' => false,
            ], 422);
        }

        $user = DB::table(TablesName::Users)
            ->where('remember_token', $request->token)
            ->get()
            ->firstOrFail();


        return response()->json([
            'message' => $user,
            'success' => true,
        ], 200);
    }
    public function getUser(Request $request)
    {
        if (!User::checkToken($request)) {
            return response()->json([
                'message' => 'Sorry, somethong went wrong!!!',
                'success' => false,
            ], 422);
        }

        $user = DB::table(TablesName::Users)
            ->where('remember_token', $request->token)
            ->get()
            ->firstOrFail();

        return $user;
    }
    public function getThings(Request $request)
    {
        $usersID = User::getCurrentUserID($request);
        $query = User::select(TablesName::THING, "id_user", "=", $usersID);
        // return  $query;
        return response()->json([
            'data' => $query,
            'success' => true,
        ], 200);
    }
    //resetpassword start
    public function resetPassword(Request  $request)
    {
        $validationArray = [
            'password' => 'min:8|different:newPassword',
            'newPassword' => 'required_with:rePassword|same:rePassword',
            'rePassword' => 'min:8|required',
        ];
        $validator = Validator::make($request->all(), $validationArray);
        if ($validator->fails()) {
            $response = $validator->messages()->all();
            return response()->json([
                'message' => $response,
                'success' => false,
            ], 400);
        }

        $dbPassword = $this->getUser($request)->password;
        $result = Hash::check($request['password'], $dbPassword);
        if (!$result) {
            return response()->json([
                'message' => 'Wrong password',
                'success' => false,
            ], 401);
        } else {
            DB::table(TablesName::Users)->where('remember_token', $request->token)
                ->update(['password' => Hash::make($request['newPassword'])]);
            return response()->json([
                'message' => 'reset password successfully',
                'success' => true,
            ], 200);
        }
    }
    //resetpassword end

    // update function start
    public function updateInformation(Request $request)
    {
        $validationArray = [
            'phone' => [
                'regex:/^(0?)(3[2-9]|5[6|8|9]|7[0|6-9]|8[0-6|8|9]|9[0-4|6-9])[0-9]{7}$/',
                Rule::unique(TablesName::Users, 'phone')
                    ->where('phone', $request->phone),
            ],
            'image' => [
                'image',
                'file_extension:jpeg,png',
                'mimes:jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:2048',
            ],
            'displayname' => [
                'min:1|max:100'
            ]
        ];
        $customMessages = [];
        $validator = Validator::make($request->all(), $validationArray, $customMessages);
        if ($validator->fails()) {
            return response()->json([
                $validator->messages()->all(),
            ])->setStatusCode(400);
        }
        $user = $this->getUser($request);
        $data = [];
        if (isset($request->phone)) {
            $data['phone'] = $request->phone;
        };
        if (isset($request->displayname)) {
            $data['displayname'] = $request->displayname;
        };
        if (empty($data))
            return response()->json([
                'success' => false,
                'message' => 'Cannot update!',
            ], 400);

        $updatedUser = User::where('remember_token', $request->token)->update($data);
        $user =  User::find($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Information has been updated successfully!',
            'user' => $user
        ]);
    }

    private function validateImage(Request $request)
    {
        $validationArray = [
            'image' => [
                'required',
                'image',
                // 'file_extension:jpeg,png',
                'mimes:jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:2048',
            ]
        ];
        $customMessages = [];
        $validator = Validator::make($request->all(), $validationArray, $customMessages);
        return $validator;
    }
    public function updateAvatar(Request $request)
    {
        // $validationArray = [
        //     'image' => [
        //         'required',
        //         'image',
        //         // 'file_extension:jpeg,png',
        //         'mimes:jpeg,png',
        //         'mimetypes:image/jpeg,image/png',
        //         'max:2048',
        //     ]
        // ];
        // $customMessages = [];
        // $validator = Validator::make($request->all(), $validationArray, $customMessages);
        // $validator = static::validateImage($request);
        // if ($validator->fails()) {
        //     return response()->json([
        //         $validator->messages()->all(),
        //     ])->setStatusCode(400);
        // }
        $path = $request->file('image')->store('images', 's3');
        Storage::disk('s3')->setVisibility($path, 'public');
        DB::table(TablesName::Users)
            ->where('remember_token', $request->token)
            ->update(['avatar' => Storage::disk('s3')->url($path)]);
        return response()->json([
            'success' => true,
            'message' => 'Information has been updated successfully!',
            'path' => $path,
        ]);
    }

    public function uploadImage(Request $request)
    {
        $path = $request->file('image')->store('images', 's3');
        Storage::disk('s3')->setVisibility($path, 'public');
        DB::table(TablesName::THING)
            ->where('id_user', $request->id_user)
            ->where('id', $request->id_things)
            ->update(['avt_image' => Storage::disk('s3')->url($path)]);
        return response()->json([
            'success' => true,
            'message' => 'Thing avt has been upload successfully!',
            'path' => $path,
        ]);
    }
    // update function end
}
