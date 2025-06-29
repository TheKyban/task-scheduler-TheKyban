#!/bin/bash

# Absolute path to PHP
PHP_PATH=$(which php)

# Get current directory (where this script is run)
PROJECT_DIR="$(pwd)"

# Path to cron.php and dynamic log
CRON_FILE="$PROJECT_DIR/cron.php"
LOG_FILE="$PROJECT_DIR/cron.log"

# Run every hour at minute 0
CRON_SCHEDULE="0 * * * *"

# Cron expression: every 1 minute
# CRON_SCHEDULE="* * * * *"

# Cron job line with dynamic log path
CRON_JOB="$CRON_SCHEDULE $PHP_PATH $CRON_FILE >> $LOG_FILE 2>&1"

# Install the job (remove old one if exists)
(crontab -l 2>/dev/null | grep -v -F "$CRON_FILE" ; echo "$CRON_JOB") | crontab -

echo "✅ Cron job installed to run every minute"
echo "📄 Logs will be written to: $LOG_FILE"
