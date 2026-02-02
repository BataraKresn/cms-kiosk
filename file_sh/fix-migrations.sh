#!/bin/bash
# Script untuk fix migration conflicts
# Location: /home/ubuntu/kiosk/fix-migrations.sh

echo "🔧 Fixing migration conflicts..."

# Connect to database and mark old migrations as run
docker compose -f docker-compose.prod.yml exec mariadb mysql -ukiosk_platform -p5Kh3dY82ry05 platform <<EOF

-- Mark migrations yang sudah jalan tapi belum tercatat
INSERT IGNORE INTO migrations (migration, batch) VALUES
('2024_09_03_065822_create_devices_table', 1),
('2024_09_18_025728_create_permission_tables', 1),
('2026_01_24_000001_add_last_seen_to_remotes_table', 1),
('2026_01_26_000001_fix_media_storage_paths', 1),
('2026_01_27_000001_add_indexes_to_remotes_table', 1);

-- Show all migrations
SELECT * FROM migrations ORDER BY id DESC;

EOF

echo "✅ Migration conflicts resolved!"
echo "Run: docker compose -f docker-compose.prod.yml exec cosmic-app-1 php artisan migrate --force"
