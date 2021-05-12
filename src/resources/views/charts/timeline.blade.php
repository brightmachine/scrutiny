<?php $id = \Illuminate\Support\Str::random(10); ?>
<script type="text/javascript">
    google.charts.load("current", {packages: ["timeline"]});
    google.charts.setOnLoadCallback(function() {
        var container = document.getElementById('chart-{{$id}}');
        var chart = new google.visualization.Timeline(container);
        var dataTable = new google.visualization.DataTable();
        dataTable.addColumn({type: 'string', id: 'Result'});
        dataTable.addColumn({type: 'string', id: 'Status'});
        dataTable.addColumn({type: 'date', id: 'From'});
        dataTable.addColumn({type: 'date', id: 'To'});
        dataTable.addRows([
            @foreach($history->transformForTimelineChart() as $check)
            ['Result', "{{$check['icon']}}", new Date({{$check['time']}}000), new Date({{$check['timeTo']}}000)],
            @endforeach
        ]);

        var options = {
            timeline: {showRowLabels: false},
            colors: [
                @foreach($history->transformForTimelineChart() as $check)
                    @if($check['status'] == 'passed')
                        "#4cc437",
                    @elseif($check['status'] == 'failed')
                        "#db4437",
                    @else
                        "#4285f4",
                    @endif
                @endforeach
            ],
            avoidOverlappingGridLines: false,
            fontSize: 34,
        };

        chart.draw(dataTable, options);
    });

</script>

<div id="chart-{{$id}}" style="height: 110px;"></div>
