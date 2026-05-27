<?php
// TEMP DEBUG: capture fatal/shutdown errors to help diagnose intermittent 500s.
// Placed before includes so it's invoked even if an included file fatals.
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && isset($err['type'])) {
        $fatalTypes = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
        if ($err['type'] & $fatalTypes) {
            $log = '[' . date('c') . '] SHUTDOWN ERROR: ' . trim(print_r($err, true)) . PHP_EOL;
            @file_put_contents('/tmp/pif_backups_debug.log', $log, FILE_APPEND | LOCK_EX);
        }
    }
});

$pageTitle = 'Backups';
// Buffer output so downloads can send clean headers without HTML preamble.
ob_start();
require_once __DIR__ . '/_header.php';
$envLocal = getenv('BACKUP_LOCAL_DIR') ?: '';
$ftpHost = getenv('BACKUP_FTP_HOST') ?: '';
$ftpUser = getenv('BACKUP_FTP_USER') ?: '';
$ftpPass = getenv('BACKUP_FTP_PASS') ?: '';
$ftpPath = getenv('BACKUP_FTP_PATH') ?: '/srv/pif_backups';

// Try a list of reasonable local paths if no explicit env is present or readable.
$localCandidates = array_filter([
    $envLocal,
    __DIR__ . '/../backups',
    __DIR__ . '/../../backups',
    '/srv/pif_backups',
    '/var/backups/pif',
    '/var/www/sofjo685/backups',
    '/Users/jonathansofra/pif/backups'
]);

$localDir = '';
foreach ($localCandidates as $cand) {
    if ($cand && is_dir($cand) && is_readable($cand)) {
        $localDir = $cand;
        break;
    }
}

$mode = 'none';
// Allow deployment to force the backup access mode via SetEnv BACKUP_MODE
$forcedMode = strtolower(getenv('BACKUP_MODE') ?: '');
if ($forcedMode === 'local') {
    $mode = $localDir ? 'local' : 'none';
} elseif ($forcedMode === 'ftp') {
    $mode = 'ftp';
} elseif ($forcedMode === 'none') {
    $mode = 'none';
} else {
    if ($localDir) {
        $mode = 'local';
    } elseif ($ftpHost && $ftpUser && $ftpPass) {
        $mode = 'ftp';
    }
}

$errors = [];
$messages = [];

// Sanitize filename to prevent path traversal and enforce simple name characters
function safe_filename($name) {
    // Reject non-strings or empty
    if (!is_string($name) || $name === '') return '';
    // Remove any NUL bytes that could terminate strings in C calls
    $name = str_replace("\0", '', $name);
    // Trim surrounding whitespace
    $name = trim($name);
    // Disallow any path separators to prevent directory traversal
    if (strpos($name, '/') !== false || strpos($name, "\\") !== false) return '';
    // Disallow parent-segment references
    if (strpos($name, '..') !== false) return '';
    // Limit length to a sane value
    if (strlen($name) > 255) return '';
    // Only printable ASCII characters (space through ~) to avoid control chars
    if (!preg_match('/^[\x20-\x7E]+$/', $name)) return '';
    // Return cleaned basename
    $base = basename($name);
    if ($base !== $name) return '';
    return $base;
}

function start_download_response($filename, $length = null) {
    if (ob_get_level()) {
        ob_end_clean();
    }
    header('Content-Type: application/gzip');
    header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
    if ($length !== null) {
        header('Content-Length: ' . $length);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid CSRF token.';
    } else {
        $action = $_POST['action'] ?? '';
        $file = $_POST['file'] ?? '';
        $file_clean = safe_filename($file);
        if ($file_clean === '') {
            $errors[] = 'Invalid filename.';
        } else {
            if ($mode === 'local') {
                $path = $localDir . '/' . $file_clean;
                if (!is_file($path)) {
                    $errors[] = 'File not found.';
                } else {
                    if ($action === 'download') {
                        start_download_response($file_clean, filesize($path));
                        readfile($path);
                        exit;
                    }
                    if ($action === 'delete') {
                        if (@unlink($path)) {
                            $messages[] = 'Deleted ' . htmlspecialchars($file_clean);
                            @file_put_contents('/var/log/pif_backup.log', '[' . date('c') . '] admin deleted ' . $file_clean . PHP_EOL, FILE_APPEND);
                        } else {
                            $errors[] = 'Failed to delete file.';
                        }
                    }
                }
            } elseif ($mode === 'ftp') {
                $conn = @ftp_connect($ftpHost);
                if (!$conn) {
                    $errors[] = 'FTP connect failed.';
                } else {
                    if (!@ftp_login($conn, $ftpUser, $ftpPass)) {
                        $errors[] = 'FTP login failed.';
                        ftp_close($conn);
                    } else {
                        ftp_pasv($conn, true);
                        $remote = rtrim($ftpPath, '/') . '/' . $file_clean;
                        if ($action === 'download') {
                            $tmp = tempnam(sys_get_temp_dir(), 'pifbk_');
                            if (@ftp_get($conn, $tmp, $remote, FTP_BINARY)) {
                                start_download_response($file_clean, filesize($tmp));
                                readfile($tmp);
                                @unlink($tmp);
                                ftp_close($conn);
                                exit;
                            } else {
                                $errors[] = 'FTP download failed.';
                                @unlink($tmp);
                                ftp_close($conn);
                            }
                        }
                        if ($action === 'delete') {
                            if (@ftp_delete($conn, $remote)) {
                                $messages[] = 'Deleted ' . htmlspecialchars($file_clean);
                                @file_put_contents('/var/log/pif_backup.log', '[' . date('c') . '] admin deleted ' . $file_clean . PHP_EOL, FILE_APPEND);
                            } else {
                                $errors[] = 'FTP delete failed.';
                            }
                            ftp_close($conn);
                        }
                    }
                }
            } else {
                $errors[] = 'No backup access configured.';
            }
        }
    }
}

