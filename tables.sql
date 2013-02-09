/*
* Copyright Rob Hardy 2013 (https://github.com/robhogg/twive)
*
* This file is part of Twive
* 
* Twive is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
* 
* Twive is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with Twive.  If not, see <http://www.gnu.org/licenses/>.
*/

create table tw_archive (
name varchar(12),
description varchar(255) default "",
search varchar(255) not null,
created datetime default '1970-01-01 00:00:00',
user varchar(12) default "",
private tinyint(1) default 0,
last_updated datetime default null,
update_interval int default 60,
primary key (name)
);

create table tw_users (
uid varchar(12),
username varchar(20) not null,
name varchar(60) default "",
image varchar(255) default "",
role int default 2,
primary key (uid)
);

create table tw_tweets (
tid varchar(21),
uid varchar(12),
text varchar(200),
date datetime,
reply_tweet varchar(21),
reply_user varchar(12),
primary key (tid)
);

create table tw_archive_link (
archive varchar(12),
tid varchar(21),
primary key (archive,tid)
);

create table tw_keywords (
archive varchar(12),
keyword varchar(100),
occurrences int default 0,
primary key (keyword)
);

create table tw_stop_words (
stop_word varchar(100),
primary key (stop_word)
);
)
