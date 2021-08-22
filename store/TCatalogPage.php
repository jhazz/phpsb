<?php
class store_TCatalogPage
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
    BtnPutToCart=>array(Type=>"Caption",Caption=>$_[TPRICELIST_BTN_PUTTOCART],DefaultValue=>$_[TPRICELIST_BTN_PUTTOCART]),
    EmptyText=>array(Type=>"Caption",Caption=>$_[TPRICELIST_NOPRODUCTSINGROUP],DefaultValue=>$_[TPRICELIST_NOPRODUCTSINGROUP]),
    ProductTargetContext=>array(Type=>"SysContext",Caption=>$_[TPRICELIST_PRODUCTTARGETCONTEXT],DefaultValue=>$cfg['Settings']['store']['ProductInfoContext']),
    BindToHighlightedOptions=>array(Type=>"Binding",DataType=>"store.ViewOptions",Caption=>$_[DATADEF_HIGHLIGHTED_OPTIONS]),
    DefaultSetName=>array(Type=>"String",Caption=>$_[DEFAULT_SET_NAME],DefaultValue=>$_[DEFAULT_SET_NAME]),
    DefaultRequestName=>array(Type=>"String",Caption=>$_[DEFAULT_REQUEST_NAME],DefaultValue=>$_[DEFAULT_REQUEST_NAME]),
    ColumnCount=>array(Type=>"Int",Caption=>$_[COLUMNCOUNT_ON_PAGE],DefaultValue=>2),
    More=>array(Type=>"Caption",Caption=>$_[PRODUCT_MORE],DefaultValue=>$_[PRODUCT_MORE]),
    TeaserAsLink=>array(Type=>"Boolean",Caption=>$_[PRODUCT_TEASERASLINK]),

    CSS_Name=>array(Type=>"CSS_Class",Caption=>$_[CSS_PRODUCT_NAME],BaseCSSClass=>"p"),
    CSS_Teaser=>array(Type=>"CSS_Class",Caption=>$_[CSS_PRODUCT_TEASER],BaseCSSClass=>"p"),
    CSS_Price=>array(Type=>"CSS_Class",Caption=>$_[CSS_PRODUCT_PRICE],BaseCSSClass=>"p"),
    CSS_OtherPrice=>array(Type=>"CSS_Class",Caption=>$_[CSS_PRODUCT_OTHER_PRICE],BaseCSSClass=>"p"),
    CSS_Link=>array(Type=>"CSS_Class",Caption=>$_[CSS_PRODUCT_LINK],BaseCSSClass=>"a"),
    CSS_Border =>array(Type=>"CSS_Class",Caption=>$_[CSS_IMG_BORDER],BaseCSSClass=>"td"),
    ReplaceEmptyImg=>array(Type=>"String",Caption=>$_['SELECT_EMPTY_IMAGE'],GetValueFrom=>"jsb.IThemeReader.SelectSkinImage"),
    );
  $this->Datadefs=array(
    UsesOptions=>array(DataType=>"store.UsesOptions",Caption=>$_[DATADEF_STORE_USESOPTIONS]),
    ProdGroupImage =>array(DataType=>"img.Image",Caption=>"Product group image"),
    );
  }


function Init (&$Control)
  {
  global $cfg,$_ENV,$_USER,$_LANGUAGE;
  $_ =&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];

  if ($Control->$Control->ControlEditor)
    {
    $Control->Data['ProdGroupImage']="store.ProdGroup/image/";
    return;
    }

  $ProductGroupID=$Control->JSBPageID;
  $Control->Data['ProductGroupID']=$ProductGroupID;
  $Control->Data['ProdGroupImage']="store.ProdGroup/image/$ProductGroupID";

