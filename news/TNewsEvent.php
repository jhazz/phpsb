<?php
class news_TNewsEvent
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. News system cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

var $Data=false;

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['news'];
  $this->Propdefs=array(
    DateFormat=>array(Type=>"Dateformat",Required=>true,DefaultValue=>"normaldate",Caption=>$_[NEWSDATEFORMAT]),
    );

  $this->Datadefs=array(
    NewsEvent  =>array(DataType=>"news.NewsEvent",Caption=>$_[TNEWSEVENT_D_THENEWSEVENT]),
    NewsGroup  =>array(DataType=>"news.Group",Caption=>$_[TNEWSEVENT_D_THENEWSGROUP]),
    Title      =>array(DataType=>"String",Caption=>$_[TNEWSEVENT_D_TITLE]),
    Intro      =>array(DataType=>"String",Caption=>$_[TNEWSEVENT_D_INTRO]),
    Author     =>array(DataType=>"String",Caption=>$_[TNEWSEVENT_D_AUTHOR]),
    DateOfNews =>array(DataType=>"Timestamp",Caption=>$_[TNEWSEVENT_D_DATE]),
    EventDate  =>array(DataType=>"String",Caption=>$_[TNEWSEVENT_D_DATE]),
    NewsHead   =>array(DataType=>"img.Image",Caption=>"Head image"),
    NewsAlbum  =>array(DataType=>"img.Image",Caption=>"Album"),
    NewsText   =>array(DataType=>"stdctrls.Richtext",Caption=>"News text"),
    );
  }


function Init (&$Control)
  {
  global $cfg;
  $_=&$GLOBALS['_STRINGS']['news'];
  $__=&$GLOBALS['_STRINGS']['_'];

  if ($Control->Properties)
    {
    extract ($Control->Properties);
    }

  $JSBNewsContext=$cfg['Settings']['news']['NewsPagesContext'];

  if ($Control->DesignMode)
    {
    $Control->Data['Title']=$_[SAMPLE_NEWS_TITLE];
    $Control->Data['Intro']=$_[SAMPLE_NEWS_INTRO];
    $Control->Data['Author']=$_[TNEWSEVENT_D_AUTHOR];
    $Control->Data['DateOfNews']=time();
    $Control->Data['EventDate']=format_date($DateFormat,time());
    $Control->Data['NewsHead'] ="news.Event/imghead/";
    $Control->Data['NewsAlbum']="news.Event/imgindex/";
    $Control->Data['NewsText']="news.Event/";
    }

  if ($Control->SysContext !=$JSBNewsContext )
    {
    if ($Control->SysContext=='layouts')
      {
      return;
      }
    else
      {
#      return array(Error=>"News event loader should be rendered only in pages of context '$JSBNewsContext'");
      }
    }

  $NewsID=$Control->JSBPageID;
  $q=DBQuery ("SELECT * FROM news_Events WHERE NewsID=$NewsID");
  if ($q)
    {
    $Control->Data['CNewsEvent']=&$Control;
    $Control->Data['NewsGroup']=$q->Top->NewsGroupID;
    trace ("Publish  ".$q->Top->NewsGroupID." to newsgroup");

    $Control->Data['NewsHead'] ="news.Event/imghead/$NewsID";
    $Control->Data['NewsAlbum']="news.Event/imgindex/$NewsID";
    $Control->Data['NewsText']="news.Event/$NewsID";

    $GLOBALS['_TITLE']=$Control->Data['Title']=$q->Top->Title;
    if ($q->Top->Keywords)
      {
#      $GLOBALS['_TITLE'].=" - ".$q->Top->Keywords;
      global $_KEYWORDS;
      $_KEYWORDS.=$q->Top->Keywords;
      }

    $Control->Data['Intro']=$q->Top->Intro;
    $Control->Data['Author']=$q->Top->Author;
    if ($q->Top->AuthorHTTP)
      {
      $h=$q->Top->AuthorHTTP;
      if (substr($h,0,4)=='www.') {$h="http://".$h;}
      $Control->Data['Author']="<a href='".$h."' target='_blank'>".$Control->Data['Author']."</a>";
      }

    $Control->Data['DateOfNews']=$q->Top->DateOfNews;
    $Control->Data['EventDate']=format_date($DateFormat,$q->Top->DateOfNews);
    }
  }

/*
function Render(&$Control)
  {
  $_=&$GLOBALS[_STRINGS][news];
  global $cfg;

  extract($Control->Properties);


  if ($Control->SysContext=='layouts')
    {
    print $_[TNEWSEVENT_PLACED_IN_LAYOUT];
    }

  if ($Control->Data)
    {
    if (!$HideAllText)
      {

      list($t,$c)=get_css_pair($CSS_Title,'span');
      print "<$t$c>".$Control->Data[Title]."</$t>";

      if ($ShowDate)
        {
        list($t,$c)=get_css_pair($CSS_Date,'span');
        print "<$t$c>".$Control->Data['EventDate']."</$t>";
        }

      list($t,$c)=get_css_pair($CSS_Intro,'span');
      print "<$t$c>".$Control->Data['Intro']."</$t>";

      if (!$HideAuthor)
        {
        list($t,$c)=get_css_pair($CSS_Author,'span');
        print "<$t$c>".$Control->Data['Author']."</$t>";
        }
      }

    }
  }
    */
} # end of class


?>
