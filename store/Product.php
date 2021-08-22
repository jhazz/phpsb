<?
class store_Product
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function store_Product()
  {
  $_=&$GLOBALS[_STRINGS][store];
  $this->Title="Product context handler";
  }

function InitComponent()
  {
  global $cfg;
  $_=&$GLOBALS[_STRINGS][store];
  $this->Propdefs=array(
    TableStyle =>array(Type=>"String",Caption=>"TableStyle",GetValueFrom=>"jsb.IThemeReader.SelectTableStyle"),
    );
  $this->Datadefs=array(
    Product     =>array(DataType=>"store.Product",Caption=>"Product"),
    Name        =>array(DataType=>"String",Caption=>$_[PRODUCT_NAME]),
    Teaser      =>array(DataType=>"String",Caption=>$_[PRODUCT_TEASER]),
    Price       =>array(DataType=>"String",Caption=>$_[COLUMN_BCPRICE]),
    VCPrice     =>array(DataType=>"String",Caption=>$_[COLUMN_VCPRICE]),
    ProdGroup   =>array(DataType=>"store.ProdGroup",Caption=>"ProductGroup"),
    Options     =>array(DataType=>"string",Caption=>$_[PRODUCT_OPTIONS]),
    Image       =>array(DataType=>"img.Image",Caption=>"Image"),
    Album       =>array(DataType=>"img.Image",Caption=>"Album"),
    Descriptions=>array(DataType=>"stdctrls.Richtext",Caption=>"Description texts about product"),
    ProdGroupImage =>array(DataType=>"img.Image",Caption=>"Product group image"),
    ProdGroupCaption=>array(DataType=>"String",Caption=> 'Product group short name'),
    ProdGroupTitle=>array(DataType=>"String",Caption=> 'Product group title'),
    );
  }


