<?php
include 'CommonCode.php';
requireLogin();

$username = $_SESSION['username'];
$success_message = '';
$error_message = '';

// Create-collection from filtered measurements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_collection'])) {
    $serial_post = isset($_POST['station']) ? trim($_POST['station']) : '';
    $start_post  = isset($_POST['start']) && $_POST['start'] !== '' ? $_POST['start'] : '';
    $end_post    = isset($_POST['end'])   && $_POST['end']   !== '' ? $_POST['end']   : '';
    $colName     = isset($_POST['collection_name'])        ? trim($_POST['collection_name'])        : '';
    $colDesc     = isset($_POST['collection_description']) ? trim($_POST['collection_description']) : '';

    if ($colName === '') {
        $error_message = 'Collection name is required.';
    } else {
        $startSql = $start_post !== '' ? str_replace('T', ' ', $start_post) . ':00' : '';
        $endSql   = $end_post   !== '' ? str_replace('T', ' ', $end_post)   . ':00' : '';
        $measurementIDs = fetchMeasurementIDs($serial_post, $startSql, $endSql);
        if (count($measurementIDs) === 0) {
            $error_message = 'No measurements to add to collection.';
        } else {
            list($ok, $msg, $newCID) = createCollectionFromMeasurements($username, $colName, $colDesc, $measurementIDs);
            if ($ok) $success_message = $msg; else $error_message = $msg;
        }
    }
}

// Delete selected measurements
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
    $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'Admin';
    $deleted = 0;
    foreach ($_POST['delete_ids'] as $rawId) {
        $mid = (int)$rawId;
        if ($mid > 0 && deleteMeasurement($mid, $username, $isAdmin)) $deleted++;
    }
    $success_message = "Deleted $deleted measurement(s).";
}

$stations = fetchStationsForUser($username);

$selectedSerial = isset($_GET['station']) ? trim($_GET['station']) : ($stations[0]['pk_serialNumber'] ?? '');
$start = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : '';
$end   = isset($_GET['end'])   && $_GET['end']   !== '' ? $_GET['end']   : '';

$measurements = [];
if ($selectedSerial !== '') {
    $st = getStationBySerial($selectedSerial);
    if ($st && $st['fk_user_owns'] === $username) {
        $startSql = $start !== '' ? str_replace('T', ' ', $start) . ':00' : '';
        $endSql   = $end   !== '' ? str_replace('T', ' ', $end)   . ':00' : '';
        $measurements = fetchMeasurementsForStation($selectedSerial, $startSql, $endSql);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo getLang(); ?>" data-theme="<?php echo getTheme(); ?>">
<head>
  <meta charset="UTF-8" />
  <title>PIF - <?php echo t('measurements'); ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@300;400&display=swap" rel="stylesheet">
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
  <style>
    /* Live indicator dot */
    #liveIndicator {
      display: inline-flex;
      align-items: center;
      gap: .4rem;
      font-size: .78rem;
      color: var(--muted);
      margin-left: .6rem;
      vertical-align: middle;
    }
    #liveIndicator .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #22c55e;
      animation: pulse 2s infinite;
    }
    #liveIndicator.paused .dot {
      background: var(--muted);
      animation: none;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: .5; transform: scale(.75); }
    }
    /* Flash highlight for new rows */
    @keyframes rowFlash {
      from { background: rgba(34,197,94,.18); }
      to   { background: transparent; }
    }
    .row-new { animation: rowFlash 1.5s ease-out forwards; }
  </style>
