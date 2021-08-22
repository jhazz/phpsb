<?php
class schedule_TMyArticles
{
var $CopyrightText="(c)2006 PHPSB. Schedules";
var $CopyrightURL="http://www.phpsb.com/schedule";
var $ComponentVersion="1.0";

var $Data=false;

function InitComponent()
  {
  $_ =&$GLOBALS['_STRINGS']['schedule'];
  $this->Propdefs=array(
    CaptionAddSchedule=>array(Type=>"Caption",Required=>true,DefaultValue=>$_['ADD_SCHEDULE']),
    CaptionAddSiteSchedule=>array(Type=>"Caption",Required=>true,DefaultValue=>$_['ADD_SITE_SCHEDULE']),
    TextAddScheduleHint=>array(Type=>"Caption",Required=>true,DefaultValue=>$_['ADD_SCHEDULE_HINT']),
    SiteSchedulesURL=>array(Type=>"localurl",Caption=>$_['ADD_SITE_SCHEDULE_URL']),
    );
/*
  $this->Datadefs=array(
    CalendarImage =>array(DataType=>"img.Image",Caption=>"“итульное изображение мес€ца"),
    MonthName     =>array(DataType=>"String",Caption=>"Ќазвание выбранного мес€ца"),
    );
*/
  }


function Init (&$Control)
  {
  global $cfg,$_USER,$_SESSION;
  $_ =&$GLOBALS['_STRINGS']['pub'];
  $__=&$GLOBALS['_STRINGS']['_'];

  return array(DisableCache=>true);
  }

function Render (&$Control)
  {
  global $cfg,$_USER,$_SESSION;
  $_ =&$GLOBALS['_STRINGS']['pub'];
  $__=&$GLOBALS['_STRINGS']['_'];
  extract ($Control->Properties);

  if (!$_USER->UserID)
    {
    return;
    }
  }
} # end of class


?>
