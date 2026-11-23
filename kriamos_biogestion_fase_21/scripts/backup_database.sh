#!/usr/bin/env bash
set -e

# Backup de base de datos Kriamos BioGestión ICA.
# Ajustar variables antes de usar en producción.

DB_NAME="kriamos_biogestion_ica"
DB_USER="kriamos_user"
DB_PASS="CAMBIAR_PASSWORD"
BACKUP_DIR="/var/backups/kriamos-biogestion/database"
DATE=$(date +"%Y%m%d_%H%M%S")

mkdir -p "$BACKUP_DIR"

mysqldump -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" | gzip > "$BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"

find "$BACKUP_DIR" -type f -mtime +30 -delete

echo "Backup generado: $BACKUP_DIR/${DB_NAME}_${DATE}.sql.gz"
