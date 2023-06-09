
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
<form action="{{route('auth.change-password-forget')}}" method="post">
    <input type="hidden" name="_token" value="{{ $token }}">
    <label>Mật khẩu mới</label><br>
    <input type="password" name="password">
    <label>Nhập lại mật khẩu mới</label><br>
    <input type="password" name="password_confirm">
    <button type="submit">Xác nhận</button>
</form>
</body>
</html>
