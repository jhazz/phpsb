<?
class news_Group{
var $CopyrightText="(c)2006 PHPSB. News publishing cartridge";
var $CopyrightURL="http://www.jhazz.com/news";
var $ComponentVersion="1.0";

var $Data=false;

function InitComponent()
  {
  $this->Datadefs=array(
    NewsGroup=>array(DataType=>"news.Group",Caption=>"News group"),
    );
  }

function Init(&$Control)
  {
  $_=$GLOBALS['_STRINGS']['news'];
  $__=&$GLOBALS['_STRINGS']['_'];

  global $cfg,$SysContext;

  if ($SysContext!='layouts')
    {
    $Control->Data['NewsGroup']="news.NewsGroup/$Control->JSBPageID";
    $Control->Data['PageTitle']=langstr_get($JSB_PageData->Title);
    $GLOBALS['_TITLE']=$Control->Data['PageCaption']=langstr_get($JSB_PageData->Caption);
    if ((!$Control->ControlEditor)&&($Control->DesignMode))
      {
      $ctx=$SysContext;

      print "<table width='100%' cellpadding='5'><tr><td class='bgup'>
       <a href='$cfg[RootURL]/edit/$ctx/$Control->JSBPageID?siteview=0'>News list view</a>
       | <a href='$cfg[RootURL]/edit/$ctx/$Control->JSBPageID?siteview=1'>Website view</a>
      </td></tr></table>";

      if (!$_GET['siteview'])
        {
        $_ENV->LoadTheme ('b');
#        $_ENV->InitWindows();
        $_ENV->InitPage(array(Environment=>'b'));
        $IGroup=&$_ENV->LoadInterface("news.IGroup");
        $IGroup->Edit(array(NewsGroupID=>$Control->JSBPageID,DateGroup=>-1));
        exit;
        }
      }
    }

  }

}
