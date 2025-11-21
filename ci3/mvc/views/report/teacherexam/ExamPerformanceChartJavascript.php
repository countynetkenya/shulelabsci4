<script type="application/javascript">
    LoadExamPerformance();
    function LoadExamPerformance()
    {
      const teacherID = <?php echo json_encode($teacherID)?>;

      const exams = {
        <?php
          foreach($exams as $exam) {
              echo $exam->examID .": {name: '".$exam->exam ."'},";
          }
        ?>
      };

      const series = [
        <?php
          foreach($exams as $exam) {
            $data = "[['". $exam->examID ."', ". $teacherPosition[$exam->examID][$teacherID]['classPositionMark'] ."]]";
            echo "{name: '". $exam->exam ."', data: ". $data .", type: 'column', dataLabels: [{
                enabled: true,
                inside: false,
                style: {
                    fontSize: '16px'
                }
            }],},";
          }
          $teacherData = "[";
          foreach($exams as $exam) {
            if(isset($teacherPosition[$exam->examID][$teacherID]['classPositionMark']) && $teacherPosition[$exam->examID][$teacherID]['classPositionMark'] > 0 && isset($teacherPosition['totalTeacherMarkAverage'][$exam->examID])) {
                $teachermean = ini_round($teacherPosition['totalTeacherMarkAverage'][$exam->examID] / customCompute($teachers));
                $teacherData .= "['". $exam->examID ."', ". $teachermean ."],";
            } else {
                $teacherData .= "['". $exam->examID ."', 0.00],";
            }
          }
          $teacherData .= "]";
          echo "{name: 'Teacher mean', data: ". $teacherData .", type: 'line', dataLabels: [{
              enabled: true, style: {
                  fontSize: '12px'
              }}]},";
        ?>
      ];

      $('div[class="charts"][id="'+ teacherID +'"] .container2').highcharts({
        exams,
        title: {
          text: 'Teacher\'s performance in relation to other class teachers',
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
            text: 'Mark percentage'
          },
          showFirstLabel: true,
          max: 100
        }],
        series: series,
        exporting: {
          allowHTML: true
        }
      });
    }
</script>
