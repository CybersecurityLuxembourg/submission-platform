#!/bin/bash

set -euo pipefail

BACKUP_DIR="/backups"
MAX_BACKUPS=6
DB_HOST="${DB_HOST:-db}"
DB_NAME="${DB_DATABASE}"
DB_USER="${DB_USERNAME}"
DB_PASS="${DB_PASSWORD}"

LOG_FILE="/var/log/backup.log"

# Function to create backup
create_backup() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - Starting backup..." >> "${LOG_FILE}"

    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="${BACKUP_DIR}/backup_${TIMESTAMP}"

    # Backup database
    if mysqldump -h "${DB_HOST}" -u "${DB_USER}" -p"${DB_PASS}" "${DB_NAME}" > "${BACKUP_PATH}.sql"; then
        echo "$(date '+%Y-%m-%d %H:%M:%S') - Database backup successful." >> "${LOG_FILE}"
    else
        echo "$(date '+%Y-%m-%d %H:%M:%S') - Database backup failed!" >> "${LOG_FILE}"
        exit 1
    fi

    # Backup application files
    if tar -czf "${BACKUP_PATH}.tar.gz" -C /var/www/html .; then
        echo "$(date '+%Y-%m-%d %H:%M:%S') - Files backup successful." >> "${LOG_FILE}"
    else
        echo "$(date '+%Y-%m-%d %H:%M:%S') - Files backup failed!" >> "${LOG_FILE}"
        exit 1
    fi

    # Remove old backups
    find "${BACKUP_DIR}" -type f -name "*.sql" -mtime +${MAX_BACKUPS} -delete
    find "${BACKUP_DIR}" -type f -name "*.tar.gz" -mtime +${MAX_BACKUPS} -delete

    echo "$(date '+%Y-%m-%d %H:%M:%S') - Backup completed." >> "${LOG_FILE}"
}

# Run the backup
create_backup
