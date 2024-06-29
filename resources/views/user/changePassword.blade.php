<html>
    <body>
    <div>
        @foreach($errors as $item)
            <label>{{$item}}</label>
            <br>
        @endforeach
    </div>
    <h2>Đổi mật khẩu</h2>
    <form method="post" action="{{route('changePwd')}}">
        @csrf
        <label>
            <span>Mật khẩu hiện tại:</span>
            <input name="password" type="password">
        </label>
        <br>

        <label>
            <span>Mật khẩu mới:</span>
            <input name="newPassword" type="password">
        </label>
        <br>

        <label>
            <span>Nhập lại mật khẩu mới:</span>
            <input name="rePassword" type="password">
        </label>
        <br>

        <br>
        <button type="submit">Xác nhận</button>
    </form>
    </body>
</html>
