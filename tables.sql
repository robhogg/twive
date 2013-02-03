*/
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
search varchar(255),
created datetime,
user varchar(12),
private tinyint(1) default 0,
last_updated datetime,
update_interval int,
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
