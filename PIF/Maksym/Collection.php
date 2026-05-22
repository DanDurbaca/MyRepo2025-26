<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];

$cid = isset($_GET['cid']) ? (int)$_GET['cid'] : 0;
if ($cid <= 0) {
    echo "Invalid collection ID.";
    exit();
}

$col = getCollectionByID($cid);
if (!$col) {
    echo "Collection not found.";
    exit();
}

if (!canUserAccessCollection($username, $cid)) {
    echo "You are not authorized to view this collection.";
    exit();
}

$measurements = getMeasurementsForCollection($cid);
$showChart = isset($_GET['chart']);
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo htmlspecialchars($col['name']); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <?php if ($showChart): ?>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <?php endif; ?>
</head>
<body>
  <?php NavigationBar('collections'); ?>

  <div class="container">
    <div class="card">
      <h1><?php echo htmlspecialchars($col['name']); ?></h1>
      <div style="color:var(--muted); font-size:.82rem; margin-bottom:.5rem;"><?php echo t('creator'); ?>: <?php echo htmlspecialchars($col['firstName'] . ' ' . $col['lastName']); ?> (<?php echo htmlspecialchars($col['fk_user_creates']); ?>)</div>
      <?php if (!empty($col['description'])): ?>
        <div style="margin-bottom:1rem;"><?php echo nl2br(htmlspecialchars($col['description'])); ?></div>
      <?php endif; ?>

      <div class="tab-bar">
        <a href="Collection.php?cid=<?php echo $cid; ?>" class="tab-btn <?php if (!$showChart) echo 'active'; ?>" style="text-decoration:none;"><?php echo t('table_view'); ?></a>
        <a href="Collection.php?cid=<?php echo $cid; ?>&chart=1" class="tab-btn <?php if ($showChart) echo 'active'; ?>" style="text-decoration:none;"><?php echo t('chart_view'); ?></a>
      </div>

      <?php if (count($measurements) === 0): ?>
        <div class="empty"><?php echo t('no_measurements'); ?></div>
      <?php elseif ($showChart): ?>
        <div class="card-title"><?php echo t('select_metric'); ?>:</div>
        <div style="display:flex; gap:.5rem; flex-wrap:wrap; margin-bottom:1.2rem;" id="metricBtns">
          <button type="button" class="btn-sm metric-btn active" data-m="temperature"><?php echo t('temperature'); ?></button>
          <button type="button" class="btn-sm metric-btn" data-m="humidity"><?php echo t('humidity'); ?></button>
          <button type="button" class="btn-sm metric-btn" data-m="pressure"><?php echo t('pressure'); ?></button>
          <button type="button" class="btn-sm metric-btn" data-m="light"><?php echo t('light'); ?></button>
          <button type="button" class="btn-sm metric-btn" data-m="gas"><?php echo t('gas'); ?></button>
        </div>
        <canvas id="collectionChart" height="80"></canvas>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Timestamp</th>
                <th><?php echo t('temperature'); ?> (&deg;C)</th>
                <th><?php echo t('humidity'); ?> (%)</th>
                <th><?php echo t('pressure'); ?> (hPa)</th>
                <th><?php echo t('light'); ?> (lux)</th>
                <th><?php echo t('gas'); ?> (ppm)</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($measurements as $m): ?>
                <tr>
                  <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($m['timestamp']))); ?></td>
                  <td><?php echo number_format((float)$m['temperature'], 2); ?></td>
                  <td><?php echo number_format((float)$m['humidity'], 2); ?></td>
                  <td><?php echo number_format((float)$m['pressure'], 2); ?></td>
                  <td><?php echo number_format((float)$m['light'], 2); ?></td>
                  <td><?php echo number_format((float)$m['gas'], 2); ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <?php if ($showChart && count($measurements) > 0): ?>
  <script>
    var rawData = <?php echo json_encode(array_map(function($m) {
        return [
            'ts'          => substr($m['timestamp'], 0, 16),
            'temperature' => (float)$m['temperature'],
            'humidity'    => (float)$m['humidity'],
            'pressure'    => (float)$m['pressure'],
            'light'       => (float)$m['light'],
            'gas'         => (float)$m['gas']
        ];
    }, array_reverse($measurements))); ?>;
    var mColors = { temperature:'#ef4444', humidity:'#00d4ff', pressure:'#6c63ff', light:'#f97316', gas:'#22c55e' };
    var mLabels = { temperature:'<?php echo t('temperature'); ?> (°C)', humidity:'<?php echo t('humidity'); ?> (%)', pressure:'<?php echo t('pressure'); ?> (hPa)', light:'<?php echo t('light'); ?> (lux)', gas:'<?php echo t('gas'); ?> (ppm)' };
    var chartInst = null;

    function renderChart(m) {
      if (chartInst) chartInst.destroy();
      var textCol  = $('html').css('--text') || '#e2e8f0';
      var mutedCol = $('html').css('--muted') || '#4a5568';
      chartInst = new Chart($('#collectionChart')[0].getContext('2d'), {
        type: 'line',
        data: {
          labels: rawData.map(function(d){ return d.ts; }),
          datasets: [{
            label: mLabels[m],
            data: rawData.map(function(d){ return d[m]; }),
            borderColor: mColors[m],
            backgroundColor: mColors[m] + '22',
            fill: true,
            tension: .3,
            borderWidth: 2,
            pointRadius: rawData.length > 150 ? 0 : 2
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { labels: { color: textCol } } },
          scales: {
            x: { ticks: { maxTicksLimit:8, color: mutedCol }, grid: { color: 'rgba(127,127,127,0.1)' } },
            y: { ticks: { color: mutedCol }, grid: { color: 'rgba(127,127,127,0.1)' } }
          }
        }
      });
    }

    $(document).ready(function() {
      renderChart('temperature');
      $('.metric-btn').on('click', function() {
        $('.metric-btn').removeClass('active');
        $(this).addClass('active');
        renderChart($(this).data('m'));
      });
    });
  </script>
  <?php endif; ?>
</body>
</html>
