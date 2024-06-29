<html>
    <body>
    @if(\App\Http\Controllers\Home::minOrMod(Illuminate\Support\Facades\Auth::user()['username']))
        <a href="{{route('user_register_form')}}">Đăng ký tài khoản</a>
        <br>
        <a href="{{route('userList')}}">Danh sách tài khoản</a>
        <hr>
    @endif
        <a href="{{route('changePassword')}}">Đổi mật khẩu</a>
        <br>
        <a href="{{route('logout')}}">Đăng xuất</a>
    </body>
</html>
