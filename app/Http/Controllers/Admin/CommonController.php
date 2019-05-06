<?php

namespace App\Http\Controllers\Admin;

use App\Models\Area;
use App\Models\OpenArea;
use App\Tools\Admin\Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CommonController extends Controller
{
    function area(Request $request)
    {
        $id = $request->input('id');

        if (!$id) {
            $data = OpenArea::where('id', '<>', 1)
                ->select('province_id as id', 'province_name as name')
                ->groupBy('province_id')
                ->get();
            return Response::success($data);
        } else {
            $data = Area::instance()->where("parentid", $id)->select('id', 'areaname as name')->get();
        }

        if (!$data->isEmpty()) {
            return Response::success($data);
        } else {
            return Response::error();
        }
    }
}
