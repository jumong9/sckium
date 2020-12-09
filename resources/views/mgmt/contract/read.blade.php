@extends('layouts.main_layout')

@section('content')

    <!-- Page Heading -->
    <h1 class="h3 mb-2 text-gray-800">{{$pageTitle}}</h1>

        <!-- DataTales Example -->
        <div class="card shadow mb-4">

            <div class="card-body">
                <div class="table">
                    <table class="table-sm" id="" cellspacing="0">
                        <colgroup>
                            <col width="200px">
                            <col width="40%">
                            <col width="200px">
                            <col width="40%">
                        </colgroup>
                        <tbody class="thead-light " style="border-bottom: 1px solid #dee2e6;">
                            <tr>
                                <th>수요처명</th>
                                <td>
                                    {{ $client->name }}
                                </td>
                                <th>구분</th>
                                <td>
                                    {{ $client->code_value }}
                                </td>
                            </tr>
                            <tr>
                                <th >담당자</th>
                                <td>
                                    {{ $contract->name }}
                                </td>
                                <th >이메일</th>
                                <td>
                                    {{ $contract->email }}
                                </td>
                            </tr>
                            <tr>
                                <th>연락처</th>
                                <td>
                                    {{ $contract->phone }}
                                </td>
                                <th>연락처</th>
                                <td>
                                    {{ $contract->phone2 }}
                                </td>
                            </tr>
                            <tr>
                                <th>회당 강사비</th>
                                <td>
                                    {{ number_format($contract->class_cost) }}
                                </td>
                                <th>총 강사비</th>
                                <td>
                                    {{ number_format($contract->class_total_cost) }}
                                </td>

                            </tr>
                            <tr>
                                <th>인당 재료비</th>
                                <td>
                                    {{ number_format($contract->material_cost) }}
                                </td>
                                <th>총 재료비</th>
                                <td>
                                    {{ number_format($contract->material_total_cost) }}
                                </td>
                            </tr>
                            <tr>
                                <th>입금여부</th>
                                <td>
                                    {{ $contract->paid_yn == 0 ? '미입금' : '입금완료' }}
                                </td>
                                <th>총비용</th>
                                <td>
                                    {{ number_format($contract->total_cost) }}
                                </td>
                            </tr>
                            <tr>
                                <th>진행상태</th>
                                <td>
                                    {{ $contract->code_value }}
                                </td>
                                <td>
                                </td>
                                <td>
                                </td>
                            </tr>
                            <tr>
                                <th>비고</th>
                                <td colspan="3">
                                    {{ $contract->comments }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <span>신청 강좌</span>
        <div class="card shadow mb-4">

            <div class="card-body">
                <div class="table">
                    <table class="table-sm table-hover" width="100%" cellspacing="0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:100px;">배정상태</th>
                                <th style="width:100px;">활동일자</th>
                                <th style="width:120px;">시간</th>
                                <th style="width:160px;">프로그램</th>
                                <th style="width:160px;">교육대상</th>
                                <th style="width:80px;">인원</th>
                                <th style="width:80px;">횟수</th>
                                <th style="width:80px;">차수</th>
                                <th style="width:80px;">주강사수</th>
                                <th style="width:80px;">보조강사수</th>
                                <th style="width:100px;">수업방식</th>
                            </tr>
                        </thead>
                        <tbody id="classList" class="thead-light " style="border-bottom: 1px solid #dee2e6;">
                            @foreach($classList as $key => $list)
                            <tr>
                                <td>
                                    {{$list->lector_apply_yn == 0? '' : '배정완료'}}
                                </td>
                                <td>
                                    {{ $list->class_day }}
                                </td>
                                <td>
                                    {{$list->time_from}} - {{$list->time_to}}
                                </td>
                                <td>
                                    {{$list->class_name}}
                                </td>
                                <td>
                                    {{$list->class_target}}
                                </td>
                                <td>
                                    {{number_format($list->class_number)}}
                                </td>
                                <td>
                                    {{number_format($list->class_count)}}
                                </td>
                                <td>
                                    {{number_format($list->class_order)}}
                                </td>
                                <td>
                                    {{number_format($list->main_count)}}
                                </td>
                                <td>
                                    {{number_format($list->sub_count)}}
                                </td>
                                <td>
                                    @if($list->class_type == 0 ) 오프라인
                                    @elseif($list->class_type == 1) 온라인 실시간
                                    @else 온라인 동영상
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="row-fluid" style="text-align: right;">
                    @if ($contract->code_id == 1)
                        <button class="btn btn-primary" type="button"  data-status='2' id="updateStatusButton">오픈</button>
                    @endif
                    @if ($contract->code_id == 2)
                        <button class="btn btn-primary" type="button"  data-status='4' id="updateStatusButton">강사승인</button>
                    @endif
                    @if ($contract->code_id == 4)
                        <button class="btn btn-primary" type="button"  data-status='5' id="updateStatusButton">서류 제출완료</button>
                    @endif
                    @if ($contract->code_id == 5)
                        <button class="btn btn-primary" type="button"  data-status='6' id="updateStatusButton">최종확정</button>
                    @endif
                    <button class="btn btn-primary" type="button"  id="updateButton">수정</button>
                    <button class="btn btn-primary" type="button"  id="listButton">목록</button>
                </div>
            </div>
        </div>
@endsection

@section('scripts')


    <!-- Custom scripts for all pages-->
    <script>

        $(document).ready(function() {

            var params = "?perPage={{$perPage}}&page={{$page}}&searchStatus={{$searchStatus}}&searchType={{$searchType}}&searchWord={{$searchWord}}";

            $("#listButton").click(function(){
                location.href='{{ route('mgmt.contract.list')}}' + params ;
            });

            $("#updateButton").click(function(){
                location.href='{{ route('mgmt.contract.update')}}' + params +"&id={{$contract->id}}";
            });

            $("#updateStatusButton").click(function(){
                if(confirm( $(this).text() + ' 처리 하시겠습니까?')){
                    var status_code = $(this).data('status');
                    $.ajax({
                        type : "post",
                        url : "{{ route('mgmt.contract.updateContractStatus') }}",
                        data : {
                            _token: "{{csrf_token()}}",
                            'contract_id' : '{{ $contract->id }}',
                            'status_code' : status_code,
                        },
                        success : function(data){
                            alert(data.msg);
                            location.reload();
                        },
                        error : function(xhr, exMessage) {
                            alert('error');
                        },
                    });
                }
            });

        });

    </script>
@endsection