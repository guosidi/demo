<?php

namespace App\Http\Controllers\Admin\LotteryTicket;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\LotteryTicket;
use App\Models\OpenArea;
use Illuminate\Support\Facades\DB;
use App\Tools\Image;
use App\Tools\Admin\Response;
use Mockery\Exception;

class TypeController extends Controller
{
    public function index(Request $request, LotteryTicket $lotteryTicketModel)
    {

        $provinceList = $this->getOpenProvince();
        if ($request->ajax()) {
            $draw = $request->input("draw");
            $start = $request->input("start");
            $length = $request->input("length");
            $order = $request->input('order');

            $columns = [
                'id', 'province_id', 'pic', 'name', 'summary', 'price', 'remain', 'created_at', 'status'
            ];

            $list = $lotteryTicketModel::leftJoin('recmall.area', 'recmall.area.id', '=', 'lottery_tickets.province_id');
            if ($keyword = $request->input("keyword")) {
                $list = $list->where(function ($query) use ($keyword) {
                    $query->Where('name', 'like', '%' . $keyword . '%');
                });
            }
            if ($province_id = self::$auth_province_id) {
                $list = $list->where(function ($query) use ($province_id) {
                    $query->Where('lottery_tickets.province_id', $province_id);
                });
            } else {
                if ($province_id = $request->input("province_id")) {
                    $list = $list->where(function ($query) use ($province_id) {
                        $query->Where('lottery_tickets.province_id', $province_id);
                    });
                }
            }
            $status = $request->input("status");
            if (!is_null($status)) {
                $list = $list->where(function ($query) use ($status) {
                    $query->Where('status', $status);
                });
            }

            $list = $list->select('lottery_tickets.*', 'recmall.area.areaname as province');

            $return['recordsTotal'] = $list->count();
            $return['draw'] = $draw;
            $return['recordsFiltered'] = $list->count();
            $return["data"] = [];

            $data = $list->skip($start)->take($length)->orderBy($columns[$order[0]['column']], $order[0]['dir'])->get();

            foreach ($data as $k => $v) {
                $id = $v->id;

                $button = "";
                if (in_array("lotteryTicket_type_add", self::$access)) {
                    $button .= '<button type="button" class="btn green btn-xs btn-edit" data-id="' . $id . '"><i class="fa fa-edit"></i> 编辑</button>';
                }
                if (in_array("lotteryTicket_type_delete", self::$access)) {
                    $button .= '<button type="button" class="btn btn-danger btn-xs btn-del" data-id="' . $id . '"><i class="fa fa-trash"></i> 删除</button>';
                }

                $return["data"][] = [
                    $id,
                    $v->province,
                    '<img src="' . $v->pic . '" style="width: 60%;">',
                    $v->name,
                    $v->summary,
                    '¥'.$v->price,
                    $v->remain,
                    $v->updated_at->format('Y-m-d H:i:s'),
                    $v->status === 0 ? '已停售' : '在售中',
                    $button,
                ];
            }

            return response()->json($return);
        }
        $assign = compact('provinceList');
        return view('admin.lotteryTicket.type.index', $assign);
    }

    public function add(Request $request, LotteryTicket $lotteryTicketModel)
    {

        $provinceList = $this->getOpenProvince();
        if ($request->isMethod('post')) {
            //上传图片
            if (!isset($_FILES['file'])) {
                return Response::error("请选择上传的图片");
            }
            $result = Image::upImgFile($_FILES['file']);
            $result = json_decode($result);
            if ($result->code == 0) {
                $pic = $result->url;
            } else {
                return Response::error("图片上传失败");
            }

            $province_id = $request->input('province_id');
            $name = $request->input('name');
            $summary = $request->input('summary');
            $price = $request->input('price');
            $remain = $request->input('remain');
            $status = $request->input('status');

            DB::beginTransaction();
            try {
                $lotteryTicketModel->pic = $pic;
                $lotteryTicketModel->province_id = $province_id;
                $lotteryTicketModel->name = $name;
                $lotteryTicketModel->summary = $summary;
                $lotteryTicketModel->price = $price;
                $lotteryTicketModel->remain = $remain;
                $lotteryTicketModel->status = $status;
                if (!$lotteryTicketModel->save()) {
                    throw new Exception('彩票添加失败');
                }
                DB::commit();
                return Response::success();
            } catch (Exception $exception) {
                DB::rollBack();
                return Response::error($exception->getMessage());
            }

        }

        $render = view('admin.lotteryTicket.type.add', compact('provinceList'))->render();
        return Response::success($render);
    }

    public function edit(Request $request, LotteryTicket $lotteryTicketModel)
    {
        $provinceList = $this->getOpenProvince();
        $id = $request->input('id');
        if ($request->isMethod('post')) {
//            print_r($request->input());die;
            $lotteryTicketModel = $lotteryTicketModel::find($id);
            if (isset($_FILES['file'])) {
                //上传图片
                $result = Image::upImgFile($_FILES['file']);
                $result = json_decode($result);
                if ($result->code == 0) {
                    $lotteryTicketModel->pic = $result->url;
                } else {
                    return Response::error("图片上传失败");
                }
            }
            $province_id = $request->input('province_id');
            $name = $request->input('name');
            $summary = $request->input('summary');
            $price = $request->input('price');
            $remain = $request->input('remain');
            $status = $request->input('status');
            DB::beginTransaction();
            try {
                $lotteryTicketModel->province_id = $province_id;
                $lotteryTicketModel->name = $name;
                $lotteryTicketModel->summary = $summary;
                $lotteryTicketModel->price = $price;
                $lotteryTicketModel->remain = $remain;
                $lotteryTicketModel->status = $status;
                if (!$lotteryTicketModel->save()) {
                    throw new Exception('彩票更新失败');
                }

                DB::commit();
                return Response::success();
            } catch (Exception $exception) {
                DB::rollBack();
                return Response::error($exception->getMessage());
            }

        } else {
            $data = $lotteryTicketModel::find($id);
            $render = view('admin.lotteryTicket.type.edit', compact('provinceList', 'data'))->render();
            return Response::success($render);
        }

    }

    public function delete(Request $request, LotteryTicket $lotteryTicketModel)
    {
        try {
            $id = $request->input('id');
            if (!$lotteryTicketModel::destroy($id)) {
                throw new Exception("删除彩票失败");
            } else {
                return Response::success();
            }
        } catch (Exception $exception) {
            return Response::error($exception->getMessage());
        }


    }
    //根据当前用户权限获取省份
    protected function getOpenProvince()
    {
        $auth_province_id = self::$auth_province_id;
        if ($auth_province_id) {
            $provinceList = OpenArea::instance()->getOpenProvince($auth_province_id);
        } else {
            $provinceList = OpenArea::instance()->getOpenProvince();
        }
        return $provinceList;
    }


}
