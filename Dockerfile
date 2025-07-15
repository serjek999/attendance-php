# Use official PHP with Apache
FROM php:8.2-apache

# Copy all app files into container
COPY . /var/www/html/

# Enable Apache rewrite module (optional but useful)
RUN a2enmod rewrite

# Expose port 80
EXPOSE 80
