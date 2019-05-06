<!DOCTYPE html>
<html>

<head>
    <title>403-账户已被禁用</title>
    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet"
          type="text/css">
    <script src="{{URL::asset('/js/countdown.js')}}" type="text/javascript"></script>
    <style>
        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            padding: 0;
            width: 100%;
            color: #B0BEC5;
            display: table;
            font-weight: 100;
        'Lato';
        }

        .container {
            text-align: center;
            display: table-cell;
            vertical-align: middle;
        }

        .content {
            text-align: center;
            display: inline-block;
        }

        .title {
            font-size: 72px;
            margin-bottom: 40px;
        }
    </style>

</head>
<body>

<div class="container">
    <div class="content">
        <div class="title">您的账户已被禁用，请联系管理员</div>
        <p>将在 <span id="mes">5</span> 秒钟后<a href="{{url("login")}}">返回登录页面</a>！</p>
    </div>
</div>

</body>
</html>