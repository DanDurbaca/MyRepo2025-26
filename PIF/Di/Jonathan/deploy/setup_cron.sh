#!/bin/bash
# One-time setup to enable automatic daily backups on macOS

set -e

PIF_ROOT="/Users/jonathansofra/pif"
BACKUP_DIR="$PIF_ROOT/backups"
BACKUP_LOG="$BACKUP_DIR/pif_backup.log"
SCRIPT="$PIF_ROOT/deploy/pif_backup.sh"

echo "=== PIF Backup Cron Setup ==="
echo "Root: $PIF_ROOT"
echo "Backup Dir: $BACKUP_DIR"
echo "Log: $BACKUP_LOG"
echo ""

# Verify directories exist
if [ ! -d "$BACKUP_DIR" ]; then
    echo "✗ Backup directory not found: $BACKUP_DIR"
    exit 1
fi

if [ ! -f "$SCRIPT" ]; then
    echo "✗ Backup script not found: $SCRIPT"
    exit 1
fi

echo "✓ Directories verified"
echo ""

# Show current crontab
echo "Current crontab:"
crontab -l 2>/dev/null || echo "(empty)"
echo ""

# Add or update cron job (runs at 2 AM daily)
CRON_CMD="0 2 * * * BACKUP_LOCAL_DIR=$BACKUP_DIR BACKUP_LOG_FILE=$BACKUP_LOG /bin/bash $SCRIPT"

# Check if already present
if crontab -l 2>/dev/null | grep -q "pif_backup.sh"; then
    echo "⚠ Backup cron job already exists. Skipping."
else
    echo "Adding cron job (runs daily at 2 AM)..."
    (crontab -l 2>/dev/null || echo "") | grep -v "pif_backup.sh" > /tmp/crontab_new.txt
    echo "$CRON_CMD" >> /tmp/crontab_new.txt
    crontab /tmp/crontab_new.txt
    rm /tmp/crontab_new.txt
    echo "✓ Cron job added"
fi

echo ""
echo "=== Setup Complete ==="
echo "Backups will run daily at 2 AM."
echo "View logs: tail -f $BACKUP_LOG"
echo "Test now:  BACKUP_LOCAL_DIR=$BACKUP_DIR BACKUP_LOG_FILE=$BACKUP_LOG TEST_MODE=1 /bin/bash $SCRIPT"
