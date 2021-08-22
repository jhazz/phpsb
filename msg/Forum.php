<?php
# UNDER DEVELOPMENT!!!
class msg_Forum
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Message cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $this->Datadefs=array(
     Forum=>array(DataType=>"msg.Forum",Caption=>"Forum")
     );
  }
function Init (&$Control)
  {
  $TopicID=$Control->Properties['TopicID'];
  if ($TopicID)
    {
    $Control->Data['Forum']="msg.Topic/$TopicID";
    }
  }

}
?>
