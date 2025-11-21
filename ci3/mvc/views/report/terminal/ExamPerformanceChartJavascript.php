<script type="application/javascript">
    LoadExamPerformance();
    function LoadExamPerformance()
    {
      const studentID = <?php echo json_encode($studentID)?>;

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
            $data = "[";
            if(isset($studentPosition[$exam->examID][$studentID]['classPositionMark']))
              $data .= "['". $exam->examID ."', ". ini_round($studentPosition[$exam->examID][$studentID]['classPositionMark']) ."],";
            else
              $data .= "['". $exam->examID ."', 0.00],";
            $data .= "]";
            echo "{name: '". $exam->exam ."', data: ". $data .", type: 'column', dataLabels: [{
                enabled: true,
                inside: true,
                style: {
                    fontSize: '16px'
                }
            }],},";
          }
          $classData = "[";
          foreach($exams as $exam) {
            if(isset($studentPosition[$exam->examID][$studentID]['classPositionMark']) && $studentPosition[$exam->examID][$studentID]['classPositionMark'] > 0 && isset($studentPosition['totalStudentMarkAverage'][$exam->examID])) {
                $classmean = ini_round($studentPosition['totalStudentMarkAverage'][$exam->examID] / $studentPosition[$exam->examID][$studentID]['classPositionMark']);
                $classData .= "['". $exam->examID ."', ". $classmean ."],";
            } else {
                $classData .= "['". $exam->examID ."', 0.00],";
            }
          }
          $classData .= "]";
          echo "{name: 'Class mean', data: ". $classData .", type: 'line', dataLabels: [{
              enabled: true, style: {
                  fontSize: '12px'
              }}]},";
        ?>
      ];

      $('div[class="charts"][id="'+ studentID +'"] .container2').highcharts({
        exams,
        title: {
          text: 'Student\'s performance in relation to class performance',
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
