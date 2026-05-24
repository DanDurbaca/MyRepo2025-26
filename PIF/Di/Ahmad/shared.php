<?php 
include 'includes/header.php'; 

if (!isset($_SESSION['username'])) { 
    header("Location: /login.php"); 
    exit(); 
}

$user = $_SESSION['username'];
?>

<div class="card">
    <h2 style="color: #a78bfa;">📂 Collections Shared With Me</h2>
    <p style="color:#94a3b8; font-size:0.9em; margin-bottom:25px;">
        These collections were created by your friends and shared with you.
    </p>

    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <?php 
        // Logic: JOIN collection with hasaccess to find where recipient = current user
        $sql = "SELECT c.*, u.pk_username as owner_name 
                FROM collection c 
                JOIN hasaccess h ON c.pk_collection = h.fk_collection 
                JOIN user u ON c.fk_user_creates = u.pk_username
                WHERE h.fk_user_recipient = '$user'
                ORDER BY c.pk_collection DESC";
        
        $shared = $conn->query($sql);
        
        if ($shared && $shared->num_rows > 0):
            while($c = $shared->fetch_assoc()): ?>
                <div style="background: #1e1b4b; padding: 20px; border-radius: 8px; border: 1px solid #4338ca; position: relative;">
                    <div style="position: absolute; top: 10px; right: 10px; background: #4338ca; color: white; font-size: 0.7em; padding: 2px 8px; border-radius: 4px; font-weight: bold;">
                        SHARED
                    </div>
                    
                    <h3 style="margin: 0 0 10px 0; color: #c4b5fd;"><?= htmlspecialchars($c['name']) ?></h3>
                    
                    <div style="font-size: 0.85em; color: #a5b4fc; line-height: 1.6; margin-bottom: 20px;">
                        <strong>Owner:</strong> <?= htmlspecialchars($c['owner_name']) ?><br>
                        <strong>Station:</strong> <?= htmlspecialchars($c['fk_station_source']) ?><br>
                        <strong>Start:</strong> <?= $c['start_timestamp'] ?><br>
                        <strong>End:</strong> <?= $c['end_timestamp'] ?>
                    </div>

                    <div style="display: flex; gap: 10px;">
                        <a href="/view_collection.php?id=<?= $c['pk_collection'] ?>" 
                           style="background: #6366f1; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none; font-size: 0.85em; width: 100%; text-align: center; font-weight: bold;">
                           View Measurement Data
                        </a>
                    </div>
                </div>
            <?php endwhile;
        else:
            echo "
            <div style='grid-column: 1 / -1; text-align: center; padding: 40px; background: #0f172a; border-radius: 8px; border: 1px dashed #334155;'>
                <p style='color:#64748b;'>No collections have been shared with you yet.</p>
                <p style='font-size: 0.8em; color: #475569;'>Once a friend shares a collection, it will appear here.</p>
            </div>";
        endif; 
        ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
