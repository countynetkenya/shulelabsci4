<script type="application/javascript">
    LoadSubjectPerformance();
    function LoadSubjectPerformance()
    {
      const studentID = <?php echo json_encode($studentID)?>;

      const subjects = {
        <?php
          foreach($mandatorysubjects as $subject) {
              echo $subject->subjectID .": {name: '".$subject->subject ."'},";
          }
        ?>
      };

      const series = [
        <?php
          $i = 0;
          foreach($exams as $exam) {
            $data = "[";
            if(customCompute($mandatorysubjects)) {
              foreach ($mandatorysubjects as $mandatorysubject) {
                $uniquepercentageArr  = isset($markpercentagesArr[$mandatorysubject->subjectID]) ? $markpercentagesArr[$mandatorysubject->subjectID] : [];
                $markpercentages      = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
                if(customCompute($markpercentages)) {
                    foreach ($markpercentages as $markpercentageID) {
                      $f = false;
                      if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
                          $f = true;
                      }
                      if(isset($marks[$exam->examID][$studentID][$mandatorysubject->subjectID][$markpercentageID]) && $f) {
                          if($marks[$exam->examID][$studentID][$mandatorysubject->subjectID][$markpercentageID] > 0) {
                              $data .= "['". $mandatorysubject->subjectID ."', ". $marks[$exam->examID][$studentID][$mandatorysubject->subjectID][$markpercentageID] ."],";
                          } else {
                              $data .= "['". $mandatorysubject->subjectID ."', 0],";
                          }
                      } else {
                          $data .= "['". $mandatorysubject->subjectID ."', 0],";
                      }
                    }
                }
              }
            }
            $data .= "]";
            echo "{name: '". $exam->exam ."', data: ". $data .", type: 'column', pointPlacement: $i},";
            //echo "{name: '". $exam->exam ."', data: ". $data .", type: 'column'},";
            $i-=0.1;
          }
          $targetData = "[";
          foreach($mandatorysubjects as $mandatorysubject) {
            $targetData .= "['". $mandatorysubject->subjectID ."', ". $mandatorysubject->targetmark ."],";
          }
          $targetData .= "]";
          echo "{name: 'Target', data: ". $targetData .", type: 'line'},";
        ?>
      ];

      $('div[class="charts"][id="'+ studentID +'"] .container').highcharts({
        subjects,
        title: {
          text: 'Student\'s subject performance trend',
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
            description: 'Subjects'
          },
          labels: {
            useHTML: true,
            animate: true,
            format: '{chart.options.subjects.(value).name}<br>' +
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
