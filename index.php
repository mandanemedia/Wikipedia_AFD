<?php
require_once "classes/config.php";
require_once "classes/Crawler.php";
require_once "classes/DebateDateList.php";
require_once "classes/functions.php";
ini_set('MAX_EXECUTION_TIME', -1);

echo '<link rel="stylesheet" type="text/css" href="style.css">';
echo '<br/>';

                
?>
<ol>
    <li><a target="_blank" href="getHtmlByUrl.php?url=aaaaa">GetHtmlByUrl.php</a></li>
    <li><a target="_blank" href="getHtmlByID.php?id=xx">GetHtmlByID.php?id=xx</a></li>
    <li><a target="_blank" href="GetHtmlByDebateDateListID.php?DebateDateListID=xx">GetHtmlByDebateDateListID.php?DebateDateListID=xx</a></li>
    <li><a target="_blank" href="crawlDebateDateList.php">CrawlDebateDateList.php</a></li>
    <li><a target="_blank" href="parseDebateDate.php?fromID=xx&toID=xx">ParseDebateDate.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="parseDebateDate_2.php?fromID=xx&toID=xx">ParseDebateDate_2.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="parseDebateDate_3.php?fromID=xx&toID=xx">ParseDebateDate_3.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="parseDebateDateByID.php?id=xx">ParseDebateDateByID.php?id=xx</a></li>
    <li><a target="_blank" href="getAFDHtmlByID.php?id=xx">GetAFDHtmlByID.php?id=xx</a></li>
    <li><a target="_blank" href="getAFDHtmlByUrl.php?AFDURL=xx">GetAFDHtmlByUrl.php?AFDURL=xx</a></li>
    <li><a target="_blank" href="getAFDListbyDebateDateListID.php?DebateDateListID=xx">GetAFDListbyDebateDateListID.php?DebateDateListID=xx</a></li>
    <li><a target="_blank" href="parseAFD.php?fromID=xx&toID=xx">ParseAFD.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="parseAFDByAFDID.php?AFDID=xx">ParseAFDByAFDID.php?AFDID=xx</a></li>
    <li><a target="_blank" href="parseAFD_EndResult.php?fromID=xx&toID=xx">ParseAFD_EndResult.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="parseAFD_MainComment.php?fromID=xx&toID=xx">ParseAFD_MainComment.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="parseAFD_OtherComment.php?fromID=xx&toID=xx">ParseAFD_OtherComment.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="parseAFD_OtherCommentByAFDID.php?AFDID=xx">ParseAFD_OtherCommentByAFDID.php?AFDID=xx</a></li>
    <li><a target="_blank" href="parseAFD_CommentLink.php?fromID=xx&toID=xx">ParseAFD_CommentLink.php?fromID=xx&toID=xx</a></li>
    <li><a target="_blank" href="testing.php">Testing.php</a></li>
</ol>