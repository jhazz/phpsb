<?
class stdctrls_IRichtext
{
function LoadXML ($args) {
  global $cfg;
  extract(param_extract(array(
    BindTo=>'string',
    ),$args));

  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo) return array(Error=>'[BAD_BINDTO_PATH]',Details=>$BindTo);
  $d0=$cfg['FilesPath']."/stdctrls.RichtextImg/$BindToInfo->Folder";

  $result="";
  $qt=DBQuery ("SELECT DocID,Title,FileName FROM stdctrls_Richtexts WHERE BindTo='$BindTo' ORDER BY OrderNo","DocID");
  if (!$qt) {return array(XML=>"<page/>");}

  $PageNo=1;
  foreach ($qt->Rows as $DocID=>$doc)
    {
    $page="";
    $filename=$d0.$doc->FileName;
    if (!is_file($filename))
      {return array(XML=>"<page><p>Error. File has been removed '$filename'</p></page>");}
    $page=implode ("\n",file($filename));
    $page=str_replace("../storeproduct/","asfunction:_root._openproduct,",$page);
    $f=create_function('$matches',"if (\$matches[2]=='IMG') {if (strpos(\$matches[3],'.png')!==false) { return '';} } return \$matches[0];");
    $page=preg_replace_callback("|(<\/?)(\w+)([^>]*>)|",$f,$page);
    if ($doc->Title) {$Title=$doc->Title ;} else {$Title=$PageNo; $PageNo++; }
    $result.="\n<page title='$Title' id='$DocID'>$page</page>";
    }
  return array(XML=>$result);
  }

function Remove_Page ($args)
  {
  extract(param_extract(array(
    BindTo=>'string',
    DocID=>'int',
    ),$args));
  $q=DBQuery ("SELECT DocID,BindTo FROM stdctrls_Richtexts WHERE DocID=$DocID AND BindTo='$BindTo'","DocID");
  if ($q)
    {
    $r=$this->RemoveDocumentsByQueryResult($q);
    }
  if ($r['Error']) return $r;
  return array(ModalResult=>true);
  }

function Remove_BoundToObject($args)
  {
  extract(param_extract(array(
    BindTo=>'string',
    ),$args));

  $q=DBQuery ("SELECT DocID,BindTo FROM stdctrls_Richtexts WHERE BindTo='$BindTo'","DocID");
  if ($q)
    {
    $r=$this->RemoveDocumentsByQueryResult($q);
    }
  if ($r['Error']) return $r;
  return array(ModalResult=>true);
  }

function Remove_BoundToControl($args)
  {
  extract(param_extract(array(
    TargetControlID=>'int', # The PageControl that richtext was bound to
    ),$args));
  $q=DBQuery ("SELECT DocID,BindTo FROM stdctrls_Richtexts WHERE BindTo LIKE 'jsb/%!$TargetControlID'","DocID");
  if ($q)
    {
    $this->RemoveDocumentsByQueryResult($q);
    }
  return array(ModalResult=>true);
  }

function RemoveDocumentsByQueryResult (&$q)
  {
  global $cfg;
  $doclist=implode (",",array_keys($q->Rows));
  trace ("Removing richtext document: $doclist");
  foreach ($q->Rows as $DocID=>$row)
    {
    $BindToInfo=BindPathInfo($row->BindTo);
    if (!$BindToInfo)
      {
      return array(Error=>"[[BAD_BINDTO_PATH]]",Details=>$row->BindTo);
      }
    }
  DBExec ("DELETE FROM stdctrls_Richtexts WHERE DocID IN ($doclist)");
  }

