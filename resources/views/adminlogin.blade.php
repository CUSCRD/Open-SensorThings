<html>

<head>
  <link rel="stylesheet" href="{{asset('css/login.css')}}">
</head>

<body>
  <script>
    function submitFormLogin() {
      document.getElementById("formLogin").submit();
    }
  </script>
  <div>
    @foreach ($errors as $error)
    <script>
      alert('{{$error}}');
    </script>
    @endforeach
  </div>

  <div class="login-box">
    <marquee style="color:red;font-size:30;"> This page for ADMIN </marquee>
    <h2>SignIn</h2>

    <form method="post" action="{{route('checkAdminLogin')}}" id="formLogin">
      @csrf
      <div class="user-box">
        <input type="text" name="username" value="{{old('username')}}" />
        <label>Tài khoản</label>
      </div>
      <div class="user-box">
        <input type="password" name="password">
        <label>Mật khẩu</label>
      </div>

      <a onclick="submitFormLogin()" ;>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        Đăng nhập
      </a>
    </form>
  </div>
</body>

</html>