<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];

// Get stations with their latest measurement
$stations = fetchStationsWithLatestMeasurement($username);

// Counts for the stat tiles
$friendCount     = count(getFriends($username));
$collectionCount = count(getUserCollections($username));
$sharedCount     = count(getCollectionsSharedWithUser($username));
$pendingCount    = count(getIncomingRequests($username));

// 24h chart data for the first station
$chartSeries = [];
if (!empty($stations)) {
    $chartSeries = fetchLast24hForStation($stations[0]['pk_serialNumber']);
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('dashboard'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
</head>
<body>
  <?php NavigationBar('dashboard'); ?>

  <div class="container">

    <div style="margin-bottom: 1.6rem;">
      <div class="page-title"><?php echo t('welcome_back'); ?>, <?php echo htmlspecialchars($_SESSION['firstName']); ?></div>
      <div class="page-sub"><?php echo t('overview'); ?></div>
    </div>

    <div id="pending-alert" class="alert alert-info" style="display:<?php echo $pendingCount > 0 ? 'flex' : 'none'; ?>;align-items:center;gap:.6rem;">
      <?php if ($pendingCount > 0): ?>
      <?php echo sprintf(t('pending_friend_request_msg'), $pendingCount); ?>
      <a href="Friends.php" style="color:var(--accent);font-weight:600;margin-left:.25rem;"><?php echo t('view'); ?> &rarr;</a>
      <?php endif; ?>
    </div>

    <!-- Stat tiles -->
    <div class="stats">
      <div class="stat" style="--s-color:#00d4ff">
        <div class="stat-val" id="val-stations"><?php echo count($stations); ?></div>
        <div class="stat-label"><?php echo t('stations'); ?></div>
      </div>
      <div class="stat" style="--s-color:#818cf8">
        <div class="stat-val" id="val-friends"><?php echo $friendCount; ?></div>
        <div class="stat-label"><?php echo t('friends'); ?></div>
      </div>
      <div class="stat" style="--s-color:#f97316">
        <div class="stat-val" id="val-collections"><?php echo $collectionCount; ?></div>
        <div class="stat-label"><?php echo t('collections'); ?></div>
      </div>
      <div class="stat" style="--s-color:#4ade80">
        <div class="stat-val" id="val-shared"><?php echo $sharedCount; ?></div>
        <div class="stat-label"><?php echo t('shared_with_me'); ?></div>
      </div>
    </div>

    <!-- 24h chart for first station -->
    <?php if (!empty($chartSeries) && count($chartSeries) > 1): ?>
    <div class="card" style="margin-bottom:1rem;">
      <div class="card-title">
        <?php echo t('last_24h'); ?> - <?php echo htmlspecialchars($stations[0]['name'] ? $stations[0]['name'] : $stations[0]['pk_serialNumber']); ?>
      </div>
      <canvas id="dashChart" height="55"></canvas>
    </div>
    <?php endif; ?>

    <!-- Station cards -->
    <div id="station-grid">
    <?php if (count($stations) === 0): ?>
      <div class="card">
        <div class="empty">
          <?php echo t('no_stations'); ?><br />
          <a href="Stations.php"><button type="button" style="margin-top:1.2rem;"><?php echo t('register_station'); ?></button></a>
        </div>
      </div>
    <?php else: ?>
      <div class="grid-2">
        <?php foreach ($stations as $s):
          $tc = tempColor($s['temperature']);
          $gc = gasColor($s['gas']);
          $gl = gasLabel($s['gas']);
        ?>
          <div class="card card-hover">
            <div class="station-card-head">
              <div>
                <div class="station-name"><?php echo htmlspecialchars($s['name'] ? $s['name'] : $s['pk_serialNumber']); ?></div>
                <?php if (!empty($s['description'])): ?>
                  <div style="font-size:.73rem;color:var(--muted);margin-top:.18rem;"><?php echo htmlspecialchars($s['description']); ?></div>
                <?php endif; ?>
              </div>
              <code class="station-sn"><?php echo htmlspecialchars($s['pk_serialNumber']); ?></code>
            </div>

            <?php if ($s['timestamp'] !== null): ?>
              <div class="sensor-grid">
                <div class="sensor-card" style="--s-color:<?php echo $tc; ?>">
                  <div class="sensor-lbl-row"><span class="sensor-lbl"><?php echo t('temperature'); ?></span></div>
                  <div class="sensor-val"><?php echo number_format((float)$s['temperature'], 1); ?><span class="sensor-unit">&deg;C</span></div>
                </div>
                <div class="sensor-card" style="--s-color:#00d4ff">
                  <div class="sensor-lbl-row"><span class="sensor-lbl"><?php echo t('humidity'); ?></span></div>
                  <div class="sensor-val"><?php echo number_format((float)$s['humidity'], 1); ?><span class="sensor-unit">%</span></div>
                </div>
                <div class="sensor-card" style="--s-color:#818cf8">
                  <div class="sensor-lbl-row"><span class="sensor-lbl"><?php echo t('pressure'); ?></span></div>
                  <div class="sensor-val"><?php echo number_format((float)$s['pressure'], 0); ?><span class="sensor-unit">hPa</span></div>
                </div>
                <div class="sensor-card" style="--s-color:#fbbf24">
                  <div class="sensor-lbl-row"><span class="sensor-lbl"><?php echo t('light'); ?></span></div>
                  <div class="sensor-val"><?php echo number_format((float)$s['light'], 0); ?><span class="sensor-unit">lux</span></div>
                </div>
                <div class="sensor-card" style="--s-color:<?php echo $gc; ?>;grid-column:span 2">
                  <div class="sensor-lbl-row"><span class="sensor-lbl"><?php echo t('gas'); ?></span></div>
                  <div class="sensor-val"><?php echo number_format((float)$s['gas'], 0); ?><span class="sensor-unit">ppm</span></div>
                  <div class="aqi-badge"><?php echo htmlspecialchars($gl); ?></div>
                </div>
              </div>

              <div style="color:var(--muted);font-family:var(--mono);font-size:.68rem;margin-top:.85rem;text-align:right;letter-spacing:.03em;">
                <?php echo t('last_updated'); ?>: <?php echo htmlspecialchars(substr($s['timestamp'], 0, 16)); ?>
              </div>
            <?php else: ?>
              <div class="empty" style="padding:1.5rem;"><?php echo t('no_data'); ?></div>
            <?php endif; ?>

            <div style="margin-top:.9rem;display:flex;gap:.4rem;border-top:1px solid var(--border);padding-top:.9rem;">
              <a href="Measurements.php?station=<?php echo urlencode($s['pk_serialNumber']); ?>"><button type="button" class="btn-sm"><?php echo t('view_data'); ?></button></a>
              <a href="MyCollections.php"><button type="button" class="btn-sm"><?php echo t('collections'); ?></button></a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    </div><!-- #station-grid -->

  </div>

  <?php if (!empty($chartSeries) && count($chartSeries) > 1): ?>
  <script>
    var chartData = <?php echo json_encode(array_map(function($r) {
        return [
            't'     => substr($r['timestamp'], 0, 16),
            'temp'  => (float)$r['temperature'],
            'hum'   => (float)$r['humidity'],
            'press' => (float)$r['pressure'],
            'lux'   => (float)$r['light'],
            'aqi'   => (float)$r['gas']
        ];
    }, $chartSeries)); ?>;

    var dashChart = null;
    (function(){
      var gridCol  = 'rgba(127,127,127,0.1)';
      var mutedCol = 'rgba(148,163,184,0.55)';
      dashChart = new Chart(document.getElementById('dashChart').getContext('2d'), {
        type: 'line',
        data: {
          labels: chartData.map(function(d){ return d.t.slice(11,16); }),
          datasets: [
            { label: '<?php echo t('temperature'); ?> (°C)', data: chartData.map(function(d){return d.temp;}),  borderColor:'#fbbf24', backgroundColor:'rgba(251,191,36,0.07)', fill:true, tension:.3, pointRadius: chartData.length>80?0:2, borderWidth:2 },
            { label: '<?php echo t('humidity'); ?> (%)',     data: chartData.map(function(d){return d.hum;}),   borderColor:'#00d4ff', backgroundColor:'rgba(0,212,255,0.06)', fill:true, tension:.3, pointRadius: chartData.length>80?0:2, borderWidth:2 },
            { label: '<?php echo t('pressure'); ?> (hPa)',   data: chartData.map(function(d){return d.press;}), borderColor:'#818cf8', backgroundColor:'rgba(129,140,248,0.06)', fill:true, tension:.3, pointRadius: chartData.length>80?0:2, borderWidth:2 },
            { label: '<?php echo t('light'); ?> (lux)',      data: chartData.map(function(d){return d.lux;}),   borderColor:'#fbbf24', backgroundColor:'rgba(251,191,36,0.04)', fill:false, tension:.3, pointRadius: chartData.length>80?0:2, borderWidth:1.5 },
            { label: '<?php echo t('gas'); ?> (ppm)',        data: chartData.map(function(d){return d.aqi;}),   borderColor:'#22c55e', backgroundColor:'rgba(34,197,94,0.06)', fill:true, tension:.3, pointRadius: chartData.length>80?0:2, borderWidth:2 }
          ]
        },
        options: {
          responsive: true,
          plugins: { legend: { labels: { color: mutedCol, font: { size:11 } } } },
          scales: {
            x: { ticks: { maxTicksLimit:8, color: mutedCol, font:{ size:10 } }, grid:{ color: gridCol } },
            y: { ticks: { color: mutedCol, font:{ size:10 } }, grid:{ color: gridCol } }
          }
        }
      });
    })();
  </script>
  <?php endif; ?>

  <script>
    var pendingMsgTemplate = <?php echo json_encode(t('pending_friend_request_msg')); ?>;
    var viewText           = <?php echo json_encode(t('view')); ?>;
    var lblTemp            = <?php echo json_encode(t('temperature')); ?>;
    var lblHum             = <?php echo json_encode(t('humidity')); ?>;
    var lblPress           = <?php echo json_encode(t('pressure')); ?>;
    var lblLight           = <?php echo json_encode(t('light')); ?>;
    var lblGas             = <?php echo json_encode(t('gas')); ?>;
    var lblLastUpd         = <?php echo json_encode(t('last_updated')); ?>;
    var lblNoData          = <?php echo json_encode(t('no_data')); ?>;
    var lblViewData        = <?php echo json_encode(t('view_data')); ?>;
    var lblCollections     = <?php echo json_encode(t('collections')); ?>;
    var lblNoStations      = <?php echo json_encode(t('no_stations')); ?>;
    var lblRegister        = <?php echo json_encode(t('register_station')); ?>;

    function tempColor(v) {
      v = parseFloat(v);
      if (isNaN(v) || v <= 0)  return '#93c5fd';
      if (v <= 10) return '#67e8f9';
      if (v <= 18) return '#34d399';
      if (v <= 24) return '#60a5fa';
      if (v <= 30) return '#fbbf24';
      return '#f87171';
    }
    function gasColor(v) {
      v = parseFloat(v);
      if (isNaN(v) || v < 800)  return '#4ade80';
      if (v < 1500) return '#fbbf24';
      return '#f87171';
    }
    function gasLabel(v) {
      v = parseFloat(v);
      if (isNaN(v) || v < 800)  return 'Good';
      if (v < 1500) return 'Fair';
      return 'Poor';
    }
    function esc(s) { return $('<span>').text(s || '').html(); }

    function updateDashboard(data) {
      // Stat tiles
      $('#val-stations').text(data.stations.length);
      $('#val-friends').text(data.friendCount);
      $('#val-collections').text(data.collectionCount);
      $('#val-shared').text(data.sharedCount);

      // Pending alert
      if (data.pendingCount > 0) {
        var msg = pendingMsgTemplate.replace('%d', data.pendingCount);
        $('#pending-alert').html(msg + ' <a href="Friends.php" style="color:var(--accent);font-weight:600;margin-left:.25rem;">' + esc(viewText) + ' &rarr;</a>').show();
      } else {
        $('#pending-alert').hide();
      }

      // Station cards
      if (data.stations.length === 0) {
        $('#station-grid').html('<div class="card"><div class="empty">' + esc(lblNoStations) + '<br><a href="Stations.php"><button type="button" style="margin-top:1.2rem;">' + esc(lblRegister) + '</button></a></div></div>');
      } else {
        var html = '<div class="grid-2">';
        $.each(data.stations, function(i, s) {
          var name = s.name ? s.name : s.pk_serialNumber;
          var tc = tempColor(s.temperature);
          var gc = gasColor(s.gas);
          var gl = gasLabel(s.gas);
          html += '<div class="card card-hover">';
          html += '<div class="station-card-head"><div>';
          html += '<div class="station-name">' + esc(name) + '</div>';
          if (s.description) {
            html += '<div style="font-size:.73rem;color:var(--muted);margin-top:.18rem;">' + esc(s.description) + '</div>';
          }
          html += '</div><code class="station-sn">' + esc(s.pk_serialNumber) + '</code></div>';

          if (s.timestamp) {
            html += '<div class="sensor-grid">';
            html += '<div class="sensor-card" style="--s-color:' + tc + '"><div class="sensor-lbl-row"><span class="sensor-lbl">' + esc(lblTemp) + '</span></div><div class="sensor-val">' + parseFloat(s.temperature).toFixed(1) + '<span class="sensor-unit">&deg;C</span></div></div>';
            html += '<div class="sensor-card" style="--s-color:#00d4ff"><div class="sensor-lbl-row"><span class="sensor-lbl">' + esc(lblHum) + '</span></div><div class="sensor-val">' + parseFloat(s.humidity).toFixed(1) + '<span class="sensor-unit">%</span></div></div>';
            html += '<div class="sensor-card" style="--s-color:#818cf8"><div class="sensor-lbl-row"><span class="sensor-lbl">' + esc(lblPress) + '</span></div><div class="sensor-val">' + Math.round(parseFloat(s.pressure)) + '<span class="sensor-unit">hPa</span></div></div>';
            html += '<div class="sensor-card" style="--s-color:#fbbf24"><div class="sensor-lbl-row"><span class="sensor-lbl">' + esc(lblLight) + '</span></div><div class="sensor-val">' + Math.round(parseFloat(s.light)) + '<span class="sensor-unit">lux</span></div></div>';
            html += '<div class="sensor-card" style="--s-color:' + gc + ';grid-column:span 2"><div class="sensor-lbl-row"><span class="sensor-lbl">' + esc(lblGas) + '</span></div><div class="sensor-val">' + Math.round(parseFloat(s.gas)) + '<span class="sensor-unit">ppm</span></div><div class="aqi-badge">' + esc(gl) + '</div></div>';
            html += '</div>';
            html += '<div style="color:var(--muted);font-family:var(--mono);font-size:.68rem;margin-top:.85rem;text-align:right;letter-spacing:.03em;">' + esc(lblLastUpd) + ': ' + esc(s.timestamp.substring(0, 16)) + '</div>';
          } else {
            html += '<div class="empty" style="padding:1.5rem;">' + esc(lblNoData) + '</div>';
          }

          html += '<div style="margin-top:.9rem;display:flex;gap:.4rem;border-top:1px solid var(--border);padding-top:.9rem;">';
          html += '<a href="Measurements.php?station=' + encodeURIComponent(s.pk_serialNumber) + '"><button type="button" class="btn-sm">' + esc(lblViewData) + '</button></a>';
          html += '<a href="MyCollections.php"><button type="button" class="btn-sm">' + esc(lblCollections) + '</button></a>';
          html += '</div></div>';
        });
        html += '</div>';
        $('#station-grid').html(html);
      }

      // Update chart data if chart exists
      if (typeof dashChart !== 'undefined' && dashChart && data.chartData && data.chartData.length > 1) {
        dashChart.data.labels           = $.map(data.chartData, function(d) { return d.t.slice(11, 16); });
        dashChart.data.datasets[0].data = $.map(data.chartData, function(d) { return d.temp; });
        dashChart.data.datasets[1].data = $.map(data.chartData, function(d) { return d.hum; });
        dashChart.data.datasets[2].data = $.map(data.chartData, function(d) { return d.press; });
        dashChart.data.datasets[3].data = $.map(data.chartData, function(d) { return d.lux; });
        dashChart.data.datasets[4].data = $.map(data.chartData, function(d) { return d.aqi; });
        dashChart.update('none');
      }
    }

    $(document).ready(function() {
      function loadDashboard() {
        $.getJSON('Api.php?action=get_dashboard', function(data) {
          updateDashboard(data);
        });
      }
      // Poll every 3 seconds like chat
      setInterval(loadDashboard, 3000);
    });
  </script>
</body>
</html>
