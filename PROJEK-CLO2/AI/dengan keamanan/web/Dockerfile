FROM php:8.2-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    fail2ban \
    iptables \
    openssl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache modules
RUN a2enmod ssl rewrite headers

# Generate self-signed SSL certificate
RUN mkdir -p /etc/apache2/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/server.key \
    -out /etc/apache2/ssl/server.crt \
    -subj "/C=ID/ST=Indonesia/L=Jakarta/O=KAMSIS/OU=Security/CN=localhost"

# Copy Apache SSL configuration
COPY apache-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN a2ensite default-ssl

# Copy PHP security configuration
COPY php.ini /usr/local/etc/php/conf.d/security.ini

# Copy fail2ban configuration
COPY fail2ban/jail.local /etc/fail2ban/jail.local
COPY fail2ban/filter.d/php-login.conf /etc/fail2ban/filter.d/php-login.conf

# Create log file for fail2ban
RUN touch /var/log/auth-login.log && \
    chown www-data:www-data /var/log/auth-login.log

# Create fail2ban socket directory
RUN mkdir -p /var/run/fail2ban

# Copy source files
COPY src/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Copy and set entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 80 443

ENTRYPOINT ["/entrypoint.sh"]