$backups = [];
if ($mode === 'local') {
    $files = array_diff(scandir($localDir), ['.', '..']);
    foreach ($files as $f) {
        $full = $localDir . '/' . $f;
        if (!is_file($full)) continue;
        $backups[] = ['name' => $f, 'size' => filesize($full), 'mtime' => filemtime($full)];
    }
    usort($backups, function($a,$b){ return $b['mtime'] <=> $a['mtime']; });
} elseif ($mode === 'ftp') {
    $conn = @ftp_connect($ftpHost);
    if ($conn && @ftp_login($conn, $ftpUser, $ftpPass)) {
        ftp_pasv($conn, true);
        $list = @ftp_nlist($conn, $ftpPath) ?: [];
        foreach ($list as $r) {
            $base = basename($r);
            // try to get size and mtime
            $size = @ftp_size($conn, $r);
            $mtime = @ftp_mdtm($conn, $r);
            if ($size === -1) $size = null;
            if ($mtime === -1) $mtime = null;
            $backups[] = ['name' => $base, 'size' => $size, 'mtime' => $mtime];
        }
        ftp_close($conn);
        usort($backups, function($a,$b){ return ($b['mtime'] ?? 0) <=> ($a['mtime'] ?? 0); });
    } else {
        $errors[] = 'Unable to list FTP directory.';
    }
}

?>
<div class="container">
    <h1>Backups</h1>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul><?php foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>'; ?></ul></div>
    <?php endif; ?>
    <?php if (!empty($messages)): ?>
        <div class="alert alert-success"><ul><?php foreach ($messages as $m) echo '<li>' . htmlspecialchars($m) . '</li>'; ?></ul></div>
    <?php endif; ?>

    <p>Access mode: <strong><?php echo htmlspecialchars($mode); ?></strong></p>
    <?php if ($mode === 'none'): ?>
        <p>No backup access configured.</p>
        <p>Attempted local paths:</p>
        <ul>
            <?php foreach ($localCandidates as $c): ?>
                <li><?php echo htmlspecialchars($c); ?></li>
            <?php endforeach; ?>
        </ul>
        <p>Fix options:</p>
        <ul>
            <li>Mount or make the backup directory readable by the web server and set `BACKUP_LOCAL_DIR` to that path (example: <code>SetEnv BACKUP_LOCAL_DIR /srv/pif_backups</code> in Apache site config).</li>
            <li>Or configure FTP access on the backup host and set `BACKUP_FTP_HOST`, `BACKUP_FTP_USER`, and `BACKUP_FTP_PASS`.</li>
        </ul>
        <p>See <a href="../L6_backup_plan.txt">L6 backup plan</a> for setup instructions.</p>
    <?php else: ?>
        <?php if (count($backups) === 0): ?>
            <p>No backup files found.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Filename</th><th>Size</th><th>Modified</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($backups as $b): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($b['name']); ?></td>
                            <td><?php echo $b['size'] !== null ? number_format($b['size']) . ' bytes' : '-'; ?></td>
                            <td><?php echo $b['mtime'] ? date('Y-m-d H:i:s', $b['mtime']) : '-'; ?></td>
                            <td>
                                <form method="post" style="display:inline">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                                    <input type="hidden" name="action" value="download">
                                    <button class="btn" type="submit">Download</button>
                                </form>
                                <form method="post" style="display:inline" onsubmit="return confirm('Delete this backup?');">
                                    <?php echo csrf_input(); ?>
                                    <input type="hidden" name="file" value="<?php echo htmlspecialchars($b['name']); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button class="btn btn-danger" type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    <?php endif; ?>
</div>

</body>
</html>
