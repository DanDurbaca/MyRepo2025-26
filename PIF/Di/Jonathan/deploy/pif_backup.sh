#!/usr/bin/env bash
set -euo pipefail

# Atomic backup script template for PIF
# Configure via environment or edit variables below.

BACKUP_DIR=${BACKUP_DIR:-${BACKUP_LOCAL_DIR:-/srv/pif_backups}}
DB_NAME=${DB_NAME:-sofjo685_pif}
LOGFILE=${LOGFILE:-${BACKUP_LOG_FILE:-/var/log/pif_backup.log}}
LOGIN_PATH=${LOGIN_PATH:-pifbackup}
TMPDIR=${TMPDIR:-/tmp}
TEST_MODE=${TEST_MODE:-0}

mkdir -p "$BACKUP_DIR"
umask 027

if [ "$TEST_MODE" != "1" ]; then
  # Prevent concurrent runs (fall back to /tmp if /var/lock unavailable)
  LOCKFILE=${LOCKFILE:-/var/lock/pif_backup.lock}
  LOCKDIR=$(dirname "$LOCKFILE")
  if [ ! -d "$LOCKDIR" ] || [ ! -w "$LOCKDIR" ]; then
    LOCKFILE=/tmp/pif_backup.lock
  fi
  exec 200>"$LOCKFILE" 2>/dev/null || true
  if ! flock -n 200 2>/dev/null; then
    echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] Another backup is running" >> "$LOGFILE"
    exit 0
  fi
fi

TMPFILE=$(mktemp --tmpdir="$TMPDIR" "${DB_NAME}.XXXXXXXX.sql")
trap 'rv=$?; rm -f "$TMPFILE" "$TMPFILE.gz" >/dev/null 2>&1 || true; exit $rv' EXIT INT TERM

OUTFILE="$BACKUP_DIR/${DB_NAME}_$(date -u +%Y-%m-%dT%H%M%SZ).sql.gz"

echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] START backup" >> "$LOGFILE"

# Run mysqldump -> gzip to temporary file. In TEST_MODE create a small fake dump instead.
if [ "$TEST_MODE" = "1" ]; then
  echo "-- TEST DUMP for $DB_NAME at $(date -u +%Y-%m-%dT%H:%M:%SZ)" > "$TMPFILE".sql
  echo "CREATE TABLE test_dummy (id INT);" >> "$TMPFILE".sql
  gzip -c "$TMPFILE".sql > "$TMPFILE"
  rm -f "$TMPFILE".sql
  mv "$TMPFILE" "$OUTFILE"
  echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] TEST OK $OUTFILE" >> "$LOGFILE"
else
  if mysqldump --login-path="$LOGIN_PATH" --single-transaction --quick --routines --triggers --events "$DB_NAME" 2>>"$LOGFILE" | gzip -c > "$TMPFILE.gz"; then
    mv "$TMPFILE.gz" "$OUTFILE"
    chown root:backup "$OUTFILE" 2>/dev/null || true
    chmod 0640 "$OUTFILE" 2>/dev/null || true
    echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] OK $OUTFILE" >> "$LOGFILE"
  else
    echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] FAILED mysqldump pipe" >> "$LOGFILE"
    rm -f "$TMPFILE" "$TMPFILE.gz" || true
    exit 1
  fi
fi

# Prune backups older than 7 days
find "$BACKUP_DIR" -type f -name "${DB_NAME}_*.sql.gz" -mtime +7 -print -delete >> "$LOGFILE" 2>&1 || true

echo "[$(date -u +'%Y-%m-%dT%H:%M:%SZ')] DONE" >> "$LOGFILE"

exit 0
