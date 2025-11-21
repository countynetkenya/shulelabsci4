<script type="application/javascript">
$(function() {
    LoadPositionPerformance();
    function LoadPositionPerformance()
    {
      const exams = {
        <?php
          foreach($exams2 as $exam) {
              echo $exam->examID .": {name: '".$exam->exam ."'},";
          }
        ?>
      };

      const series = [
        <?php
          $data = "[";
          foreach($exams2 as $exam) {
            if(customCompute($studentPosition['studentClassPositionArray'][$exam->examID])) {
              $position = ((int)array_search($studentID, array_keys($studentPosition['studentClassPositionArray'][$exam->examID])) + 1);
              $data .= "['". $exam->examID ."', ". $position ."],";
            }
            else
              $data .= "['". $exam->examID ."', 0],";
          }
          $data .= "]";
          echo "{name: 'Class position', data: ". $data .", type: 'line'},";
        ?>
      ];

      $('#container3').highcharts({
        exams,
        title: {
          text: 'Student\'s class position trend',
          align: 'left'
        },
        plotOptions: {
          series: {
            grouping: false,
            borderWidth: 0
          }
        },
        legend: {
          enabled: true
        },
        xAxis: {
          type: 'category',
          accessibility: {
            description: 'Exams'
          },
          labels: {
            useHTML: true,
            animate: true,
            format: '{chart.options.exams.(value).name}<br>' +
              '<span class="f32">' +
              '<span style="display:inline-block;height:32px;vertical-align:text-top;" ' +
              'class="flag {value}"></span></span>',
            style: {
              textAlign: 'center'
            }
          }
        },
        yAxis: [{
          title: {
            text: 'Class position'
          },
          showFirstLabel: true,
          max: <?=count($students)?>
        }],
        series: series,
        exporting: {
          allowHTML: true
        }
      });
    }
});
</script>
