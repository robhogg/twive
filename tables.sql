create table tw_archive (
name varchar(12),
search varchar(255),
primary key (name)
);

create table tw_users (
uid varchar(12),
username varchar(20),
name varchar(60),
image varchar(255),
primary key (uid)
);

create table tw_tweets (
tid varchar(21),
uid varchar(12),
archive varchar(12),
text varchar(200),
date datetime,
reply_tweet varchar(21),
reply_user varchar(12),
primary key (tid)
);
