<html>

<head>
  <title>Welcome to SmartGarden</title>
  <link rel="stylesheet" href="{{asset('css/login.css')}}">
</head>

<body>
  <script>
    function submitFormLogin() {
      document.getElementById("formLogin").submit();
    }
  </script>
  <div>

  </div>

  <div class="login-box">
    <marquee style="color:red;font-size:30;"> Welcome to Smart Garden </marquee>
    <h2>SIGN IN</h2>

    <form method="post" action="{{route('checkLogin')}}" id="formLogin">
      @csrf
      <div class="user-box">
        <input type="text" name="username" value="{{old('username')}}" />
        <label>account</label>
      </div>
      <div class="user-box">
        <input type="password" name="password">
        <label>password</label>
      </div>

      <a onclick="submitFormLogin()" ;>
        <span></span>
        <span></span>
        <span></span>
        <span></span>
        Enter
      </a>
      <a href="/register">SignUp</a>
    </form>
  </div>
</body>

</html>