<?
class store_IProdGroup
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function Browse($args)
  {
  global $cfg;
  $qp=DBQuery ("SELECT HomePageID FROM sys_Contexts WHERE SysContext='".$cfg['Settings']['store']['ProductGroupsContext']."'");
  $HomePageID=0;
  if ($qp) { $HomePageID=intval($qp->Top->HomePageID); }
  $args=array(
    Path=>"/".$cfg['Settings']['store']['ProductGroupsContext']."/$HomePageID",
    ContextLocked=>1,
//    NoLayouts=>1
    );
  Header("Location: ".ActionURL("jsb.ISiteExplorer.Open.n",$args));
  }
/*
function OnPageLoad($PageData,$DesignMode)
  {
  global $_ENVIRONMENT,$cfg;

  if ($DesignMode)
    {
    print "<table width='100%' cellpadding='5'><tr><td class='bgup'>
     <a href='$cfg[RootURL]/edit/".$cfg['Settings']['store']['ProductGroupsContext']."/$PageData->JSBPageID?siteview=0'>Products list view</a>
     | <a href='$cfg[RootURL]/edit/".$cfg['Settings']['store']['ProductGroupsContext']."/$PageData->JSBPageID?siteview=1'>Website view</a>
    </td></tr></table>";
    }

  if (($DesignMode)&&(!$_GET['siteview']))
    {
    $_ENV->LoadTheme ('b');
    $_ENV->InitWindows();
    $_ENV->InitPage(array(Environment=>'b'));
    $IProduct=&$_ENV->LoadInterface("store.IProduct");
    $IProduct->EditGroup(array(JSBPageID=>$PageData->JSBPageID,FilterVisible=>$_GET['FilterVisible']));
    exit;
    }
  else
    $_ENV->InitPage(array(Environment=>'f'));
  }

                          */
function XMLDescription ($args)
  {
  global $cfg;
  extract(param_extract(array(
    ProductID=>'int',
    TnFormatNo=>'int=1',
    ),$args));

  $qp=DBQuery ("SELECT Name,Teaser FROM store_Products WHERE ProductID=$ProductID");
  if (!$qp)
    {
    return array(XML=>"<p>Product ID $ProductID</p>");
    }
  $result="<h2>".$qp->Top->Name."</h2>";
  $qo=DBQuery ("SELECT op.OptionID, opv.Name AS ValueName, op.Name AS OptionName, op.Unit, op.Info
    FROM (store_Options AS op INNER JOIN store_OptionValues AS opv ON op.OptionID = opv.OptionID) INNER JOIN store_ProdOptions AS po ON (op.OptionID = po.OptionID) AND (opv.OptionValueID = po.OptionValueID)
    WHERE po.ProductID=$ProductID ORDER BY op.OrderNo","OptionID");

  if ($qo)
    {
    $s="";
    foreach ($qo->Rows as $id=>$r)
      {
      $s.="\n<tr><td>$r->OptionName</td><td>$r->ValueName</td><td>$r->Unit</td><td>$r->Info</td></tr>";
      }
    if ($s)
      {
      $result.="<table autoformat='2'><tr><td>��������</td><td>��������</td><td>��.���</td></tr>$s</table>";
      }
    }
  $BindTo="store.Product/image/$ProductID";
  $qi=DBQuery ("SELECT Filenames FROM img_Documents WHERE BindTo='$BindTo'");
  if ($qi)
    {
    $Filenames=$_ENV->Unserialize($qi->Top->Filenames);
    $TnName=$Filenames[$TnFormatNo];
    $TnURL =$cfg['FilesURL'] ."/img/store.Product/image/$TnName";
    $TnPath=$cfg['FilesPath']."/img/store.Product/image/$TnName";
    $size=@getimagesize($TnPath);
    if ($size) {
      $result.="<image src=\"$TnURL\" $size[3] />\n";
      }
    }
  return array(XML=>"<page>".$result."</page>");
  }


}
