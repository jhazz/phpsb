<?
class store_ProdGroup
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $this->Datadefs=array(
    PageCaption=>array(DataType=>"String",Caption=> 'Product group short name'),
    PageTitle=>array(DataType=>"String",Caption=> 'Product group title'),
    ProdGroup=>array(DataType=>"store.ProdGroup",Caption=>"Product group"),
    );
  }

function Init(&$Control)
  {
  global $_ENVIRONMENT,$cfg,$JSB_PageData;

  if ($Control->SysContext!='layouts')
    {
    $Control->Data['ProdGroup']="store.ProdGroup/$Control->JSBPageID";
    $Control->Data['PageTitle']=langstr_get($JSB_PageData->Title);
    $GLOBALS['_TITLE']=$Control->Data['PageCaption']=langstr_get($JSB_PageData->Caption);
    if ((!$Control->ControlEditor)&&($Control->DesignMode))
      {
      $ctx=$Control->SysContext;

      print "<table width='100%' cellpadding='5'><tr><td class='bgup'>
       <a href='$cfg[RootURL]/edit/$ctx/$Control->JSBPageID?siteview=0'>Products list view</a>
       | <a href='$cfg[RootURL]/edit/$ctx/$Control->JSBPageID?siteview=1'>Website view</a>
      </td></tr></table>";

      if (!$_GET['siteview'])
        {
        $_ENV->LoadTheme ('b');
#        $_ENV->InitWindows();
        $_ENV->InitPage(array(Environment=>'b'));
        $IProduct=&$_ENV->LoadInterface("store.IProduct");
        $IProduct->EditGroup(array(JSBPageID=>$Control->JSBPageID,FilterVisible=>$_GET['FilterVisible']));
        exit;
        }
      }
    }
  }

}
