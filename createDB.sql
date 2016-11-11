CREATE DATABASE forum;


use forum;
CREATE TABLE users(
USERNAME varchar (254) NOT NULL,
PASSWORD varchar (40) NOT NULL,
USERID int NOT NULL AUTO_INCREMENT,
PRIMARY KEY(USERID)
);

CREATE TABLE comments(
TITLE VARCHAR (255) NOT NULL,
MESSAGE TEXT NOT NULL,
DATE DATETIME DEFAULT CURRENT_TIMESTAMP,
ID int NOT NULL AUTO_INCREMENT,
USER_ID int DEFAULT NULL,

PRIMARY KEY(ID),
FOREIGN KEY(USER_ID)
  REFERENCES users(USERID)
);

GRANT select,insert,update,delete on forum.*
             to 'forum_admin'@'localhost'
             identified by 'supersecretpancakebatter';
flush privileges;