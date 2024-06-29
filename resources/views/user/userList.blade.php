<html>
<head>
    <style>
        table,th,td{
            border-collapse: collapse;
            border: 1px solid black;
        }
    </style>
</head>
    <body>
        @if(\App\Http\Controllers\Home::minOrMod(Illuminate\Support\Facades\Auth::user()['username'])==false)
            @php
                header("Location: " . Illuminate\Support\Facades\URL::to('/'), true, 302);
                exit();
            @endphp
        @endif
        <div>
            <table>
                <tr>
                    <th>
                        <span>Username</span>
                    </th>
                    <th>
                        <span>Đổi quyền</span>
                    </th>
                    <th>
                        <span>Xóa username</span>
                    </th>
                </tr>
                @foreach($users as $item)
                    <tr>
                        <td>
                            <span>{{$item->username}}</span>
                        </td>
                        <td>
                            <span><a href="{{route('changeRole') . "?u=$item->username"}}">Sửa</a></span>
                        </td>
                        <td>
                            <span><a href="{{route('deleteUser') . "?u=$item->username"}}">Xóa</a></span>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </body>
</html>
