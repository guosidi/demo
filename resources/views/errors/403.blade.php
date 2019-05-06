<!DOCTYPE html>
<html>

<head>
    <title>403</title>
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
        <div class="title">您没有权限执行此操作~</div>
        <p>将在 <span id="mes">5</span> 秒钟后<a href="javascript:window.history.go(-1);">返回</a>！</p>
    </div>
</div>

</body>
</html>