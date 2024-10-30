#!/bin/bash

BACKUP_DIR="/backups"
MAX_BACKUPS=6
DB_HOST="db"
DB_NAME=${DB_DATABASE}
DB_USER=${DB_USERNAME}
DB_PASS=${DB_PASSWORD}

# Function to create backup
create_backup() {
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="${BACKUP_DIR}/backup_${TIMESTAMP}"

    # Backup database
    mysqldump -h ${DB_HOST} -u ${DB_USER} -p${DB_PASS} ${DB_NAME} > "${BACKUP_PATH}.sql"

    # Backup application files
    cd /var/www/html && tar -czf "${BACKUP_PATH}.tar.gz" .

    # Remove old backups
    cd ${BACKUP_DIR}
    ls -t backup_*.sql | tail -n +$((MAX_BACKUPS + 1)) | xargs -r rm
    ls -t backup_*.tar.gz | tail -n +$((MAX_BACKUPS + 1)) | xargs -r rm
}

# Create backup every 12 hours
while true; do
    create_backup
    sleep 43200  # 12 hours in seconds
done
