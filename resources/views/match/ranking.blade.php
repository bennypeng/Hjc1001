
<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">排行榜</h3>

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
        <th>名次</th>
        <th>宠物ID</th>
        <th>主人昵称</th>
        <th>票数</th>
    </tr>
    </thead>
    <tbody>
@foreach($ranking['ranking'] as $k => $val)
        <tr>
            <td>#{{ $k + 1 }}</td>
            <td>{{ $val['petId'] }}</td>
            <td>{{ $val['nickname'] }}</td>
            <td>{{ $val['ticket'] }}</td>
        </tr>
@endforeach
    </tbody>
</table>

        </div>
    </div>
</div>