function Save($args)
  {
  global $cfg;
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  extract(param_extract(array(
    Title=>'string',
    BindTo=>'string',
    TextContent=>'string',
    DocID=>'int',
    ),$args));

  if (!$BindTo)
    {
    return array(Error=>$_[RT_BAD_BINDING]);
    }

  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo) return array(Error=>'[BAD_BINDTO_PATH]',Details=>$BindTo);

  $doc=false;
  $q=DBQuery ("SELECT * FROM stdctrls_Richtexts WHERE BindTo='$BindTo'","DocID");
  if ($q)
    {
    if ($DocID)
      {
      if ($DocID!=-1) {$doc=$q->Rows[$DocID];}
      }
    else {
      $doc=$q->Top;
      $DocID=$doc->DocID;
      }
    }

  if ($doc)
    {
    $TargetFile=$doc->FileName;

    # Update resized images
    $rimages=$_POST['ResizedImgs'];
    if ($rimages)
      {
      $img_interface=&$_ENV->LoadInterface("stdctrls.IRichtextImg");
      $rimages=explode ("|",$rimages);
      $imgids=false;
      foreach ($rimages as $i=>$s)
        {
        list($ImgID,$w,$h)=explode (";",$s);
        $ImgID=intval($ImgID);
        $img_interface->Resize (array(ImgID=>$ImgID,Tn_w=>$w,Tn_h=>$h));
        }
      }
    DBExec ("UPDATE stdctrls_Richtexts SET Title='$Title',Content='$TextContent' WHERE DocID=$DocID");
    }
  else
    {
    $DocID=DBGetID('stdctrls.Richtext');
    $qm=DBQuery ("SELECT MAX(OrderNo) AS MaxOrderNo FROM stdctrls_Richtexts WHERE BindTo='$BindTo'");
    $OrderNo=($qm)?intval($qm->Top->MaxOrderNo)+1:1;
    DBExec ("INSERT INTO stdctrls_Richtexts (DocID,BindTo,Title,OrderNo,Content) VALUES
        ($DocID,'$BindTo','$Title',$OrderNo,'$TextContent')");
    }

  return array(ModalResult=>$DocID);
  }


function Add($args)
  {
  $args['DocID']=-1;
  $this->Edit($args);
  }

function Edit($args)
  {
  print "<form name='RichTextEditForm' method='post' ENCTYPE='multipart/form-data' action='"
   .ActionURL('stdctrls.IRichtext.Save.f')."'>";
  $this->RenderEditor($args);
  print "</form>";
  }


function tab_OrderNo(&$id,&$row,$fname)
  {
  print "<input type='text' name='orderno[$id]' value='$row->OrderNo' maxlength=4' size='4' class='inputarea'>";
  }

function tab_PageTitle(&$id,&$row,$fname,&$args)
  {
  $s=$row->$fname;
  if (!$s) {$s="[ --- ]";}
  if ($this->SelectedDocID!=$id) $s="<a href='".ActionURL("stdctrls.IRichtext.View.f",array(DocID=>$id,BindTo=>$args['BindTo'],EditMode=>$args['EditMode']))."'>$s</a>";
  print $s;
  }

function SetNewOrder ($args)
  {
  extract(param_extract(array(
    BindTo=>'string',
    DocID=>'int',
    NewOrder=>'int',
    IsToRight=>'int'
    ),$args));

  $q1=DBQuery ("SELECT OrderNo FROM stdctrls_Richtexts WHERE BindTo='$BindTo' AND DocID=$DocID");
  $q2=DBQuery ("SELECT DocID FROM stdctrls_Richtexts WHERE BindTo='$BindTo' AND OrderNo=$NewOrder");
  if (($q1)&&($q2))
    {
    DBExec ("UPDATE stdctrls_Richtexts SET OrderNo=$NewOrder WHERE DocID=$DocID");
    DBExec ("UPDATE stdctrls_Richtexts SET OrderNo=".$q1->Top->OrderNo." WHERE DocID=".$q2->Top->DocID);
    if ($IsToRight) $DocID=$q2->Top->DocID;
    }
  return (array(ModalResult=>$DocID));
  }

