<?
class news_Event{
var $CopyrightText="(c)2006 PHPSB. News publishing cartridge";
var $CopyrightURL="http://www.jhazz.com/news";
var $ComponentVersion="1.0";

var $Data=false;

function Browse($args)
  {
  global $cfg;
  $ctx=$cfg['Settings']['news']['NewsGroupsContext'];
  $qp=DBQuery ("SELECT HomePageID FROM sys_Contexts WHERE SysContext='$ctx'");
  $HomePageID=0;
  if ($qp) { $HomePageID=intval($qp->Top->HomePageID); }

  Header("Location: ".ActionURL("jsb.ISiteExplorer.Open.n",
    array(Path=>"/$ctx/$HomePageID",
    ContextLocked=>1,
    NoLayouts=>1)));
  }


function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['news'];
  $this->Propdefs=array(
    DateFormat=>array(Type=>"Dateformat",Required=>true,DefaultValue=>"normaldate",Caption=>$_[NEWSDATEFORMAT]),
#    ViewEventListContext=>array(Type=>"SysContext",ObjectClass=>"news.Group",Caption=>$_[NEWSGROUP_TARGET_NEWSEVENTS_CONTEXT],DefaultValue=>$cfg['Settings']['news']['PagesDefaultContext']),
    );

  $this->Datadefs=array(
    NewsEvent  =>array(DataType=>"news.Event",Caption=>$_['TNEWSEVENT_D_THENEWSEVENT']),
    NewsGroup  =>array(DataType=>"news.Group",Caption=>$_['TNEWSEVENT_D_THENEWSGROUP']),
    Title      =>array(DataType=>"String",Caption=>$_['TNEWSEVENT_D_TITLE']),
    Intro      =>array(DataType=>"String",Caption=>$_['TNEWSEVENT_D_INTRO']),
    Author     =>array(DataType=>"String",Caption=>$_['TNEWSEVENT_D_AUTHOR']),
    DateOfNews =>array(DataType=>"Timestamp",Caption=>$_['TNEWSEVENT_D_DATE']),
    EventDate  =>array(DataType=>"String",Caption=>$_['TNEWSEVENT_D_DATE']),
    NewsHead   =>array(DataType=>"img.Image",Caption=>"Head image"),
    NewsAlbum  =>array(DataType=>"img.Image",Caption=>"Album"),
    NewsText   =>array(DataType=>"stdctrls.Richtext",Caption=>"News text"),
    LinkToNewsGroup=>array(DataType=>"Link",Caption=>"Link to the list of events ot the same group"),
    MediaFiles=>array(DataType=>"Mediafiles",Caption=>"Media files that attached to the news"),
    
    );
  }


function Init (&$Control)
  {
  global $cfg,$SysContext;
  $_=&$GLOBALS['_STRINGS']['news'];
  $__=&$GLOBALS['_STRINGS']['_'];

  if ($Control->Properties)
    {
    extract ($Control->Properties);
    }

  if ($Control->DesignMode)
    {
    $Control->Data['Title']=$_['SAMPLE_NEWS_TITLE'];
    $Control->Data['Intro']=$_['SAMPLE_NEWS_INTRO'];
    $Control->Data['Author']=$_['TNEWSEVENT_D_AUTHOR'];
    $Control->Data['DateOfNews']=time();
    $Control->Data['EventDate']=format_date($DateFormat,time());
    $Control->Data['NewsHead'] ="news.Event/imghead/";
    $Control->Data['NewsAlbum']="news.Event/imgindex/";
    $Control->Data['NewsText']="news.Event/";
    return;
    }

  if ($SysContext=='layouts')
    {
    return;
    }

  $NewsID=$Control->JSBPageID;
  $q=DBQuery ("SELECT * FROM news_Events WHERE NewsID=$NewsID");
  if ($q)
    {
    $Control->Data['LinkToNewsGroup']=array(URL=>'../'.$cfg['Settings']['news']['NewsGroupsContext'].'/'.$q->Top->NewsGroupID.'.'.$cfg['VirtualExtension'],Text=>"Back");
    $Control->Data['NewsEvent']="news.Event/$NewsID";
    $Control->Data['NewsGroup']="news.Group/".$q->Top->NewsGroupID;
    $Control->Data['NewsHead'] ="news.Event/imghead/$NewsID";
    $Control->Data['NewsAlbum']="news.Event/imgindex/$NewsID";
    $Control->Data['NewsText']="news.Event/$NewsID";
    $UploadFile=$q->Top->UploadFile;
    
    $Control->Data['MediaFiles']="";#"y:rP6wzhUMwQ4";
    if ($UploadFile) {
    	if (substr($UploadFile,0,2)=='y:')$Control->Data['MediaFiles']=$UploadFile;
    }

    $GLOBALS['_TITLE']=$Control->Data['Title']=langstr_get($q->Top->Title);
    if ($q->Top->Keywords)
      {
      global $_KEYWORDS;
      $_KEYWORDS.=$q->Top->Keywords;
      }

    $Control->Data['Intro']=nl2br(langstr_get($q->Top->Intro));
    $Control->Data['Author']=langstr_get($q->Top->Author);
    if ($q->Top->AuthorHTTP)
      {
      $h=$q->Top->AuthorHTTP;
      if (substr($h,0,4)=='www.') {$h="http://".$h;}
      $Control->Data['Author']="<a href='".$h."' target='_blank'>".$Control->Data['Author']."</a>";
      }

    $Control->Data['DateOfNews']=$q->Top->DateOfNews;
    $Control->Data['EventDate']=format_date($DateFormat,$q->Top->DateOfNews);
    }
  else
    {
    return array(PageNotFound=>1);

    }
  }


}
