CREATE DATABASE IF NOT EXISTS treasures_db;
CREATE DATABASE IF NOT EXISTS treasures_test;

CREATE USER 'admin'@'localhost' IDENTIFIED BY 'admin';
GRANT ALL PRIVILEGES ON *.* TO 'admin'@'%';