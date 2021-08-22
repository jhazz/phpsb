<?php
class store_TCatalogIndex
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Webstore";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  global $cfg;

  $_=&$GLOBALS['_STRINGS']['store'];
  $this->Propdefs=array(
    ShowTitleAsTeaser =>array(Type=>"Boolean"),
    Root=>array(Editable=>1,Required=>1,Type=>"InputModal",ModalCall=>"jsb.IPage.Select",InitCall=>"jsb.IPage.GetPageNameByValue",DefaultValue=>$cfg['Settings']['store']['ProductGroupsContext']),
    TargetContext=>array(Type=>"SysContext",ObjectClass=>"store.ProdGroup",Caption=>$_['TPRICELIST_PRODUCTTARGETCONTEXT'],DefaultValue=>$cfg['Settings']['store']['ProductGroupsContext']),
    CSS_Caption=>array(Type=>"CSS_Class",Caption=>$_['CSS_PRODUCT_NAME'],BaseCSSClass=>"p"),
    CSS_Teaser=>array(Type=>"CSS_Class",Caption=>$_['CSS_PRODUCT_TEASER'],BaseCSSClass=>"p"),
    CSS_Link=>array(Type=>"CSS_Class",Caption=>$_['CSS_PRODUCT_LINK'],BaseCSSClass=>"a"),
    ImageMore=>array(DefaultValue=>"more.gif",Type=>"InputModal",Editable=>1,InitCall=>"jsb.IThemeReader.GetSkinImageByValue",ModalCall=>"jsb.IThemeReader.SelectSkinImage"),
#    Style=>array(Type=>'list', Values=>array(drawtit=>"Draw titles")),
    ColumnCount=>array(Type=>'Int'),
    Level1Style=>array(Type=>"CSS_Class",AddDrawingFonts=>true),
    Level2Style=>array(Type=>"CSS_Class",AddDrawingFonts=>true),
    );
  $this->Datadefs=array(
    UsesOptions=>array(DataType=>"store.UsesOptions",Caption=>$_[DATADEF_STORE_USESOPTIONS]),
    ProdGroupImage =>array(DataType=>"img.Image",Caption=>"Product group image"),
    );
  }


function Init (&$Control)
  {
  global $cfg,$_ENV,$_USER,$_LANGUAGE;
  $_=&$GLOBALS['_STRINGS']['store'];
  $__=&$GLOBALS['_STRINGS']['_'];
  extract ($Control->Properties);

  if ($Control->DesignMode)
    {
    $Control->Data['ProdGroupImage']="store.ProdGroup/image/";
    }

  if (!$this->Groups)
    {
    $this->Groups=DBQuery ("SELECT JSBPageID,ParentID,Caption,Title FROM jsb_Pages
    WHERE State=1 AND SysContext='".$cfg['Settings']['store']['ProductGroupsContext']."'
    ORDER BY ParentID,OrderNo",array("ParentID","JSBPageID"));
    }

  if ($this->Groups)
    {
    $Control->ProdCounts=DBQuery ("SELECT COUNT(*) AS PCount,GroupID FROM store_Products WHERE BaseProductID=0 AND Hidden=0 GROUP BY GroupID","GroupID");
    $ImageBindList="";
    foreach ($this->Groups->Rows as $aParentID=>$col)
      {
      foreach($col as $aJSBPageID=>$row)
        {
        if ($ImageBindList) $ImageBindList.=",";
        $ImageBindList.="'store.ProdGroup/image/$aJSBPageID'";
        }
      }
    # PREPARE IMAGE INFO
    $s="SELECT BindTo,Filenames FROM img_Documents WHERE BindTo IN ($ImageBindList)";
    $Control->Images=DBQuery ($s,"BindTo");
    }
  }

function Render(&$Control)
  {
  
  $_=&$GLOBALS['_STRINGS']['store'];
  global $cfg;
  extract ($Control->Properties);

#  if (!$ColumnCount) {$ColumnCount=2;}

  list ($Context,$ProductGroupID)=explode ("/",$Root);
/*  if (!$ShowRoot)
    {
    if ((!$Control->DesignMode)&&($Control->SysContext!=$cfg['Settings']['store']['ProductGroupsContext']))
      {
      print_developer_warning("You should put TCatalogIndex onto layout page of store catalog context or switch ShowRoot on");
      return;
      }
    $ProductGroupID=$Control->JSBPageID;
    }
  else
    $ProductGroupID=0;
  */
  if (!$ProductGroupID) $ProductGroupID=0;

  $this->PDrawText=&$_ENV->LoadInterface("stdctrls.PDrawText");
  if ($this->Groups)
    {
    $txt=$this->_getgrouptext($ProductGroupID,$Control,$ColumnCount);
    }
  if ($txt) print "<table>$txt</table>";
#  print $txt;
  }

function _getgrouptext($ProductGroupID,&$Control,$ColumnCount,$Level=0)
  {
  global $cfg,$_THEME;
  extract ($Control->Properties);
  list ($Link_tag,$Link_class)=get_css_pair ($CSS_Link,"a");

  $r="";
  if (($this->Groups)&&($Control->ProdCounts))
    {
    $row=&$this->Groups->Rows[$ProductGroupID];
    if ($row)
      {
      $Caption=langstr_get($row->Caption);
      $Title=langstr_get($row->Title);
      foreach($row as $aGroupID=>$col)
        {
        $Caption=langstr_get($col->Caption);
        $Title=langstr_get($col->Title);
        $img=$Control->Images->Rows["store.ProdGroup/image/$aGroupID"];
        if ($img)
          {
          $TnNames=$_ENV->Unserialize($img->Filenames);
          $TnName=$TnNames[1];
          $Filepath=$cfg['FilesPath'].'/img/store.ProdGroup/image/'.$TnName;
          $Fileurl=$cfg['FilesURL']  .'/img/store.ProdGroup/image/'.$TnName;
          $teaser="";
          if ($ShowTitleAsTeaser) $teaser="";
          if (is_file($Filepath))
            {
            $size=@getimagesize($Filepath);
            if ($size)
              {
              $tn="<img border='0' alt='$Caption' $size[3] src='$Fileurl'><br/>";
              }
            }
          } else $tn="";
        $in=$this->_getgrouptext($aGroupID,$Control,$ColumnCount,$Level+1);

        
        switch($Level)
          {
          case 0: $r.="<tr><td colspan='2'><h6>$Caption</h6></td></tr>".$in;
            break;
          case 1:
            $draw=$this->PDrawText->GetDrawText(array(Text=>$Caption,DrawingFont=>0));
            if ($draw['Error']) return $draw;
            if ($Title==$Caption) $Title="";
            if ($in) $in="<table>$in</table>"; else $in="<a $Link_class href='../$TargetContext/$aGroupID.$cfg[VirtualExtension]'><img border='0' align='absmiddle'  src='$_THEME[SkinURL]/$ImageMore'></a>";
            $r.="<tr><td><a $Link_class href='../$TargetContext/$aGroupID.$cfg[VirtualExtension]'>$tn</a></td><td>$draw[Text]<br>$Title$in</td></tr>";
            break;
          default: $r.="<a $Link_class href='../$TargetContext/$aGroupID.$cfg[VirtualExtension]'>$Caption</a>".$in;
            break;
          }

        }
      }
    }
  return $r;
  }

}
?>
