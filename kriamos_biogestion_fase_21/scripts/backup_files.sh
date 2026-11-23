#!/usr/bin/env bash
set -e

# Backup de archivos cargados Kriamos BioGestión ICA.

APP_DIR="/var/www/kriamos-biogestion"
BACKUP_DIR="/var/backups/kriamos-biogestion/files"
DATE=$(date +"%Y%m%d_%H%M%S")

mkdir -p "$BACKUP_DIR"

tar -czf "$BACKUP_DIR/uploads_${DATE}.tar.gz" \
    "$APP_DIR/public/uploads" \
    "$APP_DIR/storage"

find "$BACKUP_DIR" -type f -mtime +30 -delete

echo "Backup generado: $BACKUP_DIR/uploads_${DATE}.tar.gz"
