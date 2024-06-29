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
        <h2>Thay đổi quyền</h2>
        <form method="post" action="{{route('changeRoleResult',['u' => $_REQUEST['u']]) }}">
            @csrf
            <ul>
                @foreach($roles as $role)
                    <li>
                        <label>
                            <input type="checkbox" value="{{$role->roleId}}" name="roles[]" {{ in_array($role->roleId,$userRoles) ? 'checked' : '' }}>
                            {{$role->roleName}}
                        </label>
                    </li>
                @endforeach
            </ul>
            <br>
            <button type="submit">Cập nhật</button>
        </form>
    </body>
</html>