#  $UserGroups=implode (",",$_USER->Groups);

  $Control->Products  =DBQuery ("SELECT ProductID,Name,Teaser,HotNewSale,BaseProductID FROM store_Products
    WHERE GroupID=$ProductGroupID AND Hidden=0 ORDER BY OrderNo","ProductID");
  if ($Control->Products)
    {
    $Control->Columns=DBQuery ("SELECT pc.*, c.Prefix, c.Suffix, c.Decimals, c.LangID, r.Rate
    FROM (store_PriceColumns AS pc LEFT JOIN store_Currencies AS c ON pc.CurrencyID = c.CurrencyID)
    LEFT JOIN store_CurrencyRates AS r ON c.CurrencyID = r.CurrencyID
    WHERE c.LangID='$_LANGUAGE' ORDER BY pc.OrderNo","ColumnID");
    $Control->Prices=DBQuery ("SELECT p.ProductID, p.Hidden, v.ColumnID, v.Value, p.GroupID
    FROM store_Products AS p LEFT JOIN store_Prices AS v ON p.ProductID = v.ProductID
    WHERE p.Hidden=0 AND p.GroupID=$ProductGroupID","ProductID");
#    $Control->Options=DBQuery ("SELECT Name,HasImage,Colour,Unit,Info
#      WHERE ShowInBrief=1
#      ORDER BY OrderNo","OptionID");
    $Control->Options=DBQuery("SELECT p.ProductID, p.Hidden, p.GroupID, po.OptionID,
    po.OptionValueID, o.Name, o.Unit, o.Colour, o.HasImage, ov.Name
FROM ((store_Products AS p INNER JOIN store_ProdOptions AS po ON p.ProductID = po.ProductID)
INNER JOIN store_OptionValues AS ov ON po.OptionValueID = ov.OptionValueID)
INNER JOIN store_Options AS o ON po.OptionID = o.OptionID
WHERE (p.Hidden=0) AND (p.GroupID=$ProductGroupID) AND (o.ShowInBrief=1)
ORDER BY o.OrderNo",array("ProductID","OptionID"));

    $ImageBindList="";
    foreach ($Control->Products->Rows as $ProductID=>$row)
      {
      if ($ImageBindList) $ImageBindList.=",";
      $ImageBindList.="'store.Product/image/$ProductID'";
      }
    $Control->ProdIDs=implode(',',array_keys($Control->Products->Rows));

    # PREPARE IMAGE INFO
    $Control->Images=DBQuery ("SELECT BindTo,Filenames FROM img_Documents WHERE BindTo IN ($ImageBindList)","BindTo");
    $q=DBQuery ("SELECT OptionID FROM store_ProdOptions WHERE ProductID IN ($Control->ProdIDs) GROUP BY OptionID","OptionID");
    if ($q)
      {
      $Control->Data['UsesOptions']=implode(",",array_keys($q->Rows));
      }
    }
  }

function Render(&$Control)
  {
  $_=&$GLOBALS[_STRINGS][store];
  global $cfg;
  extract ($Control->Properties);
  if (!$ColumnCount) {$ColumnCount=2;}
  $this->DefaultSetName=&$DefaultSetName;
  $this->DefaultRequestName=&$DefaultRequestName;
  $this->JumpAfterPut=$JumpAfterPut;

  if (!$Control->Products)
    {
    if ($Control->DesignMode)
      {
      print $_[TPRICELIST_SAMPLE];
      print "<br>";
      }
    print $EmptyText;
    return;
    }

  $maxrow=$Control->Products->RowCount-1;
  $SelectedAttrs=$Control->Bindings['BindToSelectedAttrs'];

  if ($SelectedAttrs)
    {
    $attrlist="";
    foreach ($SelectedAttrs as $a)
      {
      if ($attrlist) {$attrlist.=",";}
      $attrlist.="'".DBEscape ($a)."'";
      }
    global $qa;
    $s="SELECT * FROM store_OptionValues WHERE ProductID IN ($Control->ProdIDs) AND (OptionID IN ($attrlist))";
    $qa=DBQuery ($s,array("ProductID","Attr"));
    }

  if ($CSS_Border)
    {
    list ($Border_tag,$Border_class)=get_css_pair ($CSS_Border,"td");
    }
  print "<table cellpadding='10' width='100%'>"; $col=1;
  $colwidth=" width='".round(100/$ColumnCount)."%'";
  foreach ($Control->Products->Rows as $ProductID=>$row)
    {
    if ($row->BaseProductID!=0) continue;

    if ($col==1) {print "<tr valign='top'>";}
    print "<td $colwidth><table border='0'><tr valign='top'><td>";

    $href="../$ProductTargetContext/$ProductID";

    $Tn=$Control->Images->Rows["store.Product/image/$ProductID"];
    if ($Tn)
      {
      $TnNames=$_ENV->Unserialize($Tn->Filenames);
      $TnName=$TnNames[1];
      $Filepath=$cfg['FilesPath'].'/img/store.Product/image/'.$TnName;
      $Fileurl=$cfg['FilesURL']  .'/img/store.Product/image/'.$TnName;
      if (is_file($Filepath))
        {
        $size=@getimagesize($Filepath);
        if ($size)
          {
          $s="<a href='$href'><img border='0' alt='$row->Name' $size[3] src='$Fileurl'></a>";
          if ($Border_tag)
            {
            $s="<table><tr><$Border_tag $Border_class>$s</$Border_tag></tr></table>";
            }
          print $s;
          print "</td><td>";
          }
        }
      }
    else {
      if ($ReplaceEmptyImg)
        {
        global $_THEME_NAME;
        $imgURL = $cfg[SkinsURL] .'/'.$_THEME_NAME.'/'.$ReplaceEmptyImg;
        $imgPath= $cfg[SkinsPath] .'/'.$_THEME_NAME.'/'.$ReplaceEmptyImg;
        if (file_exists($imgPath))
          {
          $size=@getimagesize($imgPath);
          print "<a href='$href'><img src='$imgURL' $size[3] border='0'></a></td><td>";
          }
        }
      }
    list($t,$c)=get_css_pair($CSS_Name,'p');
    print "<$t$c>".langstr_get($row->Name)."</$t>";
    list($t,$c)=get_css_pair($CSS_Teaser,'p');
    list($tl,$cl)=get_css_pair($CSS_Link,'a');

    if ($TeaserAsLink)
      {
      print "<$tl$cl><$t$c>".langstr_get($row->Teaser)."</$t></$tl>";
      }
    else
      {
      print "<$t$c>".langstr_get($row->Teaser)." <$tl$cl href='$href'>$More</$tl></$t>";
      }

    $attentions=explode (",",$_[PRODUCT_HOTNEWSALE]);
    switch ($row->HotNewSale)
      {
      case '1': print "<span style='color:#ffffff; background-color:#ff0000'><b>&nbsp;".$attentions[1]."&nbsp;</b></span>"; break;
      case '2': print "<span style='color:#ffffff; background-color:#ff0000'><b>&nbsp;".$attentions[2]."&nbsp;</b></span>"; break;
      case '3': print "<span style='color:#ffffff; background-color:#ff9900'><b>&nbsp;".$attentions[3]."&nbsp;</b></span>"; break;
      }
    if ($cfg['Settings']['store']['ShowPrice']==1)
      {
      if ($Control->PriceColumns)
        {
        $FirstPrice=true;
        foreach ($Control->PriceColumns as $PriceFieldName=>$params)
          {
          if ($FirstPrice)
            {
            list($t,$c)=get_css_pair($CSS_Price,'p');
            }
          else {
            list($t,$c)=get_css_pair($CSS_OtherPrice,'p');
            }
          $s1=$params[1]; # Name of the price
          if ($s1) {$s1="<b>$s1:</b>&nbsp;";}
          print "<$t$c>$s1".$ICurrency->Format($row->$PriceFieldName,$params[0])."</$t>";
          $FirstPrice=false;
          }
        }
      else
        if (!$cfg['Settings']['store']['HidePriceFromNonpermitted'])
        {
        list($t,$c)=get_css_pair($CSS_Price,'p');

        print "<$t$c></$t>";
        }
      }

    print "</td></tr></table></td>";
    if ($col==$ColumnCount) {print "</tr>"; $col=1;} else {$col++;}
    }
  if ($col!=1) print "</tr>";
  print "</table>";
  }

}
?>
