set -e
CONF=/etc/nginx/sites-enabled/etry.conf
if [ ! -f "$CONF" ]; then echo missing conf; exit 1; fi
nl -ba "$CONF" | sed -n '1,180p'