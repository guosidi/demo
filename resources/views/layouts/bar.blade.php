<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="/">首页</a>
            <i class="fa fa-circle"></i>
        </li>
        @if($barInfo)
            @foreach($barInfo as $bar)
                <li>
                    @if(empty($bar['url']))
                        <span>{{$bar['title']}}</span>
                    @else
                        <a href="{{url($bar['url'])}}">{{$bar['title']}}</a>
                    @endif
                    <i class="fa fa-circle"></i>
                </li>
            @endforeach
        @endif
    </ul>
</div>
<!-- BEGIN PAGE TITLE-->
@if(Request::path()=='/')
    <h1 class="page-title"> 首页
        <small>欢迎使用</small>
    </h1>
@else
    @if(isset($title))
        <h1 class="page-title"> {{$title['title']}}
            <small>{{$title['description']}}</small>
        </h1>
    @endif
@endif
<!-- END PAGE TITLE-->