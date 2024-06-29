{{--điền lại kết quả sau khi thất bại--}}
{{--https://laracasts.com/discuss/channels/general-discussion/keeping-input-on-form-validation-request--}}
<html>
    <body>
    @if(\App\Http\Controllers\Home::minOrMod(Illuminate\Support\Facades\Auth::user()['username'])==false)
        @php
            header("Location: " . Illuminate\Support\Facades\URL::to('/'), true, 302);
            exit();
        @endphp
    @endif
    <div>
        @foreach($errors as $item)
            <label>{{$item}}</label>
            <br>
        @endforeach
    </div>
        <h2>Tạo tài khoản</h2>
        <form method="post" action="{{route('save_user')}}">
            @csrf
            <label>
                <span>Tên tài khoản:</span>
                <input type="text" name="username"  value="{{old('username')}}">
            </label>
            <br>
            <label>
                <span>Mật khẩu:</span>
                <input type="password" name="password">
            </label>
            <br>
            <LABEL>
                <span>Nhập lại mật khẩu:</span>
                <input type="password" name="r_pwd">
            </LABEL>
            <br>
            <fieldset>
                <legend ><span>Chức năng</span></legend>
                <ul>
                    @foreach($roles as $role)
                        <li>
                            <label>
                                <input type="checkbox" value="{{$role->roleId}}" {{ (is_array(old('roles')) && in_array($role->roleId, old('roles'))) ? ' checked' : '' }} name="roles[]">
                                {{$role->roleName}}
                            </label>
                        </li>
                    @endforeach
                </ul>

            </fieldset>

            <br>
            <button type="submit">Thêm</button>
        </form>
    </body>
</html>