function Init (&$Control)
  {
  $_=&$GLOBALS[_STRINGS][store];
  $__=&$GLOBALS[_STRINGS][_];
  global $cfg;
  if ($Control->Properties) extract ($Control->Properties);


  if ($Control->DesignMode)
    {
    $Control->Data['ProdGroupImage']="store.ProdGroup/image/";
    $Control->Data['Image']  ="store.Product/image/";
    $Control->Data['Album']  ="store.Product/album/";
    return;
    }
  $ProductID=intval($Control->JSBPageID);
  $qp=DBQuery ("SELECT * FROM store_Products WHERE ProductID=$ProductID");
  if (!$qp)
    {
    return array(PageNotFound=>1);
    }
  $groupctx=$cfg['Settings']['store']['ProductGroupsContext'];
  $qpg=DBQuery ("SELECT Caption,Title FROM jsb_Pages WHERE JSBPageID=".$qp->Top->GroupID." AND SysContext='$groupctx'");
  if ($qpg)
    {
    $Control->Data['ProdGroupCaption']=langstr_get($qpg->Top->Caption);
    $Control->Data['ProdGroupTitle']=langstr_get($qpg->Top->Title);
    }

  $Control->Data['Product']="store.Product/$ProductID";
  $Control->Data['Image']  ="store.Product/image/$ProductID";
  $Control->Data['Album']  ="store.Product/album/$ProductID";
  $Control->Data['Descriptions']="store.Product/desc/$ProductID";
  $Control->Data['Price']="";
  $Control->Data['PreDiscountPrice']="";
  if (!$qp) {return;}
  $Control->Data['ProdGroup']="store.ProdGroup/".$qp->Top->GroupID;
  $Control->Data['ProdGroupImage']="store.ProdGroup/image/".$qp->Top->GroupID;
  $s=langstr_get($qp->Top->Name);
  global $_TITLE;
  $_TITLE=$s.$_TITLE;
  $Control->Data['Name']=$s;
  $Control->Data['Teaser']=langstr_get($qp->Top->Teaser);

  $ImageBindList="";
  global $ICurrency;
  if (!$ICurrency)
    {
    $ICurrency=&$_ENV->LoadInterface("store.PCurrencies");
    $ICurrency->Init();
    }

/*
  if ($cfg['Settings']['store']['ShowPrice'])
    {
    global $_USER;
    $_USER->LoadGroups();
    $PriceColumns=false;

    if ($_USER->Groups)
      {
      $UserGroupsIn=implode(",",$_USER->Groups);
      $q=DBQuery ("SELECT PriceColumnField,Name,CurrencyID FROM store_PriceColumns WHERE UserGroupID IN ($UserGroupsIn) ORDER BY ColumnID");
      if ($q)
        {
        foreach ($q->Rows as $i=>$row)
          {
          $PriceColumns[$row->PriceColumnField]=array($row->CurrencyID,$row->Name);
          }
        }
      }

    if ($PriceColumns)
      {
      foreach ($PriceColumns as $PriceFieldName=>$params)
        {
        $Control->Data['Price']=$ICurrency->Format($qp->Top->$PriceFieldName,$params[0]);
        }
      }
    else
      if (!$cfg['Settings']['store']['HidePriceFromNonpermitted'])
      {
      $Control->Data['Price']=$ICurrency->BCFormat($qp->Top->BCPrice);
      if ($cfg['Settings']['store']['ShowVCPrice']->Value==1)
        {
        $Control->Data['VCPrice']=$ICurrency->VCFormat($qp->Top->VCPrice);
        }
      }
    }

*/

  $qo=DBQuery ("SELECT op.OptionID, opv.Name AS ValueName, op.Name AS OptionName, op.Unit, op.Info
    FROM (store_Options AS op INNER JOIN store_OptionValues AS opv ON op.OptionID = opv.OptionID) INNER JOIN store_ProdOptions AS po ON (op.OptionID = po.OptionID) AND (opv.OptionValueID = po.OptionValueID)
    WHERE po.ProductID=$ProductID ORDER BY op.OrderNo","OptionID");
  if ($qo)
    {
#    $qo->Dump();
    # ������� ����� ������� ������������
    $usedfields=false;
    foreach ($qo->Rows as $OptionID=>$row)
      {
      for ($i=0;$i<count($qo->Fields);$i++)
        {
        $fname=&$qo->Fields[$i];
        if ($row->$fname) $usedfields[$fname]=1;
        }
      }
    $s="";
    $rowno=0;
    global $_THEME;
    $ts=false;
    if ($_THEME)
      {
      $ts=$_THEME['TableStyles'][$TableStyle];
      }
    list($th,$ch)=get_css_pair($ts['Top'],"td");
    list($te,$ce)=get_css_pair($ts['Even'],"td");
    list($to,$co)=get_css_pair($ts['Odd'] ,"td");
    $tle=$te; $cle=$ce; $tlo=$to; $clo=$co;
    if ($ts['LeftEven']) {list($tle,$cle)=get_css_pair($ts['LeftEven'],"td");}
    if ($ts['LeftOdd'] ) {list($tlo,$clo)=get_css_pair($ts['LeftOdd'] ,"td");}

    foreach ($qo->Rows as $OptionID=>$row)
      {
      if ($rowno & 1) {$t=$to; $c=$co; $tl=$tlo; $cl=$clo;} else {$t=$te; $c=$ce; $tl=$tle; $cl=$cle;}
      $s.="<tr valign='top'><$tl $cl><b>$row->OptionName:</b></$tl><$t $c>$row->ValueName</$t>";
      if ($usedfields['Unit']) $s.="<$t$c>$row->Unit</$t>";
      if ($usedfields['Info']) $s.="<$t$c>$row->Info</$t>";
      $s.="</tr>";
      $rowno++;
      }
    if ($s)
      {
      $s2="<$th $ch>$_[OPTION_NAME]</$th><$th $ch>$_[OPTION_VALUE]</$th>";
      if ($usedfields['Unit']) $s2.="<$th $ch>$_[OPTION_UNIT]</$th>";
      if ($usedfields['Info']) $s2.="<$th $ch>$_[OPTION_INFO]</$th>";
      $Control->Data['Options']="<table width='100%'><tr>$s2</tr>$s</table>";
      }
    }
  }

function Render(&$Control)
  {
  $_=&$GLOBALS[_STRINGS][store];
  global $cfg;
  if ($Control->DesignMode)
    {
    print $_['TPRODUCT'];
    }
  }



function OnPageLoad()
  {
#  return array(HTTPError=>404);

  }


function Select($args)
  {
  $_=&$GLOBALS[_STRINGS][store];
  global $cfg;
  extract(param_extract(array(
    ProductGroupID=>'int',
    SysContext=>'string',
    ),$args));

  print "Choose product in $SysContext";
  $qg=DBQuery ("SELECT JSBPageID,Caption FROM jsb_Pages WHERE SysContext='".$cfg['Settings']['store']['ProductGroupsContext']."' ORDER BY OrderNo","JSBPageID");

  $s="";
  foreach ($qg->Rows as $JSBPageID=>$row)
    {
    if ($s) $s.=" | ";
    if ($JSBPageID==$ProductGroupID) $s.=$row->Caption;
    else $s.="<a style='font-size:10px' href='".ActionURL('store.CProducts.Select.bm',array(ProductGroupID=>$JSBPageID,SysContext=>$SysContext))."'>$row->Caption</a>";
    }
  print "<table><tr valign='top'><td align='center' bgcolor='#000000'>$s</td></tr><tr><td>";
  $s="";

  if ($ProductGroupID)
    {
    $qp=DBQuery ("SELECT ProductID,Name FROM store_Products WHERE GroupID=$ProductGroupID ORDER BY OrderNo","ProductID");
    if ($qp)
      {
      foreach ($qp->Rows as $ProductID=>$row)
        {
        $s.="<li><a href='javascript:;' onClick='W.modalResult($ProductID);'>$row->Name</a></li>";
        }
      if (!$s)
        {
        print $_[TPRICELIST_NOPRODUCTSINGROUP];
        } else print $s;
      }
    }
  print "</td></tr></table>";
  }



}

?>
