<?
class img_IFormats
{
var $CopyrightText="(c)2003 PHP Systems Builder. Image storage cartridge";
var $CopyrightURL="www.phpsb.com/img";
var $ComponentVersion="1.0";

function Browse($args)
  {
  extract(param_extract(array(
    FormatID=>"string",
    ),$args));

  $_=&$GLOBALS['_STRINGS']['img'];
  global $cfg,$_SYSSKIN_NAME;

  $qfs=DBQuery("SELECT FormatID,Caption FROM img_Formats WHERE TnFormatNo=0","FormatID");

  $links1=array(
    Caption=>$_['IMG_IMAGEFORMATS'],
    );

  foreach ($qfs->Rows as $aFormatID=>$row)
    {
    if (!$FormatID) $FormatID=$aFormatID;
    $links1['Items'][]=array(
      Icon=>"$cfg[PublicURL]/sys/skins/$_SYSSKIN_NAME/tree_page.gif",
      Caption=>$row->Caption,
      Active=>($FormatID==$aFormatID),
      URL=>ActionURL("img.IFormats.Browse",array(FormatID=>$aFormatID)
      ));
    }

  $_ENV->PutRelatedLinks("links1",$links1);

  if ($FormatID)
    {
    $qf=DBQuery ("SELECT * FROM img_Formats WHERE FormatID=$FormatID ORDER BY TnFormatNo","TnFormatNo");
    if ($qf)
      {
      $FormatCaption=$qf->Rows['0']->Caption;
      print "<h2>$FormatCaption</h2>";
      }

    $_ENV->PutValueSet(array(ValueSetName=>"ResizeOptions",Values=>array(
      "none" =>$_['IMG_FORMAT_NONE'],
      "crop" =>$_['IMG_FORMAT_CROP'],
      "fit"  =>$_['IMG_FORMAT_FIT'],
      "color"=>$_['IMG_FORMAT_COLOR'],
      "img"  =>$_['IMG_FORMAT_IMG'],
      )));
    $_ENV->PutValueSet(array(ValueSetName=>"ImgFormats",NullCaption=>"-----",
      Values=>array(1=>"gif",2=>"jpeg",3=>"png")));
    $_ENV->OpenForm(array(
      Name=>"Form1",ShowCancel=>0,Action=>ActionURL("img.IFormats.UpdateFormat.b"),Align=>"center",ValCSS=>"td.bgdown"));
    print "<tr><td>";
    foreach ($qf->Rows as $aTnFormatNo=>$row)
      {
      if ($aTnFormatNo==0)
        {
        print "<h3>$_[IMG_FORMAT_TN_ORIGINAL]</h3><p>$_[IMG_FORMAT_RECOMMENDATION]</p>";
        }
      else
        {
        print "<h3>$_[IMG_FORMAT_TN_FORMATNO] $aTnFormatNo</h3>";
        }

      extract(param_extract(array(
        Width=>'int',
        Height=>'int',
        Caption=>'string',
        ResizeOption=>'string',
        BgColor=>'string',
        Quality=>'int=90',
        ImgType=>'string',
        Watermark=>'string',
        ),$row));

      $dw=$Width;
      $dh=$Height;
      if ($dw>200) {$dh=$dh/$dw*200; $dw=200;}
      if ($dh>200) {$dw=$dw/$dh*200; $dh=200;}
      $dummy="<img src='$cfg[PublicURL]/img/dummy_img.gif' width='$dw' height='$dh'/><br/>$Width x $Height";
      if ($aTnFormatNo!=0) $dummy.="<br/><a href='javascript:;' onClick='regenerate($FormatID,$aTnFormatNo)'>Regenerate thumbnails</a>";

      print "<table width='100%' border='0'><tr valign='top'><td rowspan='10' width='200'>$dummy</td></tr>";
      $_ENV->PutFormField(array(Type=>'langstring', Name=>"Caption[$aTnFormatNo]",Value=>$Caption,Caption=>$_['IMG_FORMAT_NAME'], Required=>1,MaxLength=>240,Size=>40));
      $_ENV->PutFormField(array(
          Type=>'droplist',
          Caption=>$_['IMG_FORMAT_RESIZEOPTION'],
          ValueSetName=>"ResizeOptions",
          Value=>$ResizeOption,
          Size=>40,
          Name=>"ResizeOption[$aTnFormatNo]"));
      print "
        <tr><th align='right'>$_[IMG_FORMAT_SIZE]:</th><td><input class='inputarea' type='text' value='$Width' size='8' maxlength='8'>x<input class='inputarea' type='text' value='$Height' size='8' maxlength='8'></td></tr>
        ";
      $_ENV->PutFormField(array(Type=>'string',Caption=>"$_[IMG_FORMAT_WATERMARKFILE]",Name=>"Watermark[$aTnFormatNo]",Value=>$Watermark));
      $_ENV->PutFormField(array(Type=>'string',Caption=>"$_[IMG_FORMAT_BGCOLOR]",Name=>"BgColor[$aTnFormatNo]",Value=>$BgColor));
      $_ENV->PutFormField(array(
          Type=>'droplist',
          Caption=>$_['IMG_FORMAT_CONVERTTO'],
          ValueSetName=>"ImgFormats",
          Value=>$ImgType,
          Size=>8,
          Name=>"ImgType[$aTnFormatNo]"));
      $_ENV->PutFormField(array(Type=>'int',Caption=>"JPEG Quality (10-100%)",Name=>"Quality[$aTnFormatNo]",Value=>$Quality));
      if ($aTnFormatNo==0)
        $_ENV->PutFormField(array(Type=>'int',Caption=>"Image file maximum size (kB)",Name=>"MaxFileSize",Value=>$MaxFileSize));
      print "</table><hr/>";
      }
    $_ENV->CloseForm();
    print "<input disabled class='button' type='submit' value='Save formats'>
    <input disabled class='button' type='submit' value='Add thumbnail format'>
    <script>
    var regenerateURL='".ActionURL("img.IImage.Regenerate.b")."';
    function regenerate(FormatID,TnFormatNo)
      {
      var w=W.openModal({url:regenerateURL,w:500,h:500,FormatID:FormatID,OnlyTnFormatNo:TnFormatNo,
      Title:'Regenerate FormatID:'+FormatID+',TnFormatNo:'+TnFormatNo});
      }
    </script>
    ";
    }
  }

function Select($args)
  {
  $_=&$GLOBALS['_STRINGS']['img'];

  extract(param_extract(array(
    FormatID=>"string",
    BindTo=>"string",
    ),$args));

  $_ENV->SetWindowOptions(array(Width=>400,Height=>400));
  $qfs=DBQuery("SELECT FormatID,Caption FROM img_Formats WHERE TnFormatNo=0","FormatID");
  print "<table><tr><td><h1>$_[CHOOSE_IMAGE_FORMAT]</h1><ul>";
  foreach ($qfs->Rows as $aFormatID=>$row)
    {
    $s=$row->Caption;
    if ($aFormatID!=$FormatID)
      {
      $s="<a href='javascript:;' onClick='W.modalResult($aFormatID)'>$s</a>";
      }
    print "<li>$s</li>";
    }
  print "</ul>";
  print "<hr>";
  $_ENV->PutButton('cancel');
  print "<br><p>$_[DONT_FORGET_TO_REGENERATE]</p>";
  }
}

?>

