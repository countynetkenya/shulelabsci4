<script type="application/javascript">
    LoadSubjectPerformance();
    function LoadSubjectPerformance()
    {
      const teacherID = <?php echo json_encode($teacherID)?>;

      const subjects = {
        <?php
          foreach($teacherSubjects as $subject) {
              if($subject->teacherID == $teacherID)
                echo $subject->subjectID .": {name: '".$subject->subject ."'},";
          }
        ?>
      };

      const series = [
        <?php
          $i = 0;
          foreach($exams as $exam) {
            $data = "[";
            if(customCompute($teacherSubjects)) {
              foreach ($teacherSubjects as $teacherSubject) {
                if($teacherSubject->teacherID == $teacherID) {
                  $uniquepercentageArr  = isset($markpercentagesArr[$teacherSubject->subjectID]) ? $markpercentagesArr[$teacherSubject->subjectID] : [];
                  $markpercentages      = $uniquepercentageArr[(($settingmarktypeID==4) || ($settingmarktypeID==6)) ? 'unique' : 'own'];
                  if(customCompute($markpercentages)) {
                      foreach ($markpercentages as $markpercentageID) {
                        $f = false;
                        if(isset($uniquepercentageArr['own']) && in_array($markpercentageID, $uniquepercentageArr['own'])) {
                            $f = true;
                        }
                        if(isset($teacherPosition[$exam->examID][$teacherID]['subjectMark'][$teacherSubject->subjectID]) && $f) {
                            if($teacherPosition[$exam->examID][$teacherID]['subjectStudent'][$teacherSubject->subjectID] > 0) {
                                $subjectmean = ini_round(($teacherPosition[$exam->examID][$teacherID]['subjectMark'][$teacherSubject->subjectID]) / ($teacherPosition[$exam->examID][$teacherID]['subjectStudent'][$teacherSubject->subjectID]));
                                $data .= "['". $teacherSubject->subjectID ."', ". $subjectmean ."],";
                            } else {
                                $data .= "['". $teacherSubject->subjectID ."', 0],";
                            }
                        } else {
                            $data .= "['". $teacherSubject->subjectID ."', 0],";
                        }
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
          foreach($teacherSubjects as $teacherSubject) {
            if($teacherSubject->teacherID == $teacherID)
              $targetData .= "['". $teacherSubject->subjectID ."', ". $teacherSubject->targetmark ."],";
          }
          $targetData .= "]";
          echo "{name: 'Target', data: ". $targetData .", type: 'line'},";
        ?>
      ];

      $('div[class="charts"][id="'+ teacherID +'"] .container').highcharts({
        subjects,
        title: {
          text: 'Teacher\'s subject performance trend',
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
