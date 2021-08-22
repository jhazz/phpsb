<?php
# UNDER DEVELOPMENT!!!
class msg_TTopic
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Message cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $this->About="Topic";
  $q=DBQuery ("SELECT TopicID,Caption FROM msg_Topics ORDER BY TopicID","TopicID");
  if ($q) {foreach($q->Rows as $TopicID=>$row) {$BindToTopicList[$TopicID]=$row->Caption;}}
  $this->Propdefs=array(
     TopicID=>array(Type=>"List",Required=>true,Values=>$BindToTopicList,Caption=>"Тема, к которой будут привязаны сообщения"),
     );
  $this->Datadefs=array(
     Topic=>array(DataType=>"Socket",SocketType=>"msg.Topic",Caption=>"Тема общения")
     );
  }
function Init (&$Control)
  {
  $TopicID=$Control->Properties['TopicID'];
  if ($TopicID)
    {
    $Control->Data[Topic]="msg.Topic/$TopicID";
    }
  }

function Render (&$Control)
  {
  $TopicID=$Control->Properties['TopicID'];
  if ($Control->DesignMode)
    {
    if (!$TopicID) {print "Тема не определена"; return;}
    $q=DBQuery ("SELECT Caption FROM msg_Topics WHERE TopicID=$TopicID");
    if (!$q)
      {
      print "Тема # $TopicID отсутствует";
      return;
      }
    print "Тема: '".$q->Top->Caption."'";
    }
  return array(DisableCache=>true);
  }
}
?>
