Twive - a Twitter archiving solution.

Created by Rob Hardy (https://github.com/robhogg/twive) and licensed under the
GNU GPL.

Twive was created using PHP 5.2.17 and MySQL 5.0.96 running on Linux. I haven't
tested it elsewhere, but it will probably work.

### Installation ###
The script tables.sql contains the table definitions required. Create a 
databse, and then run:

mysql -u dbuser -h dbhost dbname -p < tables.sql

The database login credentials will need adding to config.php (and I also
suggest moving this to somewhere outside your webroot, and changing the path
in tw_lib.php).

At present, archives need to be set up manually in the database. As a miniumum,
a name and search string needs to be provided, and I'd suggest also including
your twitter id in the user column and created date. The name should be alphanumeric, and the search string should be url encoded (try a twitter search, and 
copy the bit after "q=" up to any & character) - e.g. for:

https://twitter.com/search?q=%23edcmooc&src=savs

... the search string would be '%23edcmooc' (%23 is a hash character).

You can get your twitter id from http://www.idfromuser.com/

Example:

insert into tw_archive (name,search,user,created) values
("edcmooc","%23edcmooc","123456789","2013-02-03 11:22:00");

Once created, the archive can be accessed at directory/archivename - e.g.
assuming you've installed in a directory called twive in your web root, the
archive above would be at:

http://your.domain.name/twive/edcmooc

Tweets can be archived using the tw_update.php script - run ./tw_update.php -h
to get usage instructions. This can also be added to cron, e.g.

*/15 * * * * /usr/bin/php5 ${twive}/tw_update.php -n100 edcmooc >> ${twive}/update.log

(where base is a variable containing the full path to your install directory)

This would update the archive every 15 minutes. You may want to update
more often, but be cautious about exceeding rate limits if you are archiving
multiple streams (see https://dev.twitter.com/docs/rate-limiting).

### TODO ###
This is very much a work in progress. Speed of progress will depend on my 
energy levels, and level of interest from others.

At the moment I'm planning:
* Move to version 1.1 of the Twitter API (before March 2013)
* Responsiveness to the CSS, to make for mobile friendliness
* AJAXification for refresh/navigation
* Improved charting:
** Offering options for period covered and fineness of grain (e.g. daily/hourly)
** Chart for current page (and option to change number of tweets per page)
** tweets by user
* More intelligent search
** user search, offering "find any/all" rather than just exact phrase
** possibly some search intelligence (related keywords, etc).
* Allow conversations to be viewed
* Keyword analysis
* a cron.php script to keep all archives updated
** with option for setting different update periods for each archive
* Admin page, allowing archives to be managed
** multiple user roles (at least, archive_creator, archive_member and sysadmin)
** using twitter oAuth seems neatest option for authentication.
** By default, users whose tweets are included in an archive should be members
** Easy dump of full archive (in JSON? XML?)
* Allow private archives (viewable only to members)
