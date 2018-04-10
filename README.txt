Email verification .env
Postfix
MAIL_DRIVER=smtp
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null

mailtrap.io
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=92adebf83e17bf
MAIL_PASSWORD=2eb94f91a24984
MAIL_ENCRYPTION=null



ec2 mysql remote access setup
sudo apt install mysql-server
sudo nano /etc/mysql/my.cnf
    [mysqld]
    bind-address = 0.0.0.0
mysql -u root -p
    use twitir;
    CREATE TABLE users;
    ALTER TABLE users add id


Load Balancer w/ NGINX
nginx.conf
    include /etc/nginx/sites-available/load-balancer.conf

load-balancer.conf
upstream twitir {
    ip_hash;
    server 18.188.38.202:80;
    server 18.188.45.176:80;
}

server {
    listen 80;
    server_name cayeung.cse356.compas.cs.stonybrook.edu;
    location ~ {
        proxy_pass http://twitir;
        proxy_next_upstream error timeout http_500;
    }
}
