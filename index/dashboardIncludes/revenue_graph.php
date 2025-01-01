<?php
function revenue_graph($startDate, $endDate) {
    # Omsætningsgraf
    global $kontomin;
    global $kontomaks;
    
    echo '
<div style="
    flex: 2;
    min-width: 500px;
    background-color: #fff;
    border-radius: 5px;
    box-shadow: rgba(99, 99, 99, 0.2) 0px 2px 8px 0px;
    padding: 1.4em 2em;
">
    <h4 style="margin: 0; color: #999">Din omsætning sammenlignet med sidste år, ekskl. moms</h4>
    <div style="flex: 1; width: 100%">
      <canvas id="myChart"></canvas>
    </div>
    </div>
    ';

    // Parse start and end dates
    $startYear = date('Y', strtotime($startDate));
    $endYear = date('Y', strtotime($endDate));
    $startMonth = date('n', strtotime($startDate));
    $endMonth = date('n', strtotime($endDate));

    $revenue_now = [];
    $revenue_last = [];

    for ($month = $startMonth; $month <= $endMonth; $month++) {
        // Generate dates for the current year
        $firstDayOfMonth = date('Y-m-d', mktime(0, 0, 0, $month, 1, $startYear));
        $lastDayOfMonth = date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $startYear));

        $q = db_select("
        SELECT SUM(COALESCE(T.kredit, 0) - COALESCE(T.debet, 0))
        FROM transaktioner T
        WHERE T.transdate >= '$firstDayOfMonth'
        AND T.transdate <= '$lastDayOfMonth'
        AND T.kontonr >= $kontomin
        AND T.kontonr <= $kontomaks
        ", __FILE__ . " linje " . __LINE__);
        
        $value = db_fetch_array($q)[0];
        array_push($revenue_now, $value);

        // Generate dates for the previous year
        $firstDayOfLastYear = date('Y-m-d', mktime(0, 0, 0, $month, 1, $startYear - 1));
        $lastDayOfLastYear = date('Y-m-d', mktime(0, 0, 0, $month + 1, 0, $startYear - 1));

        $q = db_select("
        SELECT SUM(COALESCE(T.kredit, 0) - COALESCE(T.debet, 0))
        FROM transaktioner T
        WHERE T.transdate >= '$firstDayOfLastYear'
        AND T.transdate <= '$lastDayOfLastYear'
        AND T.kontonr >= $kontomin
        AND T.kontonr <= $kontomaks
        ", __FILE__ . " linje " . __LINE__);
        
        $value = db_fetch_array($q)[0];
        array_push($revenue_last, $value);
    }

    // Prepare months' labels
    $monthLabels = [];
    for ($month = $startMonth; $month <= $endMonth; $month++) {
        $monthLabels[] = date('F', mktime(0, 0, 0, $month, 1));
    }

    // Generate chart with data for both current and last year
    echo "
    <script>
      const ctx = document.getElementById('myChart');

      new Chart(ctx, {
        type: 'bar',
        data: {
          labels: ['".implode("','", $monthLabels)."'],
          datasets: [{
            label: 'Omsætning $startYear',
            data: ['".implode("','", $revenue_now)."'],
            borderWidth: 1
          },
          {
            label: 'Omsætning ".($startYear - 1)."',
            data: ['".implode("','", $revenue_last)."'],
            borderWidth: 1
          }]
        },
        options: {
          interaction: {
            mode: 'index',
            intersect: false,
          },
          responsive: true,
          maintainAspectRatio: false,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    </script>";
}
?>
