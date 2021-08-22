<?
class mship_Group{
var $CopyrightText="(c)2006 JhAZZ Site Builder. Membership system";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['msg'];
  $this->Datadefs=array(
      Group=>array(DataType=>"mship.Group",Caption=>"Membership group",DefaultValue=>"mship.Group/"),
      CatalogImage =>array(DataType=>"img.Image",Caption=>"Membership catalog image",DefaultValue=>"mship.Catalog/image/"),
      PageCaption=>array(DataType=>"String",Caption=>'Group short name'),
      PageTitle=>array(DataType=>"String",Caption=>'Group large title'),
     );
  }

function Init (&$Control)
  {
  global $cfg;
  global $_ENVIRONMENT,$JSB_PageData;


  if ($Control->SysContext!='layouts')
    {
    $Control->Data['Group']="mship.Group/$Control->JSBPageID";
    $Control->Data['CatalogImage']="mship.Catalog/image/$Control->JSBPageID";
    $Control->Data['PageCaption']=langstr_get($JSB_PageData->Caption);
    $Control->Data['PageTitle']=langstr_get($JSB_PageData->Title);
    if ($Control->DesignMode)
      {

      print "<table width='100%' cellpadding='5'><tr><td class='bgup'>
       <a href='$cfg[RootURL]/edit/$Control->SysContext/$Control->JSBPageID?siteview=0'>Member list view</a>
       | <a href='$cfg[RootURL]/edit/$Control->SysContext/$Control->JSBPageID?siteview=1'>Website view</a>
      </td></tr></table>";

      if (!$_GET['siteview'])
        {
        $_ENV->LoadTheme ('b');
#        $_ENV->InitWindows();
        $_ENV->InitPage(array(Environment=>'b'));
        $IMembers=&$_ENV->LoadInterface("mship.IMembers");
        $IMembers->Browse(array(FilterVisible=>$_GET['FilterVisible'],CatalogID=>$Control->JSBPageID));
        exit;
        }
      }

    }
  }

}
