

rm -f /var/run/fail2ban/fail2ban.sock

echo "Starting fail2ban..."
service fail2ban start || echo "fail2ban start warning (non-critical)"

echo "Starting Apache with SSL..."
apache2-foreground
