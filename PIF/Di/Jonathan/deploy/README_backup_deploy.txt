Deployment instructions: install backup artifacts on the backup VM

Files added to this repo (deploy/):
- pif_backup.sh - atomic backup script (place at /usr/local/bin/pif_backup.sh)
- systemd/pif_backup.service - systemd unit (install to /etc/systemd/system/)
- systemd/pif_backup.timer   - systemd timer (install to /etc/systemd/system/)
- logrotate.d/pif_backup     - logrotate config (install to /etc/logrotate.d/)
- vsftpd.conf.sample         - sample vsftpd config if you want FTP access

Quick install steps (run on the backup VM as root):

# copy the script and units (from your workstation) e.g. using scp:
# scp deploy/pif_backup.sh root@backup:~/
# scp deploy/systemd/pif_backup.service root@backup:~/
# scp deploy/systemd/pif_backup.timer root@backup:~/
# scp deploy/logrotate.d/pif_backup root@backup:~/
# scp deploy/vsftpd.conf.sample root@backup:~/

# on the backup VM:
sudo mv ~/pif_backup.sh /usr/local/bin/pif_backup.sh
sudo chmod 750 /usr/local/bin/pif_backup.sh
sudo chown root:root /usr/local/bin/pif_backup.sh

sudo mv ~/pif_backup.service /etc/systemd/system/pif_backup.service
sudo mv ~/pif_backup.timer   /etc/systemd/system/pif_backup.timer
sudo mv ~/pif_backup /etc/logrotate.d/pif_backup || true

# reload systemd and enable timer
sudo systemctl daemon-reload
sudo systemctl enable --now pif_backup.timer
sudo systemctl status pif_backup.timer --no-pager

# install logrotate config
sudo mv ~/pif_backup /etc/logrotate.d/pif_backup || true
sudo chmod 644 /etc/logrotate.d/pif_backup

# (optional) install vsftpd config
sudo mv ~/vsftpd.conf.sample /etc/vsftpd.conf
sudo systemctl restart vsftpd

# ensure login-path `pifbackup` is configured for root (or adjust LOGIN_PATH in script)
sudo mysql_config_editor set --login-path=pifbackup --host=192.168.178.52 --user=pif_backup --password

# test a manual run
sudo /usr/local/bin/pif_backup.sh
sudo tail -n 50 /var/log/pif_backup.log

Notes
- The script uses `--login-path=pifbackup` by default; change `LOGIN_PATH` or the script if you stored credentials elsewhere.
- The script writes archives as `sofjo685_pif_YYYY-MM-DDTHHMMSSZ.sql.gz` and prunes older-than-7-days by default.
- If you need FTP access from the web server, configure vsftpd and create an ftp user jailed to `/srv/pif_backups`.
