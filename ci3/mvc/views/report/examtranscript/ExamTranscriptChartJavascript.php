<?php
    $chartSeriesData = isset($chartSeries) ? $chartSeries : array();
?>
<script type="text/javascript">
(function(){
    var chartData = <?php echo json_encode($chartSeriesData); ?>;
    if(typeof Highcharts === 'undefined' || !chartData) {
        return;
    }

    Object.keys(chartData).forEach(function(studentID){
        var container = document.getElementById('exam-progress-chart-' + studentID);
        if(!container) {
            return;
        }
        var entry = chartData[studentID];
        if(!entry || !entry.labels || entry.labels.length === 0) {
            return;
        }
        var studentScores = (entry.studentScores || []).map(function(value){
            return value !== null ? parseFloat(value) : null;
        });
        var classAverages = (entry.classAverages || []).map(function(value){
            return value !== null ? parseFloat(value) : null;
        });
        var hasData = studentScores.some(function(v){ return v !== null; }) || classAverages.some(function(v){ return v !== null; });
        if(!hasData) {
            return;
        }

        Highcharts.chart(container, {
            chart: {
                spacingTop: 20
            },
            title: {
                text: null
            },
            credits: {
                enabled: false
            },
            xAxis: {
                categories: entry.labels,
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: '<?=$this->lang->line('examtranscriptreport_average_mark')?>'
                }
            },
            tooltip: {
                shared: true,
                valueDecimals: 2
            },
            legend: {
                enabled: true
            },
            series: [{
                name: '<?=$this->lang->line('examtranscriptreport_chart_student')?>',
                type: 'column',
                data: studentScores,
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.2f}'
                }
            }, {
                name: '<?=$this->lang->line('examtranscriptreport_chart_class_average')?>',
                type: 'spline',
                data: classAverages,
                marker: {
                    enabled: true
                },
                dataLabels: {
                    enabled: true,
                    format: '{point.y:.2f}'
                }
            }]
        });
    });
})();
</script>
