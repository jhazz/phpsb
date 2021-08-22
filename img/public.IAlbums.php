<?
class img_IAlbums
{
var $CopyrightText="(c)2007 PHPSB. Image storage cartridge";
var $CopyrightURL="www.jhazz.com/img";
var $ComponentVersion="1.0";

function tab_Folder(&$k,&$row)
  {
  print "<b>$row->BindToFolder</b>";
  }
function tab_Count (&$k,&$row)
  {
  if ($this->qcounts) {
    $r=$this->qcounts->Rows[$row->BindToFolder];
    if ($r->imgcount)
      {
      print "<a href='".ActionURL("img.IAlbums.BrowseImgIndex.bm",
        array(BindTo=>$row->BindToFolder))."'><b>$r->imgcount</b> image(s)</a>";
      }
    else
      {
      print "empty";
      }
    }
  }
function tab_Format (&$k,&$row)
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  if ($row->FormatID) $f=&$this->qf->Rows[$row->FormatID];
  $c=$_['CHANGE_IMAGE_FORMAT'];
  if ($f)
    {
    print "<b>".$f[0]->Caption."</b><br>";
    foreach ($f as $i=>$fv)
      {
      $wm=$fv->Watermark;
      if ($wm) {$wm=" '$wm'";}
      print $i.":[".$fv->Width.'x'.$fv->Height."$wm]<br>";
      }
    }

  $bf=$row->BindToFolder;
  if ($row->BindToID) {$bf.=$row->BindToID;}
  print "<a href='javascript:;' onClick='changeFormat(\"$bf\",$row->FormatID)'>$c</a>";
  }

function tab_OnGetCellStyle(&$k,&$row)
  {
  if (!$row->FormatID)
    {
    return 'Red';
    }
  }
function tab_Regenerate(&$k,&$row)
  {
  $_=&$GLOBALS[_STRINGS]['img'];
  if ($row->FormatID)
    {
    print "<a href='javascript:;' onClick='W.openModal({url:\"".ActionURL("img.IImage.Regenerate.b",
      array(BindTo=>$row->BindToFolder.$row->BindToID))."\"})'>$_[IMAGE_REGENERATE]</a>";
    }
  }

function _addFormatScript()
  {
  ?>
<script>
var _BindTo=false;
function changeFormat(BindTo,CurrentFormatID)
  {
  _BindTo=BindTo;
  W.openModal({url:'<? print ActionURL("img.IFormats.Select.b"); ?>',FormatID:CurrentFormatID,callback:"changeFormatCallback"});
  }
function changeFormatCallback (r)
  {
  W.openModal({url:'<? print ActionURL("img.IAlbums.ApplyFormatToAlbum.b"); ?>',w:400,h:400,BindTo:_BindTo,FormatID:r,reloadOnOk:1});
  }
</script>
  <?
  }

