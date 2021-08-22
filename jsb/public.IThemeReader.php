<?
class jsb_IThemeReader
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function GetSkinImageByValue($args)
  {
  $Value=$args['Value'];
  if (!$Value) return array(Caption=>"");
  global $cfg,$_THEME_NAME;

  $SkinURL ="$cfg[SkinsURL]/$_THEME_NAME";
  $SkinPath="$cfg[SkinsPath]/$_THEME_NAME";
  $imgfile=$SkinPath."/".$Value;
  if (file_exists($imgfile))
    {
    $size=@getimagesize($imgfile);
    if ($size)
      {
      $w=$size[0]; $h=$size[1]; $a=$w/$h;
      if ($a>1) {if ($w>100) {$h=100/$a; $w=100;}} else {if ($h>100) {$w=100*$a; $h=100;}}
      return array(Caption=>"<img src='$SkinURL/$Value' width='$w' height='$h'/>");
      }
      else return array(Caption=>"Not an image");
    } else return array(Caption=>"Image not found in skin '$_THEME_NAME'");
  }

function SelectSkinImage($args)
  {
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];
#  $_ENV->InitWindows();
  $_ENV->SetWindowOptions(array(Width=>750,Height=>500));

  global $cfg,$_THEME_NAME;
  $SkinURL ="$cfg[SkinsURL]/$_THEME_NAME";
  $SkinPath="$cfg[SkinsPath]/$_THEME_NAME";
  $this->_ImageExplorer($SkinPath,$SkinURL,"[Skin $_THEME_NAME]",'.gif|.jpg|.jpeg|.png');
  }

function _ImageExplorer($Path,$URL,$RootName='Main folder')
  {
  $s=$this->_CreateFoldersStructure($Path,$URL);
  $a=ActionURL("jsb.IThemeReader.ViewImgFolderContent",array(Path=>$Path,URL=>$URL));
  if ($s) $s="<a href='$a' target='view'>$RootName</a><br>$s";

  print "<script>
   var root='$URL';
   function openFolder(s) {view.location.href='$a&f='+s;}";
   ?>
  function selectImg(path,w,h)
    {
    W.modalResult(path+"\n"+"<img src='<? print $URL; ?>/"+path+"' width='"+w+"' height='"+h+"'>");
    }
  function imgClick(fpath,info,w,h)
    {
    var w2=w,h2=h,a=w/h;
    if (a>1) {if (w>100) {h2=100/a; w2=100;}} else {if (h>100) {w2=100*a; h2=100;}}
    window.status=fpath;
    document.getElementById('viewer').innerHTML="<img width='"+w+"' height='"+h+"' src='"+root+"/"+fpath+"'>"
     +"<br>"+info+"<br><br><input type='button' class='button' onClick='selectImg(\""+fpath+"\","+w2+","+h2+");' value='Select image'>" ;
    }
  </script>
  <?
  print "<table width='100%' cellpadding='0' height='100%' border=0>
  <tr valign='top'><td class='bgup' style='padding:10' width='200' >$s</td>
  <td rowspan='2'><iframe style='width:100%' height=490 name='view' id='view' src='$a'></iframe></td></tr>
  <tr><td style='padding:10 0 20 0' class='bgup' align='center' valign='bottom' style='font-size:9px;' id='viewer'></td></tr>
  </table>";
  }

function _CreateFoldersStructure($Path,$URL,$Folder="",$Level=1)
  {
  $result="";
  $dname=$Path; if ($Folder) $dname.=$Folder;
  $dh=opendir($dname);
  if (!$dh) {print "'$dname' is not a directory"; return "";}
  while (($file = readdir($dh)) !== false)
    {
    if (is_dir($dname.'/'.$file)&&($file!='.')&&($file!='..'))
      {
      $pad=""; for ($i=0;$i<$Level;$i++) $pad.="&nbsp;&nbsp;";
      $s=$this->_CreateFoldersStructure($Path,$URL,$Folder.'/'.$file,$Level+1);
      $ff=(($Folder)?"$Folder/":"").$file;
      $ff=urlencode($ff);
      $result.="\n$pad<a href='javascript:;' onClick='openFolder(\"$ff\");'>$file</a><br>$s";
      }
    }
  closedir($dh);
  return $result;
  }

