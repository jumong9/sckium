@extends('layouts.main_layout')

@section('content')

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{$pageTitle}}</h1>

    <form name="searchForm" id="searchForm"  action="{{route('mgmt.statistics.financesumlist') }}" method="post" >
        <input name="checkedItemId" type="hidden" value=""/>
    @csrf
    <!-- DataTales Example -->
    <div class="card shadow mb-4">

        <div class="card-header py-3">
            <div class="float-left">
                <div class="form-inline">
                    <select name="perPage" id="perPage" class="form-control float-left mr-2">
                        <option value="5" {{$clientList->perPage() == 5 ? "selected" : "" }} >5</option>
                        <option value="10" {{$clientList->perPage() == 10 ? "selected" : "" }}>10</option>
                        <option value="30" {{$clientList->perPage() == 30 ? "selected" : "" }}>30</option>
                        <option value="50" {{$clientList->perPage() == 50 ? "selected" : "" }}>50</option>
                    </select>
                    <span >전체 {{ $clientList->total() }} 건</span>
                </div>
            </div>
            <div class="float-right">
                <div class="form-inline">
                    <div class="form-group">

                        <!-- <input type="text" class="form-control" id="searchWord" name="searchWord" value="{{ $searchWord }}" placeholder="수요처명">
                         -->
                        <input style="width: 110px;" type="text" class="form-control datepicker " id="searcFromDate" name="searcFromDate" value="{{ $searcFromDate }}" placeholder="시작일">
                        <input style="width: 110px;" type="text" class="form-control datepicker" id="searcToDate" name="searcToDate" value="{{ $searcToDate }}" placeholder="종료일">
                        <button type="button" name="searchButton" id="searchButton" class="btn btn-primary ml-2">검색</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="dataTable" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th>재원</th>
                            <th>구분</th>
                            <th>강사단명</th>
                            <th>진행횟수</th>
                            <th>진행차시</th>
                            <th>진행인원</th>
                            <th>지출금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientList as $key => $list)
                        <tr>
                            <td>{{ $list->code_value}}</td>
                            <td>{{ $list->class_gubun}}</td>
                            <td>{{ $list->class_name}}</td>
                            <td>{{ number_format($list->class_count) }}</td>
                            <td>{{ number_format($list->class_order) }}</td>
                            <td>{{ number_format($list->lector_count) }}</td>
                            <td>{{ number_format($list->lector_cost) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{ $clientList->withQueryString()->links() }}
            </div>
            <div class="row-fluid" style="text-align: right;">
                <button class="btn btn-primary" type="button" name="exportExcelButton" id="exportExcelButton">엑셀다운로드</button>
            </div>
        </div>
    </div>
    </form>
@endsection

@section('scripts')
    <!-- Custom scripts for all pages-->
    <script>
        $(document).ready(function() {


            $("#perPage").change(function(){
                $("#searchButton").trigger("click");
            });

            $("#searchButton").click(function(){
                $("#searchForm").submit();
            });

            $("#selectAllCheck").click(function(){
                if($("#selectAllCheck").prop("checked")) {
                    $("input[type=checkbox]").prop("checked",true);
                } else {
                    $("input[type=checkbox]").prop("checked",false);
                }

            });

            $("#createButton").click(function(){
                location.href='{{ route('mgmt.client.create')}}';
            });

            $("#exportExcelButton").click(function(e){
                $("#searchForm").attr("action", "{{ route('mgmt.statistics.exportFinanceSumExcel') }}");
                $("#searchForm").submit();
                $("#searchForm").attr("action", "{{route('mgmt.statistics.financesumlist') }}");
            });


        });
    </script>
@endsection
