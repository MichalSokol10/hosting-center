FROM debian:bookworm-slim

ENV PHP_INI_DIR /etc/php/8.2

WORKDIR /var/www

RUN echo "deb-src http://deb.debian.org/debian bookworm main" > /etc/apt/sources.list

RUN apt update && apt upgrade -y && apt install -y \
    nano \
    sudo \
    openssl \
    libssl-dev \
    supervisor \
    curl \
    dpkg-dev \
    nginx \
    libnginx-mod-stream \
    php8.2 \
    php8.2-fpm \
    php8.2-pgsql

RUN mkdir /tmp/pure-ftpd && \
    cd /tmp/pure-ftpd && \
    apt source pure-ftpd && \
    cd pure-ftpd-1.0.50 && \
    ./configure --with-everything --with-tls --without-capabilities && \
    make install-strip && \
    rm -r /tmp/pure-ftpd

# certifikáty pro nginx
RUN mkdir /etc/nginx/ssl
COPY dhparam.pem /etc/nginx/ssl/dhparam.pem

# nginx config
COPY config/nginx.conf /etc/nginx/nginx.conf

# php config
RUN rm ${PHP_INI_DIR}/fpm/pool.d/www.conf

# supervisord config
COPY config/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# skript pro vytváření stránek
COPY create-site /usr/local/etc/create-site
RUN chmod a+rwx /usr/local/etc/create-site

#skript pro mazání stránek
COPY delete-site /usr/local/etc/delete-site
RUN chmod a+rwx /usr/local/etc/delete-site

# webová stránka pro hosting
COPY create-hosting /usr/local/etc/create-hosting
RUN /usr/local/etc/create-hosting hosting.com pepa
COPY hosting /var/www/hosting.com/
RUN rm /usr/local/etc/create-hosting

RUN cp /etc/nginx/ssl/hosting.com/hosting.com.* /etc/ssl/private/;
RUN cp /etc/ssl/private/hosting.com.key /etc/ssl/private/pure-ftpd.pem && \
    cat /etc/ssl/private/hosting.com.crt >> /etc/ssl/private/pure-ftpd.pem && \
    chmod 777 -R /etc/ssl/private/

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