function ViewImgFolderContent($args)
  {
  extract(param_extract(array(
    Path=>"string",
    URL=>"string",
    f=>'string',
    Filter=>'string=.gif|.jpg|.jpeg|.png',
    ),$args));

  global $cfg;

  if ($f) {$Path.='/'.$f; $URL.='/'.$f;}
  $dh=opendir($Path);
  if (!$dh) {print "'$dname' is not a directory"; return;}
  $r="";
  $colno=0;
  while (($file = readdir($dh)) !== false)
    {
    if (is_file($Path.'/'.$file)&&(preg_match("/$Filter/i",$file)))
      {
      $size=@getimagesize($Path.'/'.$file);
      if (!$size) continue;
      $w2=$w=$size[0]; $h2=$h=$size[1]; $a=$w/$h;
      if ($w>80) $w=80; $h=$w/$a;
      if ($h>80) $h=80; $w=$h*$a;
      if ($w2>200) $w2=200; $h2=$w2/$a;
      if ($h2>200) $h2=200; $w2=$h2*$a;

      $fn=(($f)?"$f/":"").$file;
      if (!$colno) $r.="<tr valign='bottom'>";
      $r.="<td width='90' align='center' bgcolor='#f0f0f0'
        onClick='window.parent.imgClick(\"$fn\",\"$size[0]x$size[1]<br>$file\",$w2,$h2)'
        onMouseOver='this.style.backgroundColor=\"#ffffff\"'
        onMouseOut='this.style.backgroundColor=\"#f0f0f0\"'><img width='$w' height='$h' src='$URL/$file'><br><span style='font-size:9px; color:#606060;'>$file</span></td>";
      $colno++;
      if ($colno>4) {$colno=0;$r.="</tr>";}
      }
    }
  closedir($dh);
  print "<table width='100%'><tr><td class='bgdown'><h3>$f</h3></td></tr></table>";
  if ($r) {if ($colno) $r.="</tr>";print "<table >$r</table>";}
  }
function SelectTableStyle($args)
  {
  $_ =&$GLOBALS['_STRINGS']['jsb'];
  $__=&$GLOBALS['_STRINGS']['_'];
#  $_ENV->InitWindows();
  $_ENV->SetWindowOptions(array(Title=>$_['CHOOSE_TABLE_STYLE'],Width=>750,Height=>500));

  global $cfg,$_THEME_NAME,$_THEME;
  $ThemeTableStyles=$_THEME['TableStyles'];

  if (!$ThemeTableStyles)
    {
    $result=$this->LoadActiveTheme(array());
    if ($result[Error]) {print_error($result[Error],$result[Details]); return false;}
    }

  $ThemeTableStyles=$GLOBALS['_THEME']['TableStyles'];
  if (!$ThemeTableStyles) {print "<h1>Error</h1><p>Theme.php file does not contains TableStyles section</p>"; return;}
    print "<title>Select table style</title>";
#    <script>
#    function ReturnResult(result){if (window.opener) {window.opener.OpenedDialog_Return(result); window.close();} else {alert ('?'); }}</script>";

  for ($i=0; $i<count($ThemeTableStyles);$i++)
    {
    $ts=$ThemeTableStyles[$i];
    print "<br>";
    print "\n\n#$i $ts[Caption]<br>\n";
    $s="";
    $maxcol=4; $maxrow=5;
    for ($r=0;$r<=$maxrow;$r++)
      {
      $s.="\n<tr>";
      for ($c=0;$c<=$maxcol;$c++)
        {
        $sample="Sample $col $row";

        if (!($r%2)) $tc=$ts['Even']; else $tc=$ts['Odd'];

        if (($c==0)&&($ts['Left'])) {$tc=$ts['Left'];}
        if (($c==$maxcol)&&($ts['Right'])) {$tc=$ts['Right'];}
        if (($c==$maxcol)&&(!($r%2))&&($ts['RightEven'])) {$tc=$ts['RightEven'];}
        if (($c==$maxcol)&&($r%2)&&($ts['RightOdd']))  {$tc=$ts['RightOdd'];}

        if (($c==0)&&(!($r%2))&&($ts['LeftEven'])) {$tc=$ts['LeftEven'];}
        if (($c==0)&&($r%2)&&($ts['LeftOdd']))  {$tc=$ts['LeftOdd'];}

        if (($r==0)&&($ts['Top'])) {$tc=$ts['Top'];}
        if (($r==$maxrow)&&($ts['Bottom'])) {$tc=$ts['Bottom'];}

        if (($c==0)&&($r==0)&&($ts['TopLeft'])) {$tc=$ts['TopLeft'];}
        if (($c==$maxcol)&&($r==0)&&($ts['TopRight'])) {$tc=$ts['TopRight'];}
        if (($c==0)&&($r==$maxrow)&&($ts['BottomLeft'])) {$tc=$ts['BottomLeft'];}
        if (($c==$maxcol)&&($r==$maxrow)&&($ts['TopRight'])) {$tc=$ts['BottomRight'];}

        list ($t,$_c,$class)=get_css_pair($tc,'td');
        $s.="<$t$_c>$t.$class $sample</$t>";
        }
      $s.="</tr>";
      }
    list ($t,$_c,$class)=get_css_pair($ts['Table'],'table');
    print "\n<$t$_c width='100%'>\n$s\n</$t>\n";
    print "<div style='text-align:right'>";
    $_ENV->PutButton(array(Kind=>'submit',OnClick=>"W.modalResult('$i\\n$ts[Caption]')"));
    print "</div>";
#    print "<input type='button' class='button' value='$__[CAPTION_OK]' onClick='ReturnResult($i)'><hr>";
    }
 }

function GetTableStyleByValue($args)
  {
  extract ($args);
  global $_THEME;
  if ($Value)
    {
    	if ($_THEME['Environment']!='f') {
    		$_ENV->LoadTheme('f');
    		$Caption=$_THEME['TableStyles'][$Value]['Caption'];
    		$_ENV->LoadTheme('b');
    	} else {
    		$Caption=$_THEME['TableStyles'][$Value]['Caption'];
    	}
    }
  return array(Caption=>$Caption);
  }
}


?>
