# Hackathon-2023
[Sid](https://github.com/Sid220) and I worked on a mini metaverse that functions a video calling site. This can be used as a virtual hangout and get together for distant friends and family.

View it live [here.](https://winterwonderland.plios.tech/)

## Specs:
* Used Aframe.js and Networked Aframe
* JS based
* Lightweight enough for Raspberry Pi

## Docker Installation (Recommended):
1) Get the compose file

`wget https://raw.githubusercontent.com/Piflyer/Hackathon-2023/docker/compose.yaml`

2) Start docker compose

```sh
docker compose up
```

## Manual Installation:

1) Clone the project by running:

`git clone https://github.com/Piflyer/Hackathon-2023.git`

2) Install the necessary dependencies for the backend:

`cd Hackathon-2023 && npm install`

3) Run the backend on your local network:

`npm run start &`

4) Configure to use your database and node server

`cp conf.ex.php conf.php && nano conf.php`

5) Run the frontend by pointing your server of choice to the `public` directory, for example with NGINX

```nginx
server {
     root /directory/to/winterwonderland/public;
     index index.php index.html index.htm;
     server_name winterwonderland.mydomain.com;
     location / {
           try_files $uri $uri/ =404;
     }
     location ~ \.php$ {
           include snippets/fastcgi-php.conf;
           # Make sure you use your correct PHP version
           fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
     }

    listen 80;
}
```

5) Create database by running `db.sql`

6) (Optional) Automatically discard older rooms by running a webcron:

`curl https://interwonderland.mydomain.com/internals/purge_rooms.json.php?pass=PURGE_PASS`

## Configuration Options:

| Name            | Description                                                  |
|-----------------|--------------------------------------------------------------|
| DATABASE_SERVER | Server of the database                                       |
| DATABASE_USER   | User of the database which has write access to DATABASE_NAME |
| DATABASE_PASS   | Password of DATABASE_USER                                    |
| DATABASE_NAME   | Name of database you have created tables for                 |
| NODE_SERVER     | Server running NPM command                                   |
| PURGE_PASS      | Password used to remove older rooms                          |