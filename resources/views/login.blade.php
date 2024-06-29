<html>
    <body>
    <div>
        @foreach($errors as $item)
            <label>{{$item}}</label>
            <br>
        @endforeach
    </div>
        <p>Đăng nhập</p>
        <form method="post" action="{{route('checkLogin')}}">
            @csrf
            <div class="form-group">
                <label>Tài khoản</label>
                <input type="text" name="username" value="{{old('username')}}"/>
            </div>
            <div class="form-group">
                <label>Mật khẩu</label>
                <input type="password" name="password">
            </div>
            <button type="submit">Đăng nhập</button>
        </form>
    </body>
</html>
