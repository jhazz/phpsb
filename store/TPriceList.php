<?php
class store_TPriceList
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  global $cfg;
  $_=&$GLOBALS[_STRINGS][store];
  $this->About=$_[PRICELIST];
  $this->Propdefs=array(
    TableStyle=>array(Type=>"ThemeElement",Section=>"TableStyles",Caption=>$_['TPRICELIST_P_TABLESTYLE']),
    BtnPutToCart=>array(Type=>"Caption",DefaultValue=>$_['TPRICELIST_BTN_PUTTOCART']),
    EmptyText=>array(Type=>"Caption",DefaultValue=>$_['TPRICELIST_NOPRODUCTSINGROUP']),
    ProductTargetContext=>array(Type=>"SysContext",Caption=>$_['TPRICELIST_PRODUCTTARGETCONTEXT'],DefaultValue=>$cfg['Settings']['store']['ProductGroupsContext']),
    BindToSelectedAttrs=>array(Type=>"Binding",DataType=>"store.SelectedAttrs",Caption=>$_['DATADEF_STORE_SELECTEDATTRS']),
    JumpAfterPut=>array(Type=>"LocalURL",Caption=>$_['TPRICELIST_JUMPAFTERPUT']),
    DefaultSetName=>array(Type=>"String",Caption=>$_['DEFAULT_SET_NAME'],DefaultValue=>$_['DEFAULT_SET_NAME']),
    DefaultRequestName=>array(Type=>"String",Caption=>$_['DEFAULT_REQUEST_NAME'],DefaultValue=>$_['DEFAULT_REQUEST_NAME'])
    );
  $this->Datadefs=array(
    UsedOptions=>array(DataType=>"store.UsedOptions",Caption=>$_['DATADEF_STORE_USEDATTRS']),
    CatalogImage=>array(DataType=>"img.Image",Caption=>$_['CATALOG_SECTION_IMAGE']),
    );
  }


function Init (&$Control)
  {
  global $cfg;
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  $GroupID=$Control->JSBPageID;
  $Control->Data['ProductGroupID']=$GroupID;
  $Control->ProductsList=DBQuery ("SELECT ProductID,Status,HotNewSale FROM store_Products WHERE GroupID=$GroupID AND Hidden=0 ORDER BY OrderNo","ProductID");
  $Control->ProdAttrs=false;
  if ($Control->DesignMode)
    {
    $Control->Data['CatalogImage']="store.ProdGroup/image/";
    }
  else
    {
    $Control->Data['CatalogImage']="store.ProdGroup/image/$GroupID";
    }
  if ($Control->ProductsList)
    {
    $Control->ProdIDs=implode(',',array_keys($Control->ProductsList->Rows));

    $q=DBQuery ("SELECT OptionID FROM store_ProdOptions WHERE ProductID IN ($Control->ProdIDs) GROUP BY OptionID ","OptionID ");
    if ($q)
      {
      $Control->Data['UsedOptions']=implode("^",array_keys($q->Rows));
      }
    }
  $Control->ColumnWidths=array(Name=>"70%",BCPrice=>"10%",VCPrice=>"10%");
  }

function Render(&$Control)
{
$_=&$GLOBALS[_STRINGS][store];
global $cfg;
extract ($Control->Properties);

$this->DefaultSetName=&$DefaultSetName;
$this->DefaultRequestName=&$DefaultRequestName;
$this->JumpAfterPut=$JumpAfterPut;

$List=&$Control->ProductsList;
if (!$List)
  {
  if ($Control->DesignMode)
    {
    print $_[TPRICELIST_SAMPLE];
    print "<br>";
    }

  print $EmptyText;
  return;
  }

$maxrow=$List->RowCount-1;
$SelectedAttrs=$Control->Bindings['BindToSelectedAttrs'];


$Fields=array(Name=>$_['COLUMN_PRODUCTNAME']);
$FieldHooks=array(Name=>tab_ShowIt,BCPrice=>tab_ShowBCPrice,VCPrice=>tab_ShowVCPrice,);

if ($SelectedAttrs)
  {
  $attrlist="";
  foreach ($SelectedAttrs as $a)
    {
    if ($attrlist) {$attrlist.=",";}
    $attrlist.="'".DBEscape ($a)."'";
    $Fields[$a]=$a;
    $FieldHooks[$a]=tab_ShowAttrValue;
    }
  global $qa;
  $s="SELECT * FROM store_ProdAttrValues WHERE ProductID IN ($Control->ProdIDs) AND (Attr IN ($attrlist))";
  $qa=DBQuery ($s,array("ProductID","Attr"));
  }

global $ICurrency;
$ICurrency=&$_ENV->LoadInterface("store.PCurrencies");
$ICurrency->Init();
$Fields[VCPrice]=$_[COLUMN_VCPRICE].', '.$ICurrency->VC->ConversionName;
$Fields[Guarantee]=$_[COLUMN_GUARANTEE];
$Fields[BCPrice]=$_[COLUMN_BCPRICE].', '.$ICurrency->BC->ConversionName;

$GLOBALS[_CORE]->PrintTable($List,array(
  URL=>ActionURL("store.IPriceList.DoAction.f"),
  Fields=>$Fields,
  ShowCheckers=>true,
  FieldHooks=>$FieldHooks,
  Buttons=>array(puttocart=>$BtnPutToCart),
  TableStyle=>$TableStyle,
  Width=>'100%',
  ColWidths=>$Control->ColumnWidths,
  BgColor_Hovered=>'#ffe0e0',
  BgColor_Checked=>'#ffffe0',
  OnBeforeStart=>$this->tab_BeforeStart,
  ThisObject=>&$this
  ));

}


function tab_ShowIt(&$rowno,&$row)
  {
  print "<a href='../storeproduct/$row->ProductID.html'>$row->Name</a>";
  }
function tab_ShowAttrValue($rowno,$row,$fieldname)
  {
  global $qa;
  $row=$qa->Rows[$row->ProductID][$fieldname];
  print $row->Value;
  }
function tab_ShowBCPrice(&$rowno,&$row)
  {
  global $ICurrency;
  print $ICurrency->BCFormat($row->BCPrice);
  }
function tab_ShowVCPrice(&$rowno,&$row)
  {
  global $ICurrency;
  print $ICurrency->VCFormat($row->VCPrice);
  }
function tab_BeforeStart(&$this)
  {
  print "<input type='hidden' name='DefaultRequestName' value='$this->DefaultRequestName'>";
  print "<input type='hidden' name='DefaultSetName' value='$this->DefaultSetName'>";
  print "<input type='hidden' name='JumpAfterPut' value='$this->JumpAfterPut'>";
  }

}
?>
