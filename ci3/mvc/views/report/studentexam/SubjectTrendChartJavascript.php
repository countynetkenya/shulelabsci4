<script type="application/javascript">
    LoadSubjectTrend();
    function LoadSubjectTrend()
    {
      const studentID = <?php echo json_encode($studentID)?>;
      const subjectID = <?php echo json_encode($subjectID)?>;
      const subject = <?php echo json_encode($subject)?>;

      const exams = {
        <?php
          foreach($exams as $exam) {
              echo $exam->examID .": {name: '".$exam->exam ."'},";
          }
        ?>
      };

      const series = [
        <?php
          $data = "[";
          foreach($exams as $exam) {
            if(customCompute($studentPosition['studentClassPositionArray'][$exam->examID])) {
              $subjectMark = $studentPosition[$exam->examID][$studentID]['subjectMark'][$subjectID];
              $data .= "['". $exam->examID ."', ". $subjectMark ."],";
            }
            else
              $data .= "['". $exam->examID ."', 0],";
          }
          $data .= "]";
          echo "{name: 'Class position', data: ". $data .", type: 'line'},";
        ?>
      ];

      $('div[class="charts"][id="'+ studentID +'"] > .subjects .'+ subjectID).highcharts({
        exams,
        title: {
          text: subject,
          align: 'left'
        },
        plotOptions: {
          series: {
            grouping: false,
            borderWidth: 0
          }
        },
        legend: {
          enabled: false
        },
        xAxis: {
          type: 'category',
          accessibility: {
            description: 'Exams'
          },
          labels: {
            enabled: false
          }
        },
        yAxis: [{
          title: false,
          showFirstLabel: true,
          max: 100,
          min: 0
        }],
        series: series,
        exporting: {
          allowHTML: true
        }
      });
    }
</script>