function Albums($args)
  {
  $_=&$GLOBALS['_STRINGS']['img'];

  $this->qf=DBQuery ("SELECT * FROM img_Formats",array(FormatID,TnFormatNo));
  $this->qic=DBQuery ("SELECT * FROM img_Albums WHERE BindToID=0 ORDER BY BindToID");
  $this->qcounts=DBQuery ("SELECT a.BindToFolder, COUNT(d.ImgID) AS imgcount FROM `img_Documents` d, img_Albums a
WHERE d.BindTo LIKE CONCAT(a.BindToFolder,'%') AND a.BindToID=0
GROUP BY a.BindToFolder","BindToFolder");
  $_ENV->PrintTable($this->qic,array(
    Width=>700,
    Fields=>array(BindToFolder=>"Folder abstract context",ImgCount=>"Images inside",Caption=>$_['IMAGEALBUM_DESCRIPTION'],Format=>$_['IMG_IMAGEFORMATS'],Regenerate=>""),
    FieldHooks=>array(BindToFolder=>tab_Folder, ImgCount=>tab_Count,Caption=>tab_WPLDescription,Format=>tab_Format, Regenerate=>tab_Regenerate),
    TableStyle=>1,
    ShowCheckers=>0,
    ShowDelete=>0,
    ThisObject=>&$this,
    ColAligns=>array(Regenerate=>'center'),
    OnGetCellStyle=>tab_OnGetCellStyle,
    ));
  $this->_addFormatScript();
  }


function ApplyFormatToAlbum ($args)
  {
  extract(param_extract(array(
    FormatID=>"string",
    BindTo=>"string",
    ),$args));

  $_=&$GLOBALS['_STRINGS']['img'];

  $info=BindPathInfo($BindTo);
  $BindToID=intval($info->ID);

  $q=DBQuery ("SELECT * FROM img_Albums WHERE BindToFolder='$info->Folder'");
  if ($q)
    {
    $s="UPDATE img_Albums SET FormatID=$FormatID WHERE BindToFolder='$info->Folder' AND BindToID='$BindToID'";
    }
  else
    {
    $s="INSERT INTO img_Albums (FormatID,BindToFolder,BindToID) VALUES ($FormatID,'$info->Folder',$BindToID)";
    }
  $h=ActionURL("img.IAlbums.Contexts.bm");
  if (DBExec ($s))
    {
    print "<script>W.modalResult(true);</script>";
    } else  print "<br><a href='$h'>Continue</a>";
  }


function tab_WPLView(&$k,&$row)
  {
  $_=&$GLOBALS[_STRINGS]['img'];
  $BindTo=$row->BindToFolder.$row->BindToID;
  print "<b>$row->BindToFolder</b>";
  if ($this->qcounts) {
    $r=$this->qcounts->Rows[$BindTo];
    }
  $s=$_['EMPTY_ALBUM'];
  if ($r->imgcount) $s=" <b>$r->imgcount $_[IMAGES_INSIDE]</b>";
  print "<br><a href='".ActionURL("img.IAlbums.BrowseImgIndex.bm",array(BindTo=>$row->BindToFolder.$row->BindToID))."'>$s</a>";
  }

function tab_WPLDescription (&$k,&$row)
  {
  print "<a href='javascript:;' onClick='W.openModal({url:\""
    .ActionURL("img.IAlbums.EditAlbumDescription.b"
    ,array(BindTo=>$row->BindToFolder.$row->BindToID))
    ."\",reloadOnOk:1})'>$row->Caption</a>";
  }

function Update($args)
  {
  extract(param_extract(array(
    UserID=>'int',
    subaction=>'string',
    action=>'string',
    check=>'int_array',
    BindTo=>'string',
    ),$args));
  if ($action=='delete')
    {
    $albums="";
    foreach ($check as $BindTo=>$x)
      {
      $BindToDetails=BindPathInfo($BindTo);
      if (!$BindToDetails)
        {
        return array(Error=>"[[BAD_BINDTO_PATH]]",$BindTo);
        }
      $albums.=(($albums)?",":"")."'$BindTo'";
      }
    if (!$albums)
      {
      return array(ModalResult=>false);
      }

    $s="SELECT ImgID FROM img_Documents WHERE BindTo IN ($albums)";
    $q=DBQuery ($s,"ImgID");
    if ($q)
      {
      $IImage=&$_ENV->LoadInterface("img.IImage");
      if (!$IImage) return;
      $IImage->Delete (array(img_selected=>$q->Rows));
      }
    $s="DELETE FROM img_Albums WHERE CONCAT(BindToFolder,BindToID) IN ($albums)";
    DBExec ($s);
    return array(ModalResult=>true);
    }
  }

function GlobalAlbums($args)
  {
  extract(param_extract(array(
    FormatID=>"string",
    ),$args));
  $_=&$GLOBALS['_STRINGS']['img'];

  $this->qf=DBQuery ("SELECT * FROM img_Formats",array(FormatID,TnFormatNo));
  $this->qic=DBQuery ("SELECT CONCAT(BindToFolder,BindToID) AS BindTo,BindToFolder,BindToID,Caption,FormatID FROM img_Albums WHERE BindToFolder='img.Album/' ORDER BY BindToID","BindTo");
  $this->qcounts=DBQuery ("SELECT BindTo,COUNT(*) as imgcount FROM img_Documents
WHERE BindTo LIKE 'img.Album/%'
GROUP BY BindTo","BindTo");

  $_ENV->PrintTable($this->qic,array(
    ModalWindowURL=>"img.IAlbums.Update.b",
    HiddenFields=>array(BindTo=>'img.Album/',),
    Width=>650,
    Fields=>array(WPLView=>$_['IMAGEALBUM_OPEN'],WPLDescription=>$_['IMAGEALBUM_DESCRIPTION'],Format=>$_['IMG_IMAGEFORMATS'],Regenerate=>""),
    FieldHooks=>array(WPLView=>tab_WPLView,WPLDescription=>tab_WPLDescription, Format=>tab_Format, Regenerate=>tab_Regenerate),
    BindToFolder=>tab_Folder,
    TableStyle=>1,
    ShowCheckers=>1,
    ColAligns=>array(Regenerate=>'center'),
    ShowDelete=>true,
    OnGetCellStyle=>tab_OnGetCellStyle,
    ThisObject=>&$this
    ));
  $_ENV->PutButton(array(Caption=>$_['IMAGEALBUM_ADDNEW'],
    Kind=>'add',OnClick=>"W.openModal({url:'".ActionURL("img.IAlbums.EditAlbumDescription.b")."',reloadOnOk:1})"));
  print "</td></tr></table>";
  $this->_addFormatScript();
  }

function EditAlbumDescription($args)
  {
  $_=&$GLOBALS['_STRINGS']['img'];
  extract(param_extract(array(
    BindTo=>"string=img.Album/",
    ),$args));

  $BindToInfo=BindPathInfo($BindTo);
  $BindToID=intval($BindToInfo->ID);
  $criteria="BindToFolder='$BindToInfo->Folder' AND BindToID=$BindToID";
  $qa=DBQuery ("SELECT * FROM img_Albums WHERE $criteria");
  $qf=DBQuery ("SELECT * FROM img_Formats WHERE TnFormatNo=0",'FormatID');
  if ($qa) {
    $Caption=$AlbumName=$qa->Top->Caption;
    $FormatID=$qa->Top->FormatID;
    $CanHasURL=$qa->Top->CanHasURL;
    } else $CanHasURL=1;

  if ($qf)
    {
    foreach ($qf->Rows as $aFormatID=>$row)
      {
      $select.="<option value='$aFormatID' ".(($aFormatID==$FormatID)?"selected":"").">$row->Caption</option>";
      }
    $select="<select class='inputarea' name='NewFormatID'>$select</select>";
    }


  if (!$AlbumName)
    {
    if ($BindToInfo->Folder=="img.Album/") $AlbumName=$_['IMAGEALBUM_ADDNEW'];
    else $AlbumName=$_['IMAGEALBUM_ADDNEW'];
    }

  $_ENV->SetWindowOptions(array(Title=>" ",Width=>400,Height=>200));
  print "<h1>$AlbumName</h1><center><form method='post' action='".ActionURL("img.IAlbums.UpdateAlbumDescription.b")."'>
    <input type='hidden' name='BindTo' value='$BindToInfo->Folder$BindToInfo->ID'>
    <table>
    <tr valign='top'><td align='right'>$_[IMAGEALBUM_NAME]:</td>
    <td><input class='inputarea' type='text' size='40' name='NewCaption' value='$Caption'></td></tr>
    <tr valign='top'><td align='right'>Image format:</td><td>$select</td></tr>
    <tr valign='top'><td align='right'>Has URL:</td><td><input type='checkbox' name='CanHasURL' value='1' ".(($CanHasURL)?'checked':'')."></td></tr>
    <tr><td></td><td><br><br>".$_ENV->PutButton(array(ToString=>1,Action=>'submit')).$_ENV->PutButton(array(ToString=>1,Action=>'cancel'))."</td></tr>
    </table>";
  }

function UpdateAlbumDescription($args)
  {
  extract(param_extract(array(
    BindTo=>"string",
    NewCaption=>"string",
    NewFormatID=>"int",
    CanHasURL=>'int',
    ),$args));

  $BindToInfo=BindPathInfo($BindTo);
  $BindToID=intval($BindToInfo->ID);
  if (($BindToInfo->Folder=='img.Album/')&&(!$BindToID)) $BindToID=DBGetID("img.Album","layouts");

  if ($BindToID!=0)
    {
    # only 'img.Album/' can use non zero id
    if ($BindToInfo->Folder=='img.Album/')
      {
      $q=DBQuery ("SELECT * FROM img_Albums WHERE BindToFolder='$BindToInfo->Folder' AND BindToID=$BindToID");
      if ($q)
        {
        DBExec ("UPDATE img_Albums SET FormatID=$NewFormatID, Caption='$NewCaption',CanHasURL=$CanHasURL
          WHERE BindToFolder='$BindToInfo->Folder' AND BindToID=$BindToID");
        }
      else
        {
        DBExec ("INSERT INTO img_Albums (BindToFolder,BindToID,FormatID,Caption,CanHasURL)
        VALUES('$BindToInfo->Folder',$BindToID,$NewFormatID,'$NewCaption',$CanHasURL) ");
        }
      }
    }
  else
    {
    $q=DBQuery ("SELECT * FROM img_Albums WHERE BindToFolder='$BindToInfo->Folder' AND BindToID=0");
    if ($q)
      {
      DBExec ("UPDATE img_Albums SET FormatID=$NewFormatID, Caption='$NewCaption', CanHasURL=$CanHasURL
        WHERE BindToFolder='$BindToInfo->Folder' AND BindToID=0");
      }
    else
      {
      DBExec ("INSERT INTO img_Albums (BindToFolder,BindToID,FormatID,Caption,CanHasURL)
      VALUES('$BindToInfo->Folder',0,$NewFormatID,'$NewCaption',$CanHasURL) ");
      }
    }

  return array(ModalResult=>true);
  }

function BrowseImgIndex($args)
  {
  extract(param_extract(array(
    BindTo=>"string",
    ),$args));
  print "<h1>Browsing '$BindTo'</h1>";
  $info=BindPathInfo($BindTo);
  $ID=intval($info->ID);
  $Insertable=1;

  if (!$ID) {$Insertable=0; print "You can add images only in object related albums. This is an abstract album that default for all albums bound to selected class and context";}

  $IImgIndex=&$_ENV->LoadInterface ("img.IImgIndex");
  $IImgIndex->View(array(
    BindTo=>$BindTo,
    TnFormatNo=>1,
    ShowCaptions=>1,
    EditMode=>1,
    Insertable=>$Insertable,
    CSS_Cell=>".imgindex",
    CSS_Background=>".bgdowndown",
    ),true); # Internal call
  $this->_addFormatScript();
  }

function GetAlbumbsList($args)
  {
  extract(param_extract(array(
    ControlID=>"int",
    PageControls=>"array",
    ),$args));

  $this->qic=DBQuery ("SELECT * FROM img_Albums WHERE BindToFolder='img.Album/' ORDER BY BindToID","BindToID");
  if (!$this->qic) return;
  foreach ($this->qic->Rows as $BindToID=>$row)
    {
    $result[$BindToID]=$row->Caption;
    }
  return array(ListValues=>$result);
  }

}

?>

