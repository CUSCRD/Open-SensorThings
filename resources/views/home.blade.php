<html>

<head>
  <link rel="stylesheet" href="{{asset('css/login.css')}}">
</head>

<body>
  @if(\App\Http\Controllers\Home::minOrMod(Illuminate\Support\Facades\Auth::user()['username']))
  <a href="{{route('user_register_form')}}">Register Account</a>
  <br></br>
  <a href="{{route('userList')}}">List User</a>
  <hr>
  @endif
  <a href="{{route('changePassword')}}">Change Password</a>
  <br>
  <a href="{{route('logout')}}">Logout</a>
</body>

</html>