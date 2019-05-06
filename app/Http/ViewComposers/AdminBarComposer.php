<?php

namespace App\Http\ViewComposers;

use App\Models\Admin\Permissions;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;

class AdminBarComposer
{

    protected $bar_info;

    public function __construct()
    {
        $this->bar_info = [];
    }

    public function compose(View $view)
    {
        $route_name = Route::currentRouteName();
        if ($route_name) {
            $bar = Permissions::where('name', $route_name)->first();
            if ($bar) {
                $parent_id = $bar->parent_id;
                $display_name = $bar->display_name;
                $description = $bar->description;

                $title = ['title' => $display_name, 'description' => $description];
                $view->with('title', $title);

                $name = $bar->name;
                $url = str_replace('_', '/', $name);

                $this->bar_info = $this->getBreadCrumb($parent_id);
                array_push($this->bar_info, ['title' => $display_name, 'url' => $url]);
            }
        }

        $view->with('barInfo', $this->bar_info);
    }

    /**
     * 获取面包屑导航
     * @param $parent_id
     * @return array
     */
    function getBreadCrumb($parent_id)
    {
        $info = Permissions::find($parent_id);
        if ($info) {
            $parent_id = $info->parent_id;
            $display_name = $info->display_name;

            $name = $info->name;
            $url = '';
            if ($parent_id) {
                $url = str_replace('_', '/', $name);
            }

            array_unshift($this->bar_info, ['title' => $display_name, 'url' => $url]);

            $this->getBreadCrumb($parent_id);
        }

        return $this->bar_info;
    }
}