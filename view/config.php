<?php

/** MySQL database name */
define('DB_Name', 'mandanem_afd');

/** MySQL database username */
define('DB_User', 'root');

/** MySQL database password */
define('DB_Password', '');

/** MySQL hostname */
define('DB_Host', 'localhost');


/** MySQL hostname */
//Normal <a href="/wiki/User:DavidLeighEllis" title="User:DavidLeighEllis">DavidLeighEllis</a>
define('Pattern_UserID1', '/href=[\"|\']\/wiki\/User\:(.*?)title/i');
//AFDID=5000 Endresult  <a href="/wiki/User_talk:Black_Kite" title="User talk:Black Kite">Black Kite (talk)</a>
define('Pattern_UserID2', '/href=[\"|\']\/wiki\/User_talk\:(.*?)title/i');
//AFDID=5004 Endresult <a href="/w/index.php?title=User:Sampi&amp;action=edit&amp;redlink=1" class="new" title="User:Sampi (page does not exist)">sampi</a>
define('Pattern_UserID3', '/href=[\"|\']\/w\/index.php\?title\=User\:(.*?)&amp;action=edit/i');
//AFDID=2120 other <a href="/wiki/Special:Contributions/70.198.36.165" title="Special:Contributions/70.198.36.165">70.198.36.165</a>
define('Pattern_UserID4', '/href=[\"|\']\/wiki\/Special\:Contributions\/(.*?)title/i');

define('PassingPercentage', '80');

$log = "<br/>    ****** LOG Started ******* <br/>";

//Userfy refers to the act of moving a page from the "Main" namespace (which is reserved for articles) to userspace. Typically, this would be the userspace of the person who created the page or showed interest in working on it.

?>