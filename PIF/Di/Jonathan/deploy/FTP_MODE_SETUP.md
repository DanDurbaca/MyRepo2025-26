# PIF Backup System — FTP Mode Setup

## Overview
The backup system is now configured for **FTP mode**, meaning:
- Backup files are stored on a remote FTP server at **192.168.178.128**
- Admin panel (`admin/backups.php`) lists/downloads/deletes via FTP
- Local cron job uploads backups via FTP (optional)

## Environment Variables

The admin panel reads these environment variables:
```
BACKUP_FTP_HOST=192.168.178.128
BACKUP_FTP_USER=backupuser
BACKUP_FTP_PASS=Jojo+6842-
BACKUP_FTP_PATH=/srv/pif_backups
```

## Setup on Your Apache Server

### Option 1: Use .htaccess (easiest)
Add to your project root `.htaccess`:
```apache
SetEnv BACKUP_FTP_HOST 192.168.178.128
SetEnv BACKUP_FTP_USER backupuser
SetEnv BACKUP_FTP_PASS "Jojo+6842-"
SetEnv BACKUP_FTP_PATH /srv/pif_backups
```

### Option 2: Apache Site Config (more secure)
Edit your Apache site config (e.g., `/etc/apache2/sites-available/pif.conf`):
```apache
<VirtualHost *:80>
    ServerName your-pif-domain.com
    
    SetEnv BACKUP_FTP_HOST 192.168.178.128
    SetEnv BACKUP_FTP_USER backupuser
    SetEnv BACKUP_FTP_PASS "Jojo+6842-"
    SetEnv BACKUP_FTP_PATH /srv/pif_backups
    
    DocumentRoot /path/to/pif
    ...
</VirtualHost>
```

Then reload Apache:
```bash
sudo systemctl reload apache2
```

### Option 3: Environment File (most secure)
Create `/etc/pif-backup.env`:
```
BACKUP_FTP_HOST=192.168.178.128
BACKUP_FTP_USER=backupuser
BACKUP_FTP_PASS=Jojo+6842-
BACKUP_FTP_PATH=/srv/pif_backups
```

Set permissions:
```bash
sudo chmod 600 /etc/pif-backup.env
sudo chown www-data:www-data /etc/pif-backup.env
```

Then in your Apache site config:
```apache
Include /etc/pif-backup.env
```

## Using the Admin Panel

Once env vars are set, visit `https://your-host/admin/backups.php`:
- **Access mode** should show: `ftp`
- **List** backups stored on the FTP server
- **Download** backup files (downloads via FTP)
- **Delete** backup files (deletes on FTP, logs to backup log)

## Automatic Backup Uploads (Optional)

To have backups upload to FTP automatically (instead of storing locally), you can:

### Option A: Use an FTP upload wrapper
Create a script that runs after each backup and uploads to FTP:
```bash
#!/bin/bash
export BACKUP_FTP_HOST=192.168.178.128
export BACKUP_FTP_USER=backupuser
export BACKUP_FTP_PASS="Jojo+6842-"
export BACKUP_LOCAL_DIR=/tmp/pif_backups

# Run the backup script (creates local dump)
/usr/local/bin/pif_backup.sh

# Upload to FTP
LATEST=$(ls -t /tmp/pif_backups/*.sql.gz | head -1)
if [ -f "$LATEST" ]; then
  lftp -e "cd /srv/pif_backups; put $LATEST; quit" \
    -u backupuser,Jojo+6842- 192.168.178.128
fi
```

### Option B: Disable local cron, use remote backup
If the backup server (192.168.178.128) already has a backup script running, you can:
- Disable the local cron job: `crontab -e` and comment out/remove the PIF backup line
- Let the remote FTP server handle all backups

## Troubleshooting

### Admin panel shows "Access mode: none"
- Env vars are not set or not being passed to PHP
- Check: `php -r "echo getenv('BACKUP_FTP_HOST');"`
- Restart Apache after setting env vars: `sudo systemctl restart apache2`

### FTP connection fails ("FTP connect failed")
- Verify FTP server is running: `telnet 192.168.178.128 21`
- Check FTP credentials: `ftp 192.168.178.128` and login with `backupuser`
- Verify firewall allows port 21 (FTP) from your web server to 192.168.178.128

### "FTP login failed"
- Double-check username and password (case-sensitive)
- Verify FTP user exists on backup server: `sudo grep backupuser /etc/passwd`

### "Unable to list FTP directory"
- Ensure FTP path `/srv/pif_backups` exists on the FTP server
- Check FTP user has read/list permissions: `chmod 750 /srv/pif_backups`

## Files Modified Today

- `deploy/pif_backup.sh` — Fixed SQL corruption (pipefail + proper tmpfile handling)
- `.htaccess-ftp-backup` — Example Apache FTP configuration
- `deploy/BACKUP_SETUP.md` — Original setup guide (see for local mode)

## Next Steps

1. Copy the FTP env vars into your `.htaccess` or Apache site config
2. Restart Apache
3. Visit Admin → Backups — should now show "Access mode: ftp"
4. Test download/delete operations
5. (Optional) Disable local cron job if you don't need local backups
