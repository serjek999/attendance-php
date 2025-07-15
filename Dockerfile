FROM php:8.2-apache

# Enable Apache rewrite module and set index
RUN a2enmod rewrite && \
    echo "DirectoryIndex index.php" >> /etc/apache2/apache2.conf && \
    echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy app files to Apache root
COPY . /var/www/html/

# Expose port 80
EXPOSE 80
