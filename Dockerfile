FROM node:latest AS node
FROM php:8.2-fpm


COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

RUN #apt-get update && apt-get install -y nodejs npm

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

RUN mkdir -p /usr/src/app

RUN mkdir -p /var/www/html/public

COPY ./public /var/www/html/public

#COPY conf.ex.php /var/www/html/conf.php
RUN echo "<?php \
          global $config; \
          $config = [ \
              'DATABASE_SERVER' => '$DATABASE_SERVER', \
              'DATABASE_USER' => '$DATABASE_USER', \
              'DATABASE_PASS' => '$DATABASE_PASS', \
              'DATABASE_NAME' => '$DATABASE_NAME', \
              'NODE_SERVER' => '$NODE_SERVER', \
              'PURGE_PASS' => '$PURGE_PASS' \
          ];" > /var/www/html/public

WORKDIR /usr/src/app

COPY . /usr/src/app

RUN npm install

EXPOSE 9000

CMD npm run start & php-fpm