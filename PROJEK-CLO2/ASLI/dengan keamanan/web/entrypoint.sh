
rm -f /var/run/fail2ban/fail2ban.sock
service fail2ban start || echo "fail2ban warning"
apache2-foreground
