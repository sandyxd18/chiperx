FROM php:8.2-apache

RUN apt-get update && apt-get install -y fail2ban iptables openssl && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod ssl rewrite headers

RUN mkdir -p /etc/apache2/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/server.key \
    -out /etc/apache2/ssl/server.crt \
    -subj "/C=ID/ST=Indonesia/L=Jakarta/O=KAMSIS/CN=localhost"

COPY apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN a2ensite default-ssl

COPY php.ini /usr/local/etc/php/conf.d/security.ini

COPY fail2ban/jail.local /etc/fail2ban/jail.local
COPY fail2ban/filter.d/php-login.conf /etc/fail2ban/filter.d/php-login.conf

RUN touch /var/log/auth-login.log && chown www-data:www-data /var/log/auth-login.log
RUN mkdir -p /var/run/fail2ban

COPY src/ /var/www/html/
RUN chown -R www-data:www-data /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80 443
ENTRYPOINT ["/entrypoint.sh"]
