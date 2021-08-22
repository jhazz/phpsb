<?
class img_IImgIndex
{
var $CopyrightText="(c)2003 PHP Systems Builder. Image storage cartridge";
var $CopyrightURL="www.phpsb.com/img";
var $ComponentVersion="1.0";

function LoadXML($args)
  {
  global $cfg;
  extract(param_extract(array(
    BindTo      =>'string',
    TnFormat    =>'string=B',
    TargetFrame =>'string'
    ),$args));

  $imgPath=$cfg['FilesPath']."/img";
  $imgURL =$cfg['FilesURL']."/img";
  $TnFormatNo=ord ($TnFormat)-65;
  $BindToDetails=BindPathInfo($BindTo);
  if ($BindToDetails)
    {
    $q=DBQuery ("SELECT * FROM img_Documents WHERE BindTo='$BindTo'
    ORDER BY OrderNo,ImgID","ImgID");
    }
  if (!$q) {return false;}

  $TargetFrameAttr=($TargetFrame)?"frame='$TargetFrame'":"";
  $result="";

  foreach ($q->Rows as $ImgID=>$imgdoc) {
    $Img_file=$imgPath.'/'.$BindToDetails->Class.'/'.$imgdoc->ImgName;
    $Img_url= $imgURL.'/'.$BindToDetails->Class.'/'.$imgdoc->ImgName;
    if ($TnFormat)
      {
      $tn_s=explode_properties($imgdoc->Filenames);
      $tn=$tn_s[$TnFormatNo];
      if (!$tn) {$tn=$tn_s[0];}
      $tn=basename($tn);
      if ($tn)
        {
        $tn_file= $imgPath.'/'.$BindToDetails->Class.'/'.$tn;
        $info=@getimagesize($tn_file);
        if ($info) {
          $result.="<img src='$imgURL/$BindToDetails->Class/$tn' $info[3] onClick=\"|$TargetFrame|src=$Img_url\"/>";
          }
        }
      }

  }
  return array(XML=>$result);
}

function Edit($args)
  {
  $args['EditMode']=1;
  $args['ColumnCount']=5;
  $args['DummyMode']=1;
  $this->View($args);
  }

function View($args,$InternalCall=false)
  {
  extract(param_extract(array(
    BindTo        =>'string',
    TnFormatNo    =>'int=1',
    ShowCaptions  =>'int',
    CSS_Caption   =>'string',
    ColumnCount   =>'int',
    EditMode      =>'int',
    Insertable    =>'int',
    ForceTnSize   =>'string',
    OnShow        =>'string=w',
    Monitor       =>'string',
    ImgViewContext=>'string',
#    CSS_Cell      =>'string',
    CSS_Href      =>'string',
    ImageStyle    =>'string',
    ImagesPerPage =>'int',
    PageNo        =>'int=1',
    Padding       =>'int',
    CSS_Background=>'string',
    AddWndWidth=>'int=40',
    AddWndHeight=>'int=40',
    ImgAlign=>'string',

    CaptionCallback=>'function', # used by TRateitImg
    RText=>'string',
    SelectOneImage=>"int", # select one image mode
    Selected=>"int"        # already selected image - highlight it for user
    ),$args));


  global $cfg,$_HOMEURL,$_THEME;
  $_=&$GLOBALS['_STRINGS']['img'];

  $BindToInfo=BindPathInfo($BindTo);
  if (!$BindToInfo)
    {
    return array(Error=>$_['TIMAGE_BAD_BINDING']);
    }

  if (!$ColumnCount)
    {
?><script>
var imgCount=0;
function loadImages(){
regImgResizing();
for (var i=1;i<=imgCount;i++)
  {
  var aimg=P$.find("im"+i);
  if (aimg) {src=aimg.getAttribute('LOADSRC'); if (src) aimg.src=src;}
  }
}

function regImgResizing(){
	var x,y,pos,wt=P$.find('widthtable'),i,aimg,cellwidth,rowheight,blockWidth,vpos;
	if (wt){
		maxWidth=wt.offsetWidth;
		pos=GetAbsXY(wt);
		cellHeight=cellWidth=0;
		for (var i=1;i<=imgCount;i++){
			aimg=P$.find("img"+i);
			if (aimg){
				if (aimg.offsetWidth>cellWidth) cellWidth=aimg.offsetWidth;
				if (aimg.offsetHeight>cellHeight) cellHeight=aimg.offsetHeight;
			}
		}

		colCount=Math.floor(maxWidth/cellWidth);
		blockWidth=colCount*cellWidth;
		startX=pos.x; // Centering: pos.x+Math.floor((maxWidth-blockWidth)/2);

		x=startX; y=pos.y;
		vpos=0;
		colNo=1;
		for (var i=1;i<=imgCount;i++){
			if (colNo==1) vpos+=cellHeight;
			aimg=P$.find("img"+i);
			if (aimg){
				aimg.style.left=x;
				aimg.style.top=y;
				aimg.style.height=cellHeight;
				aimg.style.width=cellWidth;
				x+=cellWidth;
				if (colNo>=colCount) {x=startX; y+=cellHeight; colNo=0; }
				colNo++;
			}
		}
		wt.style.height=vpos;
	}
}
</script><?
    }

  $IImage=&$_ENV->LoadInterface("img.IImage");

  if ($EditMode)
    {
    if ($_GET['viewformatno']) {$TnFormatNo=$_GET['viewformatno'];}
    $r=$IImage->_getFormatInfo(array(BindTo=>$BindTo));
    if ($r['Error'])
      {
      return array(Error=>$r['Error'],Details=>$r['Details']);
      }
    $qf=&$r['qf'];
    $FormatID=$r['FormatID'];
    $TnFormat=$qf->Rows[$TnFormatNo];
#    $_ENV->InitWindows();
    if (!$qf)
      {
      print_developer_warning("No image format defined!");
      }
    }

  $subFolder=$BindToInfo->Folder;

  $Images_Path=$cfg['FilesPath']."/img/".$subFolder;  # has ending slash!
  $Images_URL =$cfg['FilesURL'] ."/img/".$subFolder;

  list ($ForceTnWidth,$ForceTnHeight)=explode ("x",$ForceTnSize);
  list ($Caption_tag,$Caption_CSS)=get_css_pair ($CSS_Caption,"span");

  $BindToID=intval($BindToInfo->ID);
  if ($InternalCall)
    {
    if ($BindToID) $BindToStr=" BindTo='$BindToInfo->Folder$BindToID' ";
    else $BindToStr=" BindTo LIKE '$BindToInfo->Folder%' ";
    }
  else {
    # if it executes from Internet browser limit browsing to only one object!!!
    if (!$BindToID) {return array(Error=>"Bound object not selected!","$BindTo");}
    $BindToStr=" BindTo='$BindToInfo->Folder$BindToID' ";
  }

  $Limits="";
  if ($ImagesPerPage)
    {
    $Limits="LIMIT ".($ImagesPerPage*($PageNo-1)).",$ImagesPerPage";
    }
  $q=DBQuery ("SELECT * FROM img_Documents WHERE $BindToStr ORDER BY OrderNo,ImgID $Limits","ImgID");
  if ($q)
    {
    $imgRows=$q->Rows;
    $this->print_Scripts($EditMode);

    if ($EditMode)
      {
      $FormName=substr(md5(uniqid("",true)),0,8);
      $Selectable=true;
      if ($Insertable) $imgRows[]="dummy";

      $select="";
      if ($qf) foreach ($qf->Rows as $vTnFormatNo=>$row)
        {
        if ($vTnFormatNo==0) continue;
        $sel=($TnFormatNo==$vTnFormatNo)?"checked":"";
        $select.="<tr><td align='right' style='font-size:9px;'  class='bgup'>".langstr_get($row->Caption)." [$row->Width x $row->Height]</td><td><input onClick='selecttn.submit();' type='radio' name='viewformatno' value='$vTnFormatNo' $sel></td></tr>";
        } if ($select) $select="<table cellpadding=0 cellspacing=0>$select</table>";
      if ($select) {
        unset ($GLOBALS['Args']['viewformatno']);
        $select="<td align='right' class='bgup'><form style='padding:0;margin:0;' name='selecttn' method='get'><b>$_[CHOOSE_THUMBNAIL_FORMAT]:</b>
        <input type='hidden' name='ArgsStr' value='".$_ENV->Serialize($GLOBALS['Args'])."'>$select</form></td>";
        }

      print "<div class='bgup'>
        <table width='100%' cellspacing=0 cellpadding='$Padding' border='0'><tr valign='bottom'><td>";
        $_ENV->PutButton(array(Caption=>$_['IMAGE_REMOVE'],
          OnClick=>"frm_$FormName.target=W.openModal({Title:'>$_[IMAGE_REMOVE]',reloadOnOk:1}); frm_$FormName.submit();",
          Kind=>'delete'));
        $_ENV->PutButton(array(Caption=>$_['IMAGE_REGENERATE'],OnClick=>"W.openModal({url:'"
         .ActionURL("img.IImage.Regenerate.b",array(BindTo=>$BindTo))
         ."',w:500,h:150,Title:'Images are regenerating',reloadOnOk:1})"));
        print "</td>$select</tr></table></div>
        <form name='frm_$FormName'
        method='post' action='".ActionURL("img.IImage.Delete.b")."'>
        ";
      }
    }
  else
    {
    if ($Insertable)
      {
      $imgRows[$i]="dummy";
      $Selectable=false;
      }
    }

  if ($EditMode)
    {
    $addimgscript="W.openModal({url:\"".ActionURL("img.IImage.Edit.f",array(BindTo=>$BindTo))."\",reloadOnOk:1})";
    }

  if (!$ColumnCount)
    {
    list ($t,$c)=get_css_pair($CSS_Background);
    print "<table id='widthtable' border='0' cellpadding=0 cellspacing=0 width='100%'><tr><td $c></td></tr></table>";
    }


  $ColWidth="";
  $ColNo=1;
  if (($imgRows)&&($BindTo || $Insertable))
    {
    if ($ColumnCount)
      {
      print "<table width='100%' cellpadding='$Padding' cellspacing=0>";
      $ColWidth=" width='".(100/$ColumnCount)."%'";
      }
    $imgNo=0;
    foreach ($imgRows as $ImgID=>$imgdoc)
      {
      $imgNo++;
      $ImgID=intval($ImgID);
      $Tn_Tag="";


      $preloadsrc=" src='$_THEME[SkinURL]/$_THEME[Spacer]' ";
      if ($imgdoc=="dummy")
        {
        if (($EditMode)&&($BindToID))
          {
          $Tn_Width =$TnFormat->Width; $Tn_Height=$TnFormat->Height;
          if ($ForceTnWidth) { $Tn_Width =$ForceTnWidth; $Tn_Height=$ForceTnHeight; }
          $src=($ColumnCount)?"src='$cfg[PublicURL]/img/dummy_img.gif'":" $preloadsrc loadsrc='$cfg[PublicURL]/img/dummy_img.gif'";
          $Tn_Tag="<a href='javascript:;'><img id='im$imgNo' onClick='$addimgscript' border='0' $src width='$Tn_Width' height='$Tn_Height'/></a>";
          if ($ShowCaptions)
            {
            #$Tn_Tag.="<br/><$Caption_tag $Caption_CSS>$_[IMAGE_DUMMYCAPTION]</$Caption_tag>";
            }
          $Tn_Tag.="";
          $Tn_Caption=$_['IMAGE_DUMMYCAPTION'];
          }
        }
      else
        {
        $Tn_Tag="<img border='0' src='$cfg[PublicURL]/img/dummy_img.gif' width='$Tn_Width' height='$Tn_Height'>";
        $Tn_URL="";
        $Filenames=explode_properties($imgdoc->Filenames);
        $Img_URL =$Images_URL .$Filenames[0];
        $Img_Path=$Images_Path.$Filenames[0];
        if (!is_file($Img_Path)) continue;
        $Img_Info=@getimagesize($Img_Path);
        if (($Img_Info[2]==4)||($Img_Info[2]==13))
          {
          # Stored image is SWF
          $Tn_Path=$Img_Path;
          $Tn_URL =$Img_URL;
          $Tn_Width =$Img_Info[0];
          $Tn_Height=$Img_Info[1];
          $aspect=$Tn_Width/$Tn_Height;
          /*
          $fw=($ForceTnWidth )?$ForceTnWidth :$TnFormat->Width;
          $fh=($ForceTnHeight)?$ForceTnHeight:$TnFormat->Height;
          if ($Tn_Width >$fw) {$Tn_Width =$fw; $Tn_Height=round($Tn_Width/$aspect);}
          if ($Tn_Height>$fh) {$Tn_Height=$fh; $Tn_Width =round($Tn_Height*$aspect);}
          */
          $Tn_Tag=$_ENV->PutSwf(array(ToString=>1,width=>$Tn_Width,height=>$Tn_Height,src=>$Img_URL));
          
          if ($EditMode) {$Tn_Tag.="<br>[Edit flash swf-file]";}

          }
        else
          {
          # Stored image is png/gif/jpg
          $tn=basename($Filenames[$TnFormatNo]);
          $Tn_Path=$Images_Path.$tn;
          $cc=langstr_get($imgdoc->Caption);
          $alt=($cc) ?" alt='$cc' ":"";

          if (($tn)&&(is_file($Tn_Path)))
            {
            $Tn_URL= $Images_URL .$tn;
            $Tn_Path=$Images_Path.$tn;
            $Tn_Info=@getimagesize($Tn_Path);
            $Tn_Width =$Tn_Info[0];
            $Tn_Height=$Tn_Info[1];
            $aspect=$Tn_Width/$Tn_Height;
            if ($ForceTnWidth)
              {
              if ($Tn_Width >$TnFormat->Width) {$Tn_Width =$TnFormat->Width; $Tn_Height=round($Tn_Width/$aspect);}
              if ($Tn_Height>$TnFormat->Height){$Tn_Height=$TnFormat->Height;$Tn_Width =round($Tn_Height*$aspect);}
              }
              /*
            if (substr($Tn_Path,-4)=='.swf')
              {
              $Tn_Tag=$_ENV->PutSwf(array(ToString=>1,SWF=>$Tn_URL,Width=>$Tn_Width,Height=>$Tn_Height));
              $Tn_Tag.="<br/>[Edit image and swf]";
              }
            else*/
              {
              $src=($ColumnCount)?"src='$Tn_URL'":" $preloadsrc loadsrc='$Tn_URL'";
              $Tn_Tag="<img $alt border=0 id='im$imgNo' $src width='$Tn_Width' height='$Tn_Height' $alt>";
              }
            }
          else
            {
            if ($EditMode)
              {
              $src=($ColumnCount)?"src='$cfg[PublicURL]/img/dummy_img.gif'":" $preloadsrc loadsrc='$cfg[PublicURL]/img/dummy_img.gif'";
              $Tn_Tag="<img id='im$imgNo' border='0' $src width='$Tn_Width' height='$Tn_Height'><br>
               Need to [Regenerate]!";
              }
            }
          }

        if ($SelectOneImage)
          {
          $strurl="href='javascript:;' onClick='W.modalResult($ImgID)'";
          }
        elseif ($EditMode)
          {
          $url=ActionURL("img.IImage.Edit.f",array(ImgID=>$ImgID,BindTo=>$imgdoc->BindTo));
          $strurl="href='javascript:;' onClick=\"W.openModal({url:'$url',reloadOnOk:1,Title:'$_[IMGCONTAINER_EDITCONTENT]'});\"";
          }
        else
          {
          $TargetURL=$imgdoc->TargetURL;
          if ($TargetURL)
            {
            if (substr($TargetURL,0,3)=='www') {$TargetURL="http://".$TargetURL; }
            if (substr($TargetURL,0,7)=='http://')
              {
              $strurl="target='_blank' href='$TargetURL'";
              }
            else
              {
              $inf=BindPathInfo($TargetURL);
              $strurl="href='".$GLOBALS['_HOMEURL']."/".$inf->Context."/".$inf->ID.".".$cfg['VirtualExtension']."'";
              }
            }
          else
            {
            switch ($OnShow)
              {
              case 'n':
                $strurl="";
                break;
              case 'm':
                if ($Monitor)
                  {
                  list ($ControlID,$Type)=explode (":",$Monitor);
                  $strurl="href=\"javascript:img_monitor_show_$ControlID('$Img_url',$Img_Info[0],$Img_Info[1]);\"";
                  }
                break;
              case 'c':
                $strurl="href='$_HOMEURL/$ImgViewContext/$ImgID.".$cfg['VirtualExtension']."'";
                break;
              case 'hmw':
                $strurl="href=\"javascript:;\" onClick=\"W.openModal({url:'$_HOMEURL/$ImgViewContext/$ImgID.$cfg[VirtualExtension]',w:".($Img_Info[0]+$AddWndWidth).",h:".($Img_Info[1]+$AddWndHeight)."});\"";
                break;
              case 'hbw':
                $strurl=" target='_blank' href='$_HOMEURL/$ImgViewContext/$ImgID.".$cfg['VirtualExtension']."'";
              	break;
              case 'mw':
                $strurl="href=\"javascript:;\" onClick=\"W.openModal({url:'$Img_URL',w:".($Img_Info[0]+$AddWndWidth+20).",h:".($Img_Info[1]+$AddWndHeight+20)."});\"";
                break;

              case 'w':
                $strurl="href=\"javascript:;\" onClick=\"window.open('$Img_URL','view',
                'resizable=yes,scrollbars=yes,width=".($Img_Info[0]+$AddWndWidth).",height=".($Img_Info[1]+$AddWndHeight)."');\"";
                break;

              case 'b': default:
                $strurl="target='_blank' href='$Img_URL'";
                break;
              }
            }
          }

        $Tn_Caption="";
        if ($strurl)
          {
          $Tn_Tag="<a $strurl>$Tn_Tag</a>";
          }
        if ($Selectable)
          {
          $Tn_Tag='<input type="checkbox" name="img_selected['.$ImgID.']" value="1">'.$Tn_Tag;
          }

        if (($EditMode)&&($ShowCaptions))
          {
          $Tn_Caption="[#".$imgdoc->OrderNo."]";
          }
        if ( (($ShowCaptions)&&($imgdoc->Caption)) || ($addcaption))
          {
          $Tn_Caption.=nl2br(langstr_get($imgdoc->Caption));
          }
        }

/*
        $addcaption="";
        if ($CaptionCallback)
          {
          list ($Href_tag,$Href_class)=get_css_pair ($CSS_Href,"a");
#  BACKDOOR!!!
#          eval ("\$addcaption=$CaptionCallback($ImgID,\$RText);");
          $addcaption.="<$Href_tag $Href_class $strurl>$addcaption</$Href_tag><br>";
          }
*/

      $Image=$IImage->MakeThumbnailHtml(array(
        ImageHtml=>$Tn_Tag,
        CaptionHtml=>$Tn_Caption,
        Align=>$ImgAlign,
        ImageStyle=>$ImageStyle
        ));

      if (($ColumnCount)&&($ColNo==1)) {print '<tr valign="top">';}
      list ($Cell_tag,$Cell_class)=get_css_pair ($CSS_Cell,"td");
      if (!$ColumnCount)
        {
        print "<div id='img$imgNo' $Cell_class style='position:absolute'>
        <table width='$Tn_Width' border=0 cellpadding='3' cellspacing='1'><tr valign='bottom'><td align='center'>"
          .$Image."</td></tr></table></div>";
        }
      else
        {
        print "<$Cell_tag $Cell_class $ColWidth align='center'>$Image</$Cell_tag>";
        }
      $ColNo++;
      if (($ColumnCount)&&($ColNo>$ColumnCount)) {print '</tr>'; $ColNo=1;}
      }
    if ($ColumnCount) {print '</table>';}
    else {print "\n<script>imgCount=$imgNo;
      PU.on('resize',regImgResizing);
      PU.on('load',loadImages);
      </script>";}
    }


  if ($EditMode)
    {
    if ($q) print "</form>";
/*      print "<hr><font style='font-size:9px; color:#808080'>
      <b>Format:</b> ".$qf->Top->Caption."<br>
      <b>Binding context:</b> [$BindToInfo->Folder]<br>
      <b>Path to the folder:</b> $Images_Path<br>
    </font><br>";
*/      }

  print "<br/>";
  }

function print_Scripts($DesignMode)
  {
  global $cfg;

  global $ImgScriptsPrinted;
  if ($ImgScriptsPrinted) {return;}
  $ImgScriptsPrinted=true;
  }
}

?>