</head>
<body>
  <?php NavigationBar('measurements'); ?>

  <div class="container">
    <div class="page-title"><?php echo t('station_measurements'); ?></div>
    <div class="page-sub"><?php echo t('measurements_desc'); ?></div>

    <?php if ($success_message): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="card" style="margin-bottom:1.4rem;">
      <form method="GET" id="filterForm">
        <div class="form-row">
          <label><?php echo t('station'); ?>:</label>
          <select name="station" id="stationSelect">
            <?php if (count($stations) === 0): ?>
              <option value=""><?php echo t('no_stations'); ?></option>
            <?php endif; ?>
            <?php foreach ($stations as $st): ?>
              <option value="<?php echo htmlspecialchars($st['pk_serialNumber']); ?>" <?php if ($st['pk_serialNumber'] === $selectedSerial) echo 'selected'; ?>>
                <?php echo htmlspecialchars($st['pk_serialNumber'] . ($st['name'] ? ' - ' . $st['name'] : '')); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <label><?php echo t('from'); ?>:</label>
          <input type="datetime-local" name="start" id="filterStart" value="<?php echo htmlspecialchars($start); ?>" />
          <label><?php echo t('to'); ?>:</label>
          <input type="datetime-local" name="end" id="filterEnd" value="<?php echo htmlspecialchars($end); ?>" />
          <button type="submit"><?php echo t('show'); ?></button>
          <button type="button" id="createCollectionBtn"><?php echo t('create_collection'); ?></button>
          <!-- Live toggle -->
          <button type="button" id="liveToggleBtn" style="margin-left:.3rem;" title="Pause/resume live updates">⏸ <?php echo t('pause') ?: 'Pause'; ?></button>
          <span id="liveIndicator"><span class="dot"></span> LIVE</span>
        </div>
      </form>
    </div>

    <?php if (count($stations) === 0): ?>
      <div class="card"><div class="empty"><?php echo t('no_stations'); ?> <a href="Stations.php" style="color:var(--accent);"><?php echo t('register_station'); ?></a></div></div>
    <?php elseif (count($measurements) === 0): ?>
      <div id="mainContent">
        <div class="card"><div class="empty" id="emptyMsg"><?php echo t('no_measurements'); ?></div></div>
      </div>
    <?php else: ?>

      <div id="mainContent">
        <div class="tab-bar">
          <button type="button" class="tab-btn active" id="tabTableBtn"><?php echo t('table_view'); ?></button>
          <button type="button" class="tab-btn" id="tabChartBtn"><?php echo t('chart_view'); ?></button>
        </div>

        <div id="panelTable">
          <div class="card">
            <form method="POST" id="deleteForm">
              <div class="flex-between">
                <span style="color:var(--muted);font-size:.82rem;" id="recordCount"><?php echo count($measurements); ?> <?php echo t('records'); ?></span>
                <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                  <button type="button" class="btn-sm" id="selectAllBtn"><?php echo t('all'); ?></button>
                  <button type="button" class="btn-sm" id="selectNoneBtn"><?php echo t('none'); ?></button>
                  <button type="submit" class="danger btn-sm" onclick="return confirm('Delete selected measurements?');"><?php echo t('delete'); ?></button>
                </div>
              </div>
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr>
                      <th><input type="checkbox" id="masterCheckbox"></th>
                      <th>Timestamp</th>
                      <th><?php echo t('temperature'); ?> (&deg;C)</th>
                      <th><?php echo t('humidity'); ?> (%)</th>
                      <th><?php echo t('pressure'); ?> (hPa)</th>
                      <th><?php echo t('light'); ?> (lux)</th>
                      <th><?php echo t('gas'); ?> (ppm)</th>
                    </tr>
                  </thead>
                  <tbody id="measurementsTbody">
                    <?php foreach ($measurements as $m): ?>
                      <tr>
                        <td><input type="checkbox" class="row-cb" name="delete_ids[]" value="<?php echo (int)$m['pk_measurement']; ?>"></td>
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
            </form>
          </div>
        </div>

        <div id="panelChart" class="hidden">
          <div class="card">
            <div class="card-title"><?php echo t('select_metric'); ?>:</div>
            <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.2rem;" id="metricBtns">
              <button type="button" class="btn-sm metric-btn active" data-m="temperature"><?php echo t('temperature'); ?></button>
              <button type="button" class="btn-sm metric-btn" data-m="humidity"><?php echo t('humidity'); ?></button>
              <button type="button" class="btn-sm metric-btn" data-m="pressure"><?php echo t('pressure'); ?></button>
              <button type="button" class="btn-sm metric-btn" data-m="light"><?php echo t('light'); ?></button>
              <button type="button" class="btn-sm metric-btn" data-m="gas"><?php echo t('gas'); ?></button>
            </div>
            <canvas id="sensorChart" height="80"></canvas>
          </div>
        </div>
      </div>

    <?php endif; ?>

    <!-- Modal for create-collection -->
    <div id="collectionModal" class="modal" style="display:none;">
      <div class="modal-content">
        <h3><?php echo t('create_collection'); ?></h3>
        <div class="form-row">
          <label for="modal_collection_name"><?php echo t('name'); ?></label>
          <input id="modal_collection_name" type="text" maxlength="50" style="width:100%;" required />
        </div>
        <div class="form-row">
          <label for="modal_collection_description"><?php echo t('description'); ?></label>
          <textarea id="modal_collection_description" rows="3" style="width:100%;"></textarea>
        </div>
        <div style="margin-top:8px; text-align:right;">
          <button type="button" id="modal_cancel"><?php echo t('cancel'); ?></button>
          <button type="button" id="modal_save"><?php echo t('save'); ?></button>
        </div>
      </div>
    </div>

    <form method="POST" id="createCollectionForm" style="display:none;">
      <input type="hidden" name="create_collection" value="1" />
      <input type="hidden" name="station" id="post_station" />
      <input type="hidden" name="start" id="post_start" />
      <input type="hidden" name="end" id="post_end" />
      <input type="hidden" name="collection_name" id="post_collection_name" />
      <input type="hidden" name="collection_description" id="post_collection_description" />
    </form>

  </div>

  <script>
    /* ─── Initial data from PHP ─── */
    var rawData = <?php echo json_encode(array_map(function($m) {
        return [
            'id'          => (int)$m['pk_measurement'],
            'ts'          => substr($m['timestamp'], 0, 16),
            'temperature' => (float)$m['temperature'],
            'humidity'    => (float)$m['humidity'],
            'pressure'    => (float)$m['pressure'],
            'light'       => (float)$m['light'],
            'gas'         => (float)$m['gas']
        ];
    }, array_reverse($measurements))); ?>;

    var mColors = { temperature:'#ef4444', humidity:'#00d4ff', pressure:'#6c63ff', light:'#f97316', gas:'#22c55e' };
    var mLabels = {
      temperature:'<?php echo t('temperature'); ?> (°C)',
      humidity:'<?php echo t('humidity'); ?> (%)',
      pressure:'<?php echo t('pressure'); ?> (hPa)',
      light:'<?php echo t('light'); ?> (lux)',
      gas:'<?php echo t('gas'); ?> (ppm)'
    };
    var chartInst   = null;
    var activeMetric = 'temperature';
    var liveInterval = null;
    var isLive       = true;
    var knownIds     = new Set(rawData.map(function(d){ return d.id; }));

    /* ─── Chart ─── */
    function renderChart(m) {
      activeMetric = m;
      if (chartInst) chartInst.destroy();
      var textCol  = getComputedStyle(document.documentElement).getPropertyValue('--text').trim()  || '#e2e8f0';
      var mutedCol = getComputedStyle(document.documentElement).getPropertyValue('--muted').trim() || '#4a5568';
      chartInst = new Chart($('#sensorChart')[0].getContext('2d'), {
        type: 'line',
        data: {
          labels: $.map(rawData, function(d){ return d.ts; }),
          datasets: [{
            label: mLabels[m],
            data:  $.map(rawData, function(d){ return d[m]; }),
            borderColor: mColors[m],
            backgroundColor: mColors[m] + '22',
            borderWidth: 2,
            pointRadius: rawData.length > 150 ? 0 : 2,
            fill: true,
            tension: .35
          }]
        },
        options: {
          responsive: true,
          plugins: { legend: { labels: { color: textCol } } },
          scales: {
            x: { ticks: { maxTicksLimit:10, color: mutedCol }, grid: { color: 'rgba(127,127,127,0.12)' } },
            y: { ticks: { color: mutedCol },                   grid: { color: 'rgba(127,127,127,0.12)' } }
          }
        }
      });
    }

    /* ─── Build a table row HTML ─── */
    function buildRow(m, isNew) {
      var cls = isNew ? ' class="row-new"' : '';
      return '<tr' + cls + '>' +
        '<td><input type="checkbox" class="row-cb" name="delete_ids[]" value="' + m.id + '"></td>' +
        '<td>' + m.ts + '</td>' +
        '<td>' + m.temperature.toFixed(2) + '</td>' +
        '<td>' + m.humidity.toFixed(2) + '</td>' +
        '<td>' + m.pressure.toFixed(2) + '</td>' +
        '<td>' + m.light.toFixed(2) + '</td>' +
        '<td>' + m.gas.toFixed(2) + '</td>' +
        '</tr>';
    }

    /* ─── Poll the lightweight AJAX endpoint ─── */
    function pollMeasurements() {
      var station = $('#stationSelect').val() || '';
      var start   = $('#filterStart').val()   || '';
      var end     = $('#filterEnd').val()     || '';
      if (station === '') return;

      $.getJSON('MeasurementsAjax.php', { station: station, start: start, end: end })
        .done(function(data) {
          if (!Array.isArray(data) || data.length === 0) return;

          /* Normalise to same shape as rawData (chronological order) */
          var fresh = data.slice().reverse().map(function(m) {
            return {
              id:          parseInt(m.pk_measurement),
              ts:          m.timestamp ? m.timestamp.substring(0,16) : '',
              temperature: parseFloat(m.temperature),
              humidity:    parseFloat(m.humidity),
              pressure:    parseFloat(m.pressure),
              light:       parseFloat(m.light),
              gas:         parseFloat(m.gas)
            };
          });

          /* Find genuinely new rows */
          var newRows = fresh.filter(function(m){ return !knownIds.has(m.id); });

          if (newRows.length > 0) {
            /* Prepend new rows to the DOM table (newest-first visual order) */
            var $tbody   = $('#measurementsTbody');
            var htmlFrag = '';
            /* newRows is chronological; we want the most-recent first in the table */
            newRows.slice().reverse().forEach(function(m) {
              htmlFrag += buildRow(m, true);
              knownIds.add(m.id);
              rawData.push(m);          /* keep rawData in chronological order */
            });
            $tbody.prepend(htmlFrag);

            /* Update record count */
            $('#recordCount').text(rawData.length + ' <?php echo t('records'); ?>');

            /* Redraw chart if it is visible */
            if (!$('#panelChart').hasClass('hidden') && chartInst) {
              renderChart(activeMetric);
            }
          }

          /* Handle deletions: remove rows that are no longer in the response */
          var freshIds = new Set(fresh.map(function(m){ return m.id; }));
          var removed  = [];
          knownIds.forEach(function(id){ if (!freshIds.has(id)) removed.push(id); });
          if (removed.length > 0) {
            removed.forEach(function(id) {
              knownIds.delete(id);
              $('input.row-cb[value="' + id + '"]').closest('tr').remove();
              rawData = rawData.filter(function(m){ return m.id !== id; });
            });
            $('#recordCount').text(rawData.length + ' <?php echo t('records'); ?>');
            if (!$('#panelChart').hasClass('hidden') && chartInst) {
              renderChart(activeMetric);
            }
          }
        })
        .fail(function() {
          /* Silently ignore transient network errors; will retry next tick */
        });
    }

    /* ─── Start / stop live polling ─── */
    function startLive() {
      if (liveInterval) return;
      isLive = true;
      liveInterval = setInterval(pollMeasurements, 2000);
      $('#liveToggleBtn').text('⏸ <?php echo t('pause') ?: 'Pause'; ?>');
      $('#liveIndicator').removeClass('paused');
    }

    function stopLive() {
      clearInterval(liveInterval);
      liveInterval = null;
      isLive = false;
      $('#liveToggleBtn').text('▶ <?php echo t('resume') ?: 'Resume'; ?>');
      $('#liveIndicator').addClass('paused');
    }

    /* ─── DOM ready ─── */
    $(document).ready(function() {

      /* Master checkbox */
      $('#masterCheckbox').on('change', function() {
        $('.row-cb').prop('checked', $(this).is(':checked'));
      });

      /* Select all / none */
      $('#selectAllBtn').on('click', function() { $('.row-cb').prop('checked', true); });
      $('#selectNoneBtn').on('click', function() { $('.row-cb').prop('checked', false); });

      /* Tabs */
      $('#tabTableBtn').on('click', function() {
        $(this).addClass('active');
        $('#tabChartBtn').removeClass('active');
        $('#panelTable').removeClass('hidden');
        $('#panelChart').addClass('hidden');
      });
      $('#tabChartBtn').on('click', function() {
        $(this).addClass('active');
        $('#tabTableBtn').removeClass('active');
        $('#panelChart').removeClass('hidden');
        $('#panelTable').addClass('hidden');
        renderChart(activeMetric);
      });

      /* Metric buttons */
      $('.metric-btn').on('click', function() {
        $('.metric-btn').removeClass('active');
        $(this).addClass('active');
        renderChart($(this).data('m'));
      });

      /* Live toggle */
      $('#liveToggleBtn').on('click', function() {
        if (isLive) stopLive(); else startLive();
      });

      /* Pause live polling while a date filter is being changed to avoid thrash */
      $('#filterStart, #filterEnd, #stationSelect').on('change', function() {
        stopLive();
      });

      /* Resume after form submit doesn't apply here — page reloads; but
         re-enable if the user reverts their filter without submitting */

      /* Create-collection modal */
      $('#createCollectionBtn').on('click', function() {
        if ($('#measurementsTbody tr').length === 0) {
          alert('No measurements to create a collection.');
          return;
        }
        $('#modal_collection_name').val('');
        $('#modal_collection_description').val('');
        $('#collectionModal').css('display', 'flex');
        $('#modal_collection_name').focus();
      });

      $('#modal_cancel').on('click', function() { $('#collectionModal').hide(); });

      $('#modal_save').on('click', function() {
        var name = $('#modal_collection_name').val().trim();
        var desc = $('#modal_collection_description').val() || '';
        if (name === '') { $('#modal_collection_name').focus(); return; }
        if (name.length > 50) name = name.substring(0, 50);
        $('#post_station').val($('#stationSelect').val() || '');
        $('#post_start').val($('#filterStart').val() || '');
        $('#post_end').val($('#filterEnd').val() || '');
        $('#post_collection_name').val(name);
        $('#post_collection_description').val(desc);
        $('#collectionModal').hide();
        $('#createCollectionForm').submit();
      });

      $('#collectionModal').on('click', function(e) {
        if (e.target === this) $(this).hide();
      });

      $(document).on('keydown', function(e) {
        if (e.key === 'Escape') $('#collectionModal').hide();
      });

      /* Kick off live polling */
      startLive();
    });
  </script>
</body>
</html>
