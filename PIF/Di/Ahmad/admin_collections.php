<?php 
include 'includes/header.php'; 

// --- SECURITY CHECK (Fixed) ---
$current_role = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';

if ($current_role !== 'admin') {
    echo "<div class='card'>
            <h2 style='color: #ef4444;'>🚫 Access Denied</h2>
            <p>Role detected: [" . htmlspecialchars($current_role) . "]</p>
            <a href='/index.php'>Return Home</a>
          </div>";
    include 'includes/footer.php';
    exit();
}

$msg = "";

// --- DELETE COLLECTION ---
if (isset($_POST['delete_col'])) {
    $cid = $conn->real_escape_string($_POST['collection_id']);
    $conn->query("DELETE FROM collection WHERE pk_id = '$cid'");
    $msg = "<p style='background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 10px; border-radius: 5px;'>🗑️ Collection deleted.</p>";
}

// --- RENAME COLLECTION ---
if (isset($_POST['rename_col'])) {
    $cid = $conn->real_escape_string($_POST['collection_id']);
    $newName = $conn->real_escape_string($_POST['new_name']);
    $conn->query("UPDATE collection SET name = '$newName' WHERE pk_id = '$cid'");
    $msg = "<p style='background: rgba(74, 222, 128, 0.2); color: #4ade80; padding: 10px; border-radius: 5px;'>✅ Collection renamed.</p>";
}
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0;">📂 Admin: All Collections</h2>
        <span style="background: #4338ca; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8em;">Admin Mode</span>
    </div>

    <?= $msg ?>
    
    <table style="width:100%; border-collapse: collapse; margin-top: 20px;">
        <thead>
            <tr style="text-align:left; border-bottom: 2px solid #334155; color:#94a3b8;">
                <th style="padding:12px;">ID</th>
                <th style="padding:12px;">Name</th>
                <th style="padding:12px;">Owner</th>
                <th style="padding:12px; text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $res = $conn->query("SELECT * FROM collection");
            while($row = $res->fetch_assoc()): 
                // Check if the current admin actually owns this specific collection
                $is_mine = (strcasecmp($row['fk_user_owns'], $_SESSION['username']) === 0);
            ?>
                <tr style="border-bottom: 1px solid #1e293b;">
                    <td style="padding:12px; color: #64748b;"><?= $row['pk_id'] ?></td>
                    
                    <td style="padding:12px;">
                        <form method="POST" style="display:flex; gap:5px;">
                            <input type="hidden" name="collection_id" value="<?= $row['pk_id'] ?>">
                            <input type="text" name="new_name" value="<?= htmlspecialchars($row['name']) ?>" 
                                   style="background: #1e293b; border: 1px solid #334155; color: white; padding: 4px; border-radius: 4px;">
                            <button type="submit" name="rename_col" style="background:#3b82f6; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size:0.7em;">Save</button>
                        </form>
                    </td>
                    
                    <td style="padding:12px;"><?= htmlspecialchars($row['fk_user_owns']) ?></td>
                    
                    <td style="padding:12px; text-align:right;">
                        <?php if($is_mine): ?>
                            <a href="share.php?id=<?= $row['pk_id'] ?>" style="background:#8b5cf6; color:white; padding: 6px 10px; border-radius:4px; font-size:0.8em; text-decoration:none; margin-right: 5px;">Share</a>
                        <?php else: ?>
                            <span style="color: #64748b; font-size: 0.8em; margin-right: 5px;">(Not Owner)</span>
                        <?php endif; ?>
                        
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete entire collection?');">
                            <input type="hidden" name="collection_id" value="<?= $row['pk_id'] ?>">
                            <button type="submit" name="delete_col" style="background:#ef4444; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size:0.8em; cursor: pointer;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
<?php include 'includes/footer.php'; ?>
