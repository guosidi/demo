<div class="page-sidebar-wrapper">
    <!-- BEGIN SIDEBAR -->
    <div class="page-sidebar navbar-collapse collapse">
        <!-- BEGIN SIDEBAR MENU -->
        <ul class="page-sidebar-menu  page-header-fixed " data-keep-expanded="false" data-auto-scroll="true"
            data-slide-speed="200" style="padding-top: 20px">
            <!-- DOC: To remove the sidebar toggler from the sidebar you just need to completely remove the below "sidebar-toggler-wrapper" LI element -->
            <!-- BEGIN SIDEBAR TOGGLER BUTTON -->
            <li class="sidebar-toggler-wrapper hide">
                <div class="sidebar-toggler">
                    <span></span>
                </div>
            </li>
            <!-- END SIDEBAR TOGGLER BUTTON -->
            <!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
            <li class="nav-item start @if(Request::path()=='/') active @endif">
                <a href="{{url("/")}}" class="nav-link">
                    <i class="icon-home"></i>
                    <span class="title">首页</span>
                </a>
            </li>
            @if($adminMenus)
                @foreach($adminMenus as $adminMenu)
                    <li class="nav-item {{@$adminMenu['select']?'active open':''}}">
                        <a href="javascript:;" class="nav-link nav-toggle">
                            <i class="{{$adminMenu['ico']}}"></i>
                            <span class="title">{{$adminMenu['name']}}</span>
                            @if(@$adminMenu['select'])
                                <span class="selected"></span>
                            @endif
                            <span class="arrow {{@$adminMenu['select']?'open':''}}"></span>
                        </a>
                        @if(isset($adminMenu['children']))
                            <ul class="sub-menu">
                                @foreach($adminMenu['children'] as $menu)
                                    <li class="nav-item {{@$menu['select']?'active open':''}}">
                                        <a href="{{url($menu['url'])}}" class="nav-link ">
                                            <span class="title">{{$menu['name']}}</span>
                                            @if(@$menu['select'])
                                                <span class="selected"></span>
                                            @endif
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            @endif
        </ul>
        <!-- END SIDEBAR MENU -->
    </div>
    <!-- END SIDEBAR -->
</div>