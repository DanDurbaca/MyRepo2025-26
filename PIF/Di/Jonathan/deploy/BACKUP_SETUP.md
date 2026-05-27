# Backup System Setup

## Overview
The PIF backup system consists of:
- **Backup Script**: `deploy/pif_backup.sh` — creates gzipped SQL dumps
- **Backup Directory**: `backups/` (local testing) or `/srv/pif_backups` (production)
- **Admin Panel**: `admin/backups.php` — list, download, delete backups
- **Log File**: `backups/pif_backup.log`

## Local Setup (macOS / development)

### 1. Verify Backups Directory
```bash
ls -la /Users/jonathansofra/pif/backups
```
Should show backup files like `sofjo685_pif_2026-05-04T130904Z.sql.gz`.

### 2. Test Backup Script
```bash
cd /Users/jonathansofra/pif
BACKUP_LOCAL_DIR=/Users/jonathansofra/pif/backups \
BACKUP_LOG_FILE=/Users/jonathansofra/pif/backups/pif_backup.log \
TEST_MODE=1 \
/bin/bash deploy/pif_backup.sh
```

### 3. Set Up Automatic Daily Backups (cron)

On macOS, add a cron job:
```bash
crontab -e
```

Add this line (runs at 2 AM daily):
```cron
0 2 * * * BACKUP_LOCAL_DIR=/Users/jonathansofra/pif/backups BACKUP_LOG_FILE=/Users/jonathansofra/pif/backups/pif_backup.log /bin/bash /Users/jonathansofra/pif/deploy/pif_backup.sh
```

Or via `crontab` without edit:
```bash
(crontab -l 2>/dev/null; echo "0 2 * * * BACKUP_LOCAL_DIR=/Users/jonathansofra/pif/backups BACKUP_LOG_FILE=/Users/jonathansofra/pif/backups/pif_backup.log /bin/bash /Users/jonathansofra/pif/deploy/pif_backup.sh") | crontab -
```

### 4. Access Backups via Admin Panel
1. Login to the web app
2. Go to Admin → Backups
3. You should see listed backup files
4. Download or delete via the panel

## Production Setup (Linux server)

### 1. Install Backup Script
```bash
sudo cp deploy/pif_backup.sh /usr/local/bin/pif_backup.sh
sudo chmod 755 /usr/local/bin/pif_backup.sh
```

### 2. Create Backup Directory
```bash
sudo mkdir -p /srv/pif_backups
sudo chown root:backup /srv/pif_backups
sudo chmod 750 /srv/pif_backups
```

### 3. Configure Apache (SetEnv)
In your Apache site config (e.g., `/etc/apache2/sites-available/pif.conf`):
```apache
SetEnv BACKUP_LOCAL_DIR /srv/pif_backups
SetEnv BACKUP_LOG_FILE /var/log/pif_backup.log
```

Restart Apache:
```bash
sudo systemctl restart apache2
```

### 4. Set Up Systemd Timer
Install the systemd unit files:
```bash
sudo cp deploy/systemd/pif-backup.service /etc/systemd/system/
sudo cp deploy/systemd/pif-backup.timer /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable pif-backup.timer
sudo systemctl start pif-backup.timer
```

Check status:
```bash
sudo systemctl status pif-backup.timer
sudo systemctl status pif-backup.service
```

View logs:
```bash
sudo journalctl -u pif-backup.service -n 50 --follow
```

### 5. Set Up Log Rotation
Install logrotate config:
```bash
sudo cp deploy/logrotate.d/pif_backup /etc/logrotate.d/
```

### 6. (Optional) Mount Backup Directory via sshfs
If backups are on a remote host, mount them via sshfs:
```bash
mkdir -p /srv/pif_backups
sudo sshfs -o allow_other,IdentityFile=/root/.ssh/id_rsa backup@remote:/backups /srv/pif_backups
```

Add to `/etc/fstab` for persistent mount:
```
backup@remote:/backups /srv/pif_backups fuse.sshfs allow_other,IdentityFile=/root/.ssh/id_rsa,reconnect 0 0
```

## Admin Panel Usage

Visit `https://your-host/admin/backups.php` (login required):
- **List**: Shows all backup files with size and modification time
- **Download**: Download a backup file (`.sql.gz`)
- **Delete**: Remove a backup file (requires CSRF token)

All deletions are logged to `BACKUP_LOG_FILE`.

## Environment Variables

Set via Apache `SetEnv`, systemd `Environment=`, or shell:
- `BACKUP_LOCAL_DIR` — Directory where local backups are stored (default: `/srv/pif_backups`)
- `BACKUP_LOG_FILE` — Log file path (default: `/var/log/pif_backup.log`)
- `DB_NAME` — Database name (default: `sofjo685_pif`)
- `BACKUP_DIR` — Alias for `BACKUP_LOCAL_DIR`
- `LOGFILE` — Alias for `BACKUP_LOG_FILE`
- `TEST_MODE` — Set to `1` for testing (creates fake dump without mysqldump)

## Troubleshooting

### Admin panel shows "No backup access configured"
- Ensure `BACKUP_LOCAL_DIR` is set and readable by the web server
- Check one of the candidate paths exists: `/srv/pif_backups`, `/var/backups/pif`, etc.

### Backups not being created
- Run the script manually in TEST_MODE to verify it works
- Check cron logs: `log stream --predicate 'eventMessage contains "pif_backup"'` (macOS)
- Check systemd logs: `sudo journalctl -u pif-backup.service`

### "Invalid CSRF token" or "Invalid filename" on delete
- Ensure session cookies are not expired
- Verify filename contains only printable ASCII characters (no special Unicode)

## Next Steps

- [ ] Test backup script locally
- [ ] Add cron job (macOS) or systemd timer (Linux)
- [ ] Verify backups appear in Admin panel
- [ ] Test download and delete operations
- [ ] Set up production mount/path if needed
