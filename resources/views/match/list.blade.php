
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">比赛列表</h3>

        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
            <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
    </div>

    <div class="box-body">
        <div class="table-responsive">
<table class="table table-hover">
    <thead>
    <tr>
        <th>比赛类型</th>
        <!--<th>允许参赛类型</th>-->
        <!--<th>参赛花费</th>-->
        <!--<th>人数限制</th>-->
        <!--<th>投票次数限制</th>-->
        <!--<th>投票花费</th>-->
        <th>比赛时间</th>
    </tr>
    </thead>
    <tbody>
@foreach($matchList['lists'] as $val)
        <tr>
            <td>
                @if($val['matchType'] == 1)
                    模特大赛
                @elseif($val['matchType'] == 2)
                    健美大赛
                @elseif($val['matchType'] == 3)
                    萌宠大赛
                @else($val['matchType'] == 4)
                    神兽大赛
                @endif
            </td>
            <td>
                @foreach($val['openTime'] as $i => $f)
                    <ul class="list-unstyled">
                        @if($f[2])
                            <li class="bg-green-active">第 {{ $i + 1 }} 场 {{$f[0]}} - {{$f[1]}}</li>
                        @else
                            <li class="grey">第 {{ $i + 1 }} 场 {{$f[0]}} - {{$f[1]}}</li>
                        @endif

                    </ul>
                @endforeach
            </td>
        </tr>
@endforeach
    </tbody>
</table>

        </div>
    </div>
</div>