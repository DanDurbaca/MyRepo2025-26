<?php
$pageTitle = 'Transfer Logs';
require_once __DIR__ . '/_header.php';

$logFile = __DIR__ . '/../logs/app.log';
$errors = [];
$filters = [
    'serial' => trim($_GET['serial'] ?? ''),
    'level' => trim($_GET['level'] ?? ''),
    'q' => trim($_GET['q'] ?? ''),
];

$entries = [];
// If the log file is missing or unreadable, attempt a best-effort creation if the directory is writable.
$logDir = dirname($logFile);
if (!is_file($logFile) || !is_readable($logFile)) {
    if (!is_dir($logDir)) {
        if (!@mkdir($logDir, 0750, true)) {
            $errors[] = 'Log directory does not exist and could not be created: ' . htmlspecialchars($logDir);
        }
    }
    if (is_dir($logDir) && is_writable($logDir)) {
        if (!is_file($logFile)) {
            if (!@touch($logFile)) {
                $errors[] = 'Log file missing and could not be created. Create ' . htmlspecialchars($logFile) . ' and make it writable by the webserver user.';
            } else {
                @chmod($logFile, 0640);
            }
        } elseif (!is_readable($logFile)) {
            if (!@chmod($logFile, 0640) || !is_readable($logFile)) {
                $errors[] = 'Log file exists but is not readable by the webserver user: ' . htmlspecialchars($logFile);
            }
        }
    } else {
        if (empty($errors)) $errors[] = 'Log file not found or unreadable: ' . htmlspecialchars($logFile);
    }
}

if (empty($errors) && is_file($logFile) && is_readable($logFile)) {
    $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    // keep last 500 lines
    $lines = array_slice($lines, -500);
    foreach ($lines as $ln) {
        $j = json_decode($ln, true);
        if (!$j) continue;
        // simple filter: context.serial or context.serial exists
        $match = true;
        if ($filters['serial']) {
            $found = false;
            if (!empty($j['context']['serial']) && strpos($j['context']['serial'], $filters['serial']) !== false) $found = true;
            if (!$found && !empty($j['context']['insert_id']) && strpos((string)$j['context']['insert_id'], $filters['serial']) !== false) $found = true;
            if (!$found && strpos(json_encode($j['context']), $filters['serial']) === false) $match = false;
        }
        if ($filters['level'] && strcasecmp($j['level'], $filters['level']) !== 0) $match = false;
        if ($filters['q'] && strpos(json_encode($j), $filters['q']) === false) $match = false;
        if ($match) $entries[] = $j;
    }
    // newest first
    usort($entries, function($a,$b){ return strcmp($b['ts'] ?? '', $a['ts'] ?? ''); });
}

?>
<div class="container">
    <h1>Transfer Logs</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars(implode('; ', $errors)); ?></div>
    <?php endif; ?>

    <form method="get" style="margin-bottom:1rem;">
        <label>Station serial: <input name="serial" value="<?php echo htmlspecialchars($filters['serial']); ?>"></label>
        <label>Level: <select name="level">
            <option value="">(any)</option>
            <option value="info" <?php if($filters['level']=='info') echo 'selected'; ?>>info</option>
            <option value="error" <?php if($filters['level']=='error') echo 'selected'; ?>>error</option>
        </select></label>
        <label>Query: <input name="q" value="<?php echo htmlspecialchars($filters['q']); ?>"></label>
        <button class="btn" type="submit">Filter</button>
    </form>

    <table>
        <thead><tr><th>Time</th><th>Level</th><th>Message</th><th>Context</th></tr></thead>
        <tbody>
            <?php foreach ($entries as $e): ?>
                <tr>
                    <td><?php echo htmlspecialchars($e['ts'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($e['level'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($e['message'] ?? ''); ?></td>
                    <td><pre style="max-width:600px; white-space:pre-wrap;"><?php echo htmlspecialchars(json_encode($e['context'], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)); ?></pre></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
