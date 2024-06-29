{{--điền lại kết quả sau khi thất bại--}}
{{--https://laracasts.com/discuss/channels/general-discussion/keeping-input-on-form-validation-request--}}
<html>

<head>
    <title>Register account</title>
    <link rel="stylesheet" href="{{asset('css/login.css')}}">
</head>

<body>
    <script>
        function submitFormRegister() {
            document.getElementById("formRegister").submit();
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
        <h2>Register</h2>
        <form method="post" action="{{route('/registerForUser')}}" id="formRegister">

            <div class="user-box">
                <input type="text" name="username" />
                <label>username</label>
            </div>
            <div class="user-box">
                <input type="text" name="displayname" />
                <label>Your name</label>
            </div>
            <div class="user-box">
                <input type="tel" name="phone" />
                <label>Your Phone</label>
            </div>
            <div class="user-box">
                <input type="password" name="password">
                <label>password</label>
            </div>
            <!-- <div class="user-box">
                <input type="password" name="r_pwd">
                <label>Retype your password:</label>
            </div> -->

            <a onclick="submitFormRegister()" ;>
                <span></span>
                <span></span>
                <span></span>
                <span></span>
                SIGN UP
            </a>
            <a href="login">SIGN IN</a>
        </form>
    </div>
</body>

</html>