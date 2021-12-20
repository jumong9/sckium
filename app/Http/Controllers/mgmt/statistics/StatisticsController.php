<?php

namespace App\Http\Controllers\mgmt\statistics;

use App\Exports\LectorStatExcelExport;
use App\Exports\ClientStatExcelExport;
use App\Exports\ClassStatExcelExport;
use App\Exports\PointStatExcelExport;
use App\Exports\PointDetailStatExcelExport;

use App\Http\Controllers\Controller;
use App\Models\ClassLector;
use App\Models\ClassReport;
use App\Models\Client;
use App\Models\CommonCode;
use App\Models\ContractClass;
use App\Models\Contracts;
use App\Models\UserFile;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller{

    public function __construct(){

        $this->filePath = "uploads/".date("Y")."/acreport";
        $this->pageTitle = "수요처별 통계";
    }

    //수요처별 통계
    public function clientlist(Request $request){
        $searchType = $request->input('searchType');
        $searchWord = $request->input('searchWord');
        $searchStatus = $request->input('searchStatus');
        $perPage = empty($request->input('perPage') ) ? 10 : $request->input('perPage');
        $page = $request->input('page');

        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        DB::enableQueryLog();
       
        $clientList = Client::join('contract_classes as c', 'c.client_id' ,'=', 'clients.id')
                                    ->join('class_lectors as d', 'c.id', '=','d.contract_class_id')
                                    ->join('common_codes as e', function($join){
                                        $join->on('e.code_id','=', 'clients.gubun')
                                            ->where('e.code_group', '=','client_gubun');
                                        }
                                    )
                                    ->select('clients.id', 'clients.name', 'clients.gubun'
                                            , 'e.code_value as client_gubun_value'
                                            , DB::raw('sum(c.class_number) as class_number')
                                        //    , 'c.class_status'
                                            , DB::raw('count(d.id) as lector_count')
                                            , DB::raw('sum(d.lector_cost) as lector_cost')
                                    )
                                    ->where(function ($query) use ($searcFromDate, $searcToDate, $searchType){
                                        if(!empty($searcFromDate) && !empty($searcToDate) ){
                                            $query->whereBetween('c.class_day', [$searcFromDate, $searcToDate]);
                                        }
                                        if(!empty($searchType)){
                                            $query->where('clients.gubun','=',"{$searchType}");
                                        }
                                    })
                                    ->where('c.class_status', '>', '0')
                                    ->groupBy('clients.id','clients.name','clients.gubun','e.code_value')
                                    ->orderBy('clients.id', 'asc')
                                    ->paginate($perPage);


        $clientList->appends (array ('perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate));

        $clientGubunList = CommonCode::getCommonCode('client_gubun');

        //dd(DB::getQueryLog());
        return view('mgmt.statistics.clientlist', ['pageTitle'=>$this->pageTitle,'clientList'=>$clientList, 'clientGubunList'=>$clientGubunList, 'perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'page' => $page, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate] );

    }


    //강사단별 통계
    public function classlist(Request $request){

        $this->pageTitle = "강사단별 통계";
        $searchType = $request->input('searchType');
        $searchWord = $request->input('searchWord');
        $searchStatus = $request->input('searchStatus');
        $perPage = empty($request->input('perPage') ) ? 10 : $request->input('perPage');
        $page = $request->input('page');

        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        DB::enableQueryLog();
       
        $clientList = ContractClass::join('class_categories as c', 'contract_classes.class_category_id', '=', 'c.id')
                                    ->select('c.id', 'c.class_name', 'c.class_gubun'
                                            , DB::raw('sum(contract_classes.main_count) as main_count')
                                            , DB::raw('sum(contract_classes.sub_count) as sub_count')
                                    )
                                    ->where(function ($query) use ($searcFromDate, $searcToDate, $searchType){
                                        if(!empty($searcFromDate) && !empty($searcToDate) ){
                                            $query->whereBetween('contract_classes.class_day', [$searcFromDate, $searcToDate]);
                                        }
                                    })
                                    ->where('contract_classes.class_status', '>', '0')
                                    ->groupBy('c.id', 'c.class_name','c.class_gubun')
                                    ->orderBy('c.class_group', 'asc')
                                    ->orderBy('c.class_order', 'asc')
                                    ->paginate($perPage);


        $clientList->appends (array ('perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate));

        //$clientGubunList = CommonCode::getCommonCode('client_gubun');

        //dd(DB::getQueryLog());
        return view('mgmt.statistics.classlist', ['pageTitle'=>$this->pageTitle,'clientList'=>$clientList, 'perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'page' => $page, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate] );

    }


    //강사별 통계
    public function lectorlist(Request $request){

        $this->pageTitle = "강사별 통계";
        $searchType = $request->input('searchType');
        $searchWord = $request->input('searchWord');
        $searchStatus = $request->input('searchStatus');
        $perPage = empty($request->input('perPage') ) ? 10 : $request->input('perPage');
        $page = $request->input('page');

        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        DB::enableQueryLog();
       
        $clientList = User::join('class_category_user as b', 'b.user_id' ,'=', 'users.id')
                                    ->join('class_categories as c', 'c.id', '=','b.class_category_id')
                                    ->join('class_lectors as d', 'd.user_id', '=','users.id')
                                    ->join('contract_classes as e', 'e.id', '=', 'd.contract_class_id')
                                    ->join('common_codes as f', function($join){
                                        $join->on('f.code_id','=', 'b.user_status')
                                            ->where('f.code_group', '=','user_status');
                                        }
                                    )
                                    ->select('users.name', 'users.mobile', 'users.birthday'
                                            , DB::raw('if(users.gubun=0,\'내부\',\'외부\') as user_gubun')
                                            , 'f.code_value as user_status_value'
                                            , DB::raw('if(b.user_grade=10,\'반장강사\',\'일반강사\') as user_grade')
                                            , 'b.user_group'
                                            , 'c.class_name'
                                            , DB::raw('count(if(d.main_yn=1, 1, null)) as main_count')
                                            , DB::raw('count(if(d.main_yn=0, 1, null)) as sub_count')
                                            , DB::raw('sum(d.lector_cost) as lector_cost')
                                    )
                                    ->where(function ($query) use ($searcFromDate, $searcToDate, $searchType){
                                        if(!empty($searcFromDate) && !empty($searcToDate) ){
                                            $query->whereBetween('e.class_day', [$searcFromDate, $searcToDate]);
                                        }
                                    })
                                    ->where('e.class_status', '>', '0')
                                    ->groupBy('users.name', 'users.mobile', 'users.birthday', 'user_gubun', 'user_status_value','user_grade','user_group','class_name')
                                    ->orderBy('c.class_group', 'asc')
                                    ->orderBy('c.class_order', 'asc')
                                    ->paginate($perPage);


        $clientList->appends (array ('perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate));

        //$clientGubunList = CommonCode::getCommonCode('client_gubun');

        //dd(DB::getQueryLog());
        return view('mgmt.statistics.lectorlist', ['pageTitle'=>$this->pageTitle,'clientList'=>$clientList, 'perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'page' => $page, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate] );

    }


    //강사별 샘포인트 통계
    public function pointlist(Request $request){

        $this->pageTitle = "강사별 샘포인트";
        $searchType = $request->input('searchType');
        $searchWord = $request->input('searchWord');
        $searchStatus = $request->input('searchStatus');
        $perPage = empty($request->input('perPage') ) ? 10 : $request->input('perPage');
        $page = $request->input('page');

        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        DB::enableQueryLog();
       
        $clientList = User::join('class_lectors as d', 'd.user_id', '=','users.id')
                            ->join('contract_classes as e', 'e.id', '=', 'd.contract_class_id')
                            ->select('users.id'
                                    , DB::raw('max(users.name) as name') 
                                    , DB::raw('max(users.mobile) as mobile')
                                    , DB::raw('max(users.address) as address')
                                    , DB::raw('sum(if(d.main_yn=1, 1, 0)) as main_count')
                                    , DB::raw('sum(if(d.main_yn=0, 1, 0)) as sub_count')
                            )
                            ->where(function ($query) use ($searcFromDate, $searcToDate, $searchType){
                                if(!empty($searcFromDate) && !empty($searcToDate) ){
                                    $query->whereBetween('e.class_day', [$searcFromDate, $searcToDate]);
                                }
                            })
                            ->where('e.class_status', '>', '0')
                            ->groupBy('users.id')
                            ->orderBy('name', 'asc')
                            ->paginate($perPage);


        $clientList->appends (array ('perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate));

        //$clientGubunList = CommonCode::getCommonCode('client_gubun');

        //dd(DB::getQueryLog());
        return view('mgmt.statistics.pointlist', ['pageTitle'=>$this->pageTitle,'clientList'=>$clientList, 'perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'page' => $page, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate] );

    }



    //강사별 샘포인트 목록
    public function pointDetailList(Request $request){

        $this->pageTitle = "강사별 샘포인트";
        $id = $request->input('id');
        $searchType = $request->input('searchType');
        $searchWord = $request->input('searchWord');
        $searchStatus = $request->input('searchStatus');
        $perPage = empty($request->input('perPage') ) ? 10 : $request->input('perPage');
        $page = $request->input('page');

        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        DB::enableQueryLog();
        $clientList = User::join('class_lectors as b', 'b.user_id', '=','users.id')
                            ->join('contract_classes as c', 'c.id', '=', 'b.contract_class_id')
                            ->join('clients as d', 'd.id', '=', 'c.client_id')
                            ->join('common_codes as e', 'd.client_loctype', '=', 'e.code_id')
                            ->join('class_categories as f', 'f.id', '=', 'b.class_category_id')
                            ->select( 'users.id'
                                    , 'users.name'
                                    , 'c.class_day'
                                    , DB::raw('if(b.main_yn=1, \'주강사\', \'보조강사\') as main_yn')
                                    , 'f.class_name'
                                    , 'd.name as client_name'
                                    , 'e.code_value'
                            )
                            ->where(function ($query) use ($searcFromDate, $searcToDate, $searchType){
                                if(!empty($searcFromDate) && !empty($searcToDate) ){
                                    $query->whereBetween('c.class_day', [$searcFromDate, $searcToDate]);
                                }
                            })
                            ->where('c.class_status', '>', '0')
                            ->where('e.code_group', '=', 'client_loctype')
                            ->where('users.id',$id)
                            ->orderBy('c.class_day', 'asc')
                            ->paginate($perPage);


        $clientList->appends (array ('id'=> $id,'perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate));

        //$clientGubunList = CommonCode::getCommonCode('client_gubun');

        //dd(DB::getQueryLog());
        return view('mgmt.statistics.pointDetailList', ['id'=> $id, 'pageTitle'=>$this->pageTitle,'clientList'=>$clientList, 'perPage' => $perPage, 'searchType' => $searchType, 'searchWord' => $searchWord, 'page' => $page, 'searchStatus'=>$searchStatus, 'searcFromDate'=>$searcFromDate , 'searcToDate'=>$searcToDate] );

    }





    public function exportClientExcel(Request $request){
        //return Excel::download(new ClientExcelExport, 'ClientReport.xlsx');

        $searchType = $request->input('searchType');
        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        return (new ClientStatExcelExport)->forSearch($searchType, $searcToDate, $searcFromDate)->download('ClientStatReport.xlsx');

    }


    public function exportClassExcel(Request $request){
        //return Excel::download(new ClientExcelExport, 'ClientReport.xlsx');

        $searchType = $request->input('searchType');
        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        return (new ClassStatExcelExport)->forSearch($searchType, $searcToDate, $searcFromDate)->download('ClassStatReport.xlsx');

    }


    public function exportLectorExcel(Request $request){
        //return Excel::download(new ClientExcelExport, 'ClientReport.xlsx');

        $searchType = $request->input('searchType');
        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        return (new LectorStatExcelExport)->forSearch($searchType, $searcToDate, $searcFromDate)->download('LectorStatReport.xlsx');

    }


    public function exportPointExcel(Request $request){
        //return Excel::download(new ClientExcelExport, 'ClientReport.xlsx');

        $searchType = $request->input('searchType');
        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        return (new PointStatExcelExport)->forSearch($searchType, $searcToDate, $searcFromDate)->download('PointStatReport.xlsx');

    }


    public function exportPointDetailExcel(Request $request){
        //return Excel::download(new ClientExcelExport, 'ClientReport.xlsx');

        $searchType = $request->input('searchType');
        $searcFromDate = $request->input('searcFromDate');
        $searcToDate = $request->input('searcToDate');
        $id = $request->input('id');

        if(empty($searcFromDate) || empty($searcToDate)){
            $searcFromDate = date("Y-m", time()) .'-01';
            $prevMonthDate = strtotime("1 months ago", strtotime($searcFromDate));
            $dayCount = new DateTime( $searcFromDate );
            $searcToDate = $dayCount->format( 'Y-m-t' );
            $searcFromDate = date("Y-m", $prevMonthDate).'-01';
        }

        return (new PointDetailStatExcelExport)->forSearch($searchType, $searcToDate, $searcFromDate, $id)->download('PointDetailStatReport.xlsx');

    }

}