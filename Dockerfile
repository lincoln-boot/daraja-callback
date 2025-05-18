# Use official PHP Apache image
FROM php:8.2-apache

# Enable Apache mod_rewrite (optional but common for PHP apps)
RUN a2enmod rewrite

# Copy all files from your repo to the Apache web root
COPY . /var/www/html/

# Set correct working directory
WORKDIR /var/www/html/