function View($args)
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  $__=&$GLOBALS['_STRINGS']['_'];
  global $cfg,$_THEME;
  extract(param_extract(array(
#    Title=>'string',
    BindTo=>'string',
    DocID=>'int',
    EditMode=>'int',
    CreateFirstPage=>'int'
    ),$args));

  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo) return array(Error=>'[BAD_BINDTO_PATH]',Details=>$BindTo);
  $qpages=DBQuery ("SELECT * FROM stdctrls_Richtexts WHERE BindTo='$BindTo' ORDER BY OrderNo","DocID");
  if ($qpages)
    {if ($DocID) {$doc=$qpages->Rows[$DocID]; if (!$doc) $DocID=0;}
     if (!$DocID) {$doc=$qpages->Top; $DocID=$doc->DocID;}
    }
  if ($EditMode)
    {
    if (($CreateFirstPage)&&(!$qpages))
      {
      return $this->Edit($args);
      }
    # DocID=>$adocid,
    $u=ActionURL("stdctrls.IRichtext.View.f",array(BindTo=>$args['BindTo'],
      EditMode=>$args['EditMode']));

    print "<script>function onUpdate(DocID){
      if (!DocID) location.reload(); else {
        location.href='$u&DocID='+DocID; }
      }</script>";

    $imgurl=$_THEME['SkinURL'].'/richtext';
    print "<style>
      .rtp_bg {background-color:#808080; background-image: url($imgurl/rtp_bg.gif);}
      .rtp_atab {font-family:Verdana, Arial,sans; font-size:10px; font-weight:bold;color:#000000; background-image: url($imgurl/rtp_atab_bg.gif); }
      .rtp_tab  {font-family:Arial,sans; font-size:10px; background-image: url($imgurl/rtp_tab_bg.gif); }
      .rtp_href {color: #222222; text-decoration:none;}
      .rtp_href:hover {color: #800000; text-decoration:underline;}
      .rtp_btn  {font-family:Verdana, Arial,sans; font-size:10px; background-image: url($imgurl/rtp_btn_bg.gif); }
      .rtp_btn_href {font-family:Verdana, Arial,sans; font-weight:bold; font-size:10px; color: #FFFFFF; text-decoration:none;}
      .rtp_btn_href:hover {color: #ffe0e0;}
    </style>";
#    $_ENV->InitWindows();

    $MaxOrderNo=1;
    $NewPageTitle=$_[RT_PAGE_DEFAULT_TITLE].$MaxOrderNo;
    $s="";
    if ($qpages) {$MaxOrderNo=$qpages->Top->OrderNo+1;}
    if (($qpages)&&($qpages->RowCount>1))
      {
      $MaxOrderNo=0;
      $was=0; # 0-nothing  1-tab  2-atab
      $PrevOrderNo=-1;
      foreach ($qpages->Rows as $adocid=>$row)
        {
        if ($row->OrderNo>$MaxOrderNo) $MaxOrderNo=$row->OrderNo;
        $u=ActionURL("stdctrls.IRichtext.View.f",array(DocID=>$adocid,BindTo=>$args['BindTo'],EditMode=>$args['EditMode']));
        $t=($row->Title)?$row->Title: '???';
        if ($adocid==$DocID)
          {
          if (strlen ($t)>22) {$t=substr($t,0,20)."..";}
          if ($was==1) $s.="<td><img src='$imgurl/rtp_tab_right.gif'></td>";
          $left="<img border='0' src='$imgurl/rtp_atab_left.gif'>";
          if ($PrevOrderNo!=-1) {
            $left="<a title='$_[RT_SHIFTPAGELEFT]'
            href='javascript:;'
            onClick='W.openModal({url:\"".ActionURL("stdctrls.IRichtext.SetNewOrder.b",
            array(BindTo=>$BindTo,NewOrder=>$PrevOrderNo,DocID=>$adocid))
            ."\", callback:\"onUpdate\",DocID:$adocid, Title:\"$_[RT_SHIFTPAGELEFT]\"})'>$left</a>";}
          $s.="<td>$left</td><td nowrap class='rtp_atab'>&nbsp;$t&nbsp;</td>";
          $was=2;
          }
        else
          {
          if (strlen ($t)>12) {$t=substr($t,0,10)."..";}
          if ($was==2)
            {
            $s.="<td><a title='$_[RT_SHIFTPAGERIGHT]' href='javascript:;'
            onClick='W.openModal({url:\"".ActionURL("stdctrls.IRichtext.SetNewOrder.b",
             array(BindTo=>$BindTo,IsToRight=>1,NewOrder=>$PrevOrderNo,DocID=>$adocid))
             ."\",callback:\"onUpdate\",Title:\"$_[RT_SHIFTPAGERIGHT]\"})'><img border='0' src='$imgurl/rtp_atab_right.gif'></a></td>";
            $was=0;
            }
          if ($was==1) $s.="<td><img src='$imgurl/rtp_tabtab_sep.gif'></td>";
          if ($was==0) $s.="<td><img src='$imgurl/rtp_tab_left.gif'></td>";
          $s.="<td class='rtp_tab' valign='bottom' nowrap><a href='$u' class='rtp_href'>$t</a></td>";
          $was=1;
          }
        $PrevOrderNo=$row->OrderNo;
        }
      $MaxOrderNo++;
      }

    if ($qpages)
      {
      if ($was==2) $s.="<td><img src='$imgurl/rtp_atab_right.gif'></td>";
      if ($was==1) $s.="<td><img src='$imgurl/rtp_tabtab_sep.gif'></td>";
      if ($was==0) $s.="<td><img src='$imgurl/rtp_tab_left.gif'></td>";
      $s.="<td class='rtp_tab' valign='bottom' nowrap><a href='javascript:;'
      onClick='P$.find(\"addlayer\").style.display=\"block\"' class='rtp_href'> [+] $_[RT_ADD_PAGE] </a></td>
      <td><img src='$imgurl/rtp_tab_right.gif'></td>";
      }

    $NewPageTitle=$_[RT_PAGE_DEFAULT_TITLE].$MaxOrderNo;
    $s.="</tr></table></td><td align='right' class='rtp_bg'><table cellpadding='0' cellspacing='0'><tr>
      <td><img src='$imgurl/rtp_btn_left.gif'></td>
      <td class='rtp_btn' nowrap><a class='rtp_btn_href' href='javascript:;' onClick='W.openModal({url:\""
      .ActionURL("stdctrls.IRichtext.Edit.f",array(DocID=>$DocID,BindTo=>$BindTo))
      ."\",w:700,h:500,Title:\"$_[RT_EDITCONTENT]\",reloadOnOk:1});'>$_[RT_EDITCONTENT]</a></td>
      <td><img src='$imgurl/rtp_btn_right.gif'></td>";

    if ($qpages)
      {$s.="<td><img src='$imgurl/rtp_btn_left.gif'></td>
      <td class='rtp_btn' nowrap><a class='rtp_btn_href' href='javascript:;' onClick='W.openModal({url:\""
      .ActionURL("stdctrls.IRichtext.Remove_Page.b",array(DocID=>$DocID,BindTo=>$BindTo))
      ."\",w:400,h:100,Title:\"$__[CAPTION_DELETE]\",callback:\"onUpdate\"});'>$__[CAPTION_DELETE]</a></td>
      <td><img src='$imgurl/rtp_btn_right.gif'></td>";
      }

    $s.="</tr></table>";
    print "<table width='100%' cellpadding='0' cellspacing='0' border='0'><tr valign='bottom'><td class='rtp_bg'><table border='0' cellspacing='0' cellpadding='0'><tr>$s</tr></table></td></tr></table>";

    $hide=" style='display:none'";
    print "<div id='addlayer' $hide><table width='100%' border='0'>
      <form name='addnewdesc' method='post' action='".ActionURL("stdctrls.IRichtext.Add.f")."'
        onSubmit='this.target=W.openModal({w:700,h:500,Title:\"$NewPageTitle\",callback:\"onUpdate\"});'><tr><td>
      $_[RT_NEW_PAGE_TITLE]<br>
      <input type='text' class='inputarea' name='NewPageTitle' value='$NewPageTitle'>
      <input type='hidden' name='BindTo' value='$BindTo'>
      <input class='button' type='submit' value='$__[CAPTION_ADD]'>
      </td></tr></form></table></div>";
    }

  if ($qpages)
    {
    print $doc->Content;
    }
  } # end of Render

function RenderEditor($args)
  {
  extract(param_extract(array(
    NewPageTitle=>'string',
    BindTo=>'string',
    DocID=>'int',
    ),$args));

  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo) return array(Error=>'[BAD_BINDTO_PATH]',Details=>$BindTo);

  print "<body leftmargin='0' topmargin='0'>";
  $__=&$GLOBALS[_STRINGS][_];
  $_=&$GLOBALS[_STRINGS][stdctrls];
  global $cfg,$_THEME_NAME;

  print "<title>$this->About</title>";
  if (strpos($_SERVER['HTTP_USER_AGENT'],'MSIE')===false)
    {
    return array(Error=>"RichTextEditor. Only Internet Explorer supported");
    }

  if ($DocID!=-1)
    {
    $s="SELECT * FROM stdctrls_Richtexts WHERE BindTo='$BindTo'";
    if ($DocID) {$s.=" AND DocID=$DocID";}
    $q=DBQuery ($s);
    if ($q)
      {
      $doc=$q->Top;
      $Title=$doc->Title;
      $DocID=$doc->DocID;
      }
    }
  else
    {
    $Title=$NewPageTitle;
    }

  global $_THEME;

  $JSB_ThemeTableStyles=$_THEME['TableStyles'];
  $i=0;
  $StyleID=0;
  $TableAutoformats="tableAutoFormatStyles=new Array();";

  $possibleStyles="Table,TopLeft,TopRight,Top,Left,Even,Odd,Bottom,Right,BottomLeft,BottomRight,LeftEven,LeftOdd,RightEven,RightOdd";
  $ss=explode (',',$possibleStyles);
  foreach ($JSB_ThemeTableStyles as $style)
    {
    $i++; $StyleID++;
    $s="";
    foreach ($ss as $st)
      {
      eval ("list(\$$st,\$$st"."_C,\$cls)=get_css_pair(\$style['$st'],false);");
      eval ("\$css=\$$st"."_C;");
      if (($st=='Table') && (!$$st)) {$$st='table';}
      if (($st=='Even') && (!$$st)) {$$st='td';}
      if (($st=='Odd') && (!$$st)) {$$st='td';}
      if ($$st)
        {
        if ($s) {$s.=',';}
        $s.="$st:{"."tag:\"".$$st."\",cls:\"$cls\"}";
        }
      }
    $TableAutoformats.="\ntableAutoFormatStyles[$StyleID]={"."$s};";
    }

  $JSB_CSS_Styles=$_THEME['Styles'];
  $TextFormats="";
  foreach ($JSB_CSS_Styles as $className=>$Description)
    {
    if ($TextFormats) {$TextFormats.=",";}
    list ($tag,$subclass)=explode (".",$className);
    $tag=strtoupper($tag);
    $className=$tag.(($subclass)?".$subclass":"");
    $TextFormats.="'$className':'$Description'";
    }
  if ($TextFormats) {$TextFormats="var TextFormats={"."$TextFormats};";}
  $PubStdctrls=$cfg['PublicURL'].'/stdctrls';
  $RichtextSkinURL=$_THEME['SkinURL'].'/richtext';
?>
<style>
body {background-color:#808080;}
td  {font-family:Verdana,Arial; font-size:10px;}
.inputarea {font-family:Verdana,Arial; font-size:10px;}
.paneltext {font-family:Verdana,Arial; font-size:10px; color:#000000;}
</style>

<?
print "<script src='$PubStdctrls/js_richtext_editor_pframe.js'></script>
<script>
var imgurl='$RichtextSkinURL';
var RT_SELECT_TABLEAUTOFORMAT='$_[RT_SELECT_TABLEAUTOFORMAT]';
var RT_CLEAR_TEXT_STYLE='$_[RT_CLEAR_TEXT_STYLE]';
var RT_UNFORMATTED='$_[RT_UNFORMATTED]';
var MSG_PLEASE_WAIT='$__[MSG_PLEASE_WAIT]';
var URL_SelectPage='".ActionURL('jsb.IPage.SelectPageOrURL.b',array(CanSelectContext=>1,CanSelectURL=>1))."';
var URL_InsertImage='".ActionURL('stdctrls.IRichtextImg.Edit.f',array(BindTo=>$BindTo))."';
$TableAutoformats
$TextFormats
var _ACTIONS={
copy:'$_[RT_COPY]',
cut:'$_[RT_CUT]',
paste:'$_[RT_PASTE]',
insertrow:'$_[RT_INSERTROW]',
insertcol:'$_[RT_INSERTCOL]',
insertrowbelow:'$_[RT_INSERTROWBELOW]',
insertcolright:'$_[RT_INSERTCOLRIGHT]',
insertunorderedlist:'$_[RT_INSERTUNORDEREDLIST]',
insertorderedlist: '$_[RT_INSERTORDEREDLIST]',
deleterow:'$_[RT_DELETEROW]',
deletecol:'$_[RT_DELETECOL]',
tabstyle:'$_[RT_TABLESTYLE]',
link:'$_[RT_LINK]',
unlink:'$_[RT_UNLINK]',
image:'$_[RT_IMAGE]',
table:'$_[RT_TABLE]',
undo:'$_[RT_UNDO]',
redo:'$_[RT_REDO]',
bold:'$_[RT_BOLD]',
italic:'$_[RT_ITALIC]',
img_align_left:'$_[RT_IMG_ALIGN_LEFT]',
img_align_right:'$_[RT_IMG_ALIGN_RIGHT]',
img_align_inline:'$_[RT_IMG_ALIGN_INLINE]',
img_resetsize:'$_[RT_IMG_RESETSIZE]'
};
";
?>
</script>

<div id='DIVpopup' style='position:absolute;'></div>
<div id='DIVParagraphStyleSelector' style='visibility:hidden;position:absolute;'></div>
<div id='DIVAnchorStyleSelector' style='visibility:hidden;position:absolute;'></div>

<div>
<table width='100%' border='0' cellpadding='0' cellspacing='1'>
<tr><td colspan='2' height='22'>

<table border='0' cellpadding='2' cellspacing='0'><tr><td bgcolor='#c0c0c0'>
<select style='width:200px' multiple size='1' name='TextStyleDropBox' class='inputarea' onClick='EditFrame.eframe_StoreCursorPos(); pframe_ShowTextStyleList()'>
<script>document.write(pframe_EnumTextStyleForDropBox(para_tags));  </script>
</select>
</td>
<script>
document.write (pframe_toolbar2html(toolbar)+"<td width='100%' bgcolor='#c0c0c0'>&nbsp;</td>");
</script>
<td align='right'><a href='#' onClick='EditFrame.eframe_viewSource();'>View&nbsp;source</a></td>
</tr></table>

</td></tr>
<tr bgcolor='#C0C0C0' height='50' valign='top'><td>

<div id='DIVimg_edit_panel' style='display:none'>
<table cellpadding='0' cellspacing='0' border='0'><tr><td bgcolor='#C0C0C0' class='paneltext'>
<table border='0' cellpadding='1' cellspacing='0'><tr>
<td>W</td><td><input class='inputarea' onBlur='pframe_UpdateImgProperties()' type='text' name='img_edit_width' size='3' maxlength='4' value=''></td>
<td></td>
<script>document.write(pframe_toolbar2html('img_resetsize,|,img_align_left,img_align_right,img_align_inline'));</script>
<td></td><td></td><td>border:</td><td colspan='3'>
<input class='inputarea' onBlur='pframe_UpdateImgProperties()' type='text' id='img_edit_hspace' size='2' maxlength='2' value=''> x
<input class='inputarea' onBlur='pframe_UpdateImgProperties()' type='text' name='img_edit_vspace' size='2' maxlength='2' value=''>
</td></tr>
<tr>
<td>H</td><td><input class='inputarea' onBlur='pframe_UpdateImgProperties()' type='text' name='img_edit_height' size='3' maxlength='4' value=''></td>
<td>&nbsp; link:</td><td colspan='5'><input readonly class='inputarea' style='background-color:#C0C0C0' type='text' name='img_edit_link' size='20' maxlength='255' value=''></td>
<script>document.write (pframe_toolbar2html("link,unlink"));</script>
<td>name:</td><td><input class='inputarea' onBlur='pframe_UpdateImgProperties()' type='text' name='img_edit_name' size='20' maxlength='255' value=''>
</td></tr></table>
</td></tr></table>
</div>

<div id='DIVanchor_edit_panel' style='display:none'>
<table  border='0' cellpadding='2' cellspacing='0'><tr><td>
<input readonly class='inputarea' style='background-color:#C0C0C0' type='text' name='link_edit_link' size='15' maxlength='255' value=''>
</td><script>document.write (pframe_toolbar2html("link,unlink"));</script><td>name:</td><td><input class='inputarea' onBlur='pframe_UpdateLinkProperties()' type='text' name='link_edit_name' size='15' maxlength='255' value=''>
</td><td>
style: </td><td><select style='width:150px' multiple size='1' name='AnchorStyleDropBox' class='inputarea' onClick='EditFrame.eframe_StoreCursorPos(); pframe_ShowAnchorStyleList()'>
<script>document.write(pframe_EnumTextStyleForDropBox("|a|"));</script>
</td>
</tr></table>
</div>
</td>
<td align='right'>
<? print "
<input type='hidden' name='BindTo' value='$BindTo'>
<input type='hidden' name='ResizedImgs' value=''>
$_[RT_PAGE_TITLE]:<br><input name='Title' class='inputarea' value='$Title'><br>
<input class='button' class='button' type='button' value='$__[CAPTION_OK]' onClick='EditFrame.eframe_DoSubmit();'>
<input class='button' class='button' type='button' onClick='EditFrame.editBox.innerHTML=\"\";W.modalResult(false)' value='$__[CAPTION_CANCEL]'>
<input type='hidden' name='DocID' value='$DocID'>
<input name='TextContent' value='' type='hidden'>
";
?>
</td></tr><tr valign='top'><td colspan='2' height='100%' bgcolor='#c0c0c0'></td></tr></table>
</div>
<div id='DIVpasteBox' style='width:1; height:1; overflow:hidden'></div>
<iframe style='margin:10; width:100%; height:expression(document.body.clientHeight-130)' id='EditFrame' name='EditFrame'
src='<? print ActionURL("stdctrls.IRichtext.RenderEditorIFrame.f",array(BindTo=>$BindTo,DocID=>$DocID)); ?> '>
</iframe>

<script>
img_edit_panel=P$.find('DIVimg_edit_panel');
link_edit_panel=P$.find('DIVanchor_edit_panel');
para_style_selector=P$.find('DIVParagraphStyleSelector');
anchor_style_selector=P$.find('DIVAnchorStyleSelector');
textstyle_dropbox=P$.find('TextStyleDropBox');
popupBox=P$.find('DIVpopup');
para_style_selector.innerHTML=pframe_EnumTextStyle(para_tags,true);
anchor_style_selector.innerHTML=pframe_EnumTextStyle("|A|");
anchor_style_dropbox=P$.find('AnchorStyleDropBox');
</script>

<?
  return;
  }

function RenderEditorIFrame($args)
  {
  extract(param_extract(array(
    BindTo=>'string',
    DocID=>'int',
    ),$args));

  $__=&$GLOBALS[_STRINGS][_];
  $_=&$GLOBALS[_STRINGS][stdctrls];
  global $cfg,$_CORE;
#  $_CORE->InitWindows();

  if ($BindTo)
    {
    $BindToInfo=BindPathInfo($BindTo);
    if (!$BindToInfo) return array(Error=>'[BAD_BINDTO_PATH]',Details=>$BindTo);
    }

  $s="SELECT * FROM stdctrls_Richtexts WHERE BindTo='$BindTo'";
  if ($DocID) {$s.=" AND DocID=$DocID";}
  $q=DBQuery ($s);
  if ($q)
    {
    $doc=$q->Top;
    $DocID=$doc->DocID;
    }
  print "<script src='$cfg[PublicURL]/stdctrls/js_richtext_editor_eframe.js'></script>";
?>
<style>
td.clear{border:1px dotted #8d8e8f;}
</style>
<div id='DIVpopup_tabstyles'
  style='left:0; position:absolute; visibility:hidden; background-color:#c0c0c0;'>
<script>eframe_DrawTabStyles();</script>
</div>
<div id='DIVeditBox'
          style='width:98%; height:100%'
          onMouseUp='return eframe_MouseUp()'
          onFocus='eframe_ClosePopup()'
          onResizeEnd='jsb_ControlResizeEnd()'
          onPaste='return eframe_Paste();'
          onContextMenu="return false"
          onCon trolSelect='eframe_OnSelect(event.srcElement);'
          onCl ick='eframe_OnSelect()'
          onSe lectStart='eframe_OnSelect()'
          onKeyPress='eframe_KeyPressed()'
          onKeyUp='eframe_OnSelect()'
          >
<?
  if ($doc)
    {
    print $doc->Content;
    }
?>
</div>
<script>body_onLoad();</script>
<?
  return true;
  }  # end of ^RenderEditor


} # end of class declaration


/*
function _repimg_($matches)
  {
  if (($matches[2])=='IMG')
    {
    if (strpos($matches[3],".png")!==false)
      {
      return "";
      }
    }
  return $matches[0];
  }
*/

?>
