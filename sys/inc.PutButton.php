<?
function core_PutButton(&$args)
  {
  # Action    ok/cancel/back/submit
  # OnClick   javascript code
  # Href      any href
  # Kind      glyph: ok/cancel/delete
  # ImgSrc    override default Action imgbtn
  # Caption   text on a button
  # Style     one of style from _THEME
  # ToString  if true returns button as string
  # ID        object id
  # Disabled
  # NewWindow  for Href
  # AutoHide    for submit button

  global $cfg,$_SYSSKIN_NAME,$_THEME_NAME,$_THEME;
  $__=$GLOBALS['_STRINGS']['_'];
  if (is_string($args)) {$args=array(Action=>$args);}
  extract ($args);
  $result="";
  if (!$Style) $Style="default";
  $SkinPath=$_THEME['SkinPath'];
  $SkinURL=$_THEME['SkinURL'];
  if (!$ButtonStyle) $ButtonStyle['CSS']="input";
  $TypeString="type='button'";
  
  
  switch ($Action)
    {
    case 'submit': $TypeString="type='submit'";
      if (!$Kind) $Kind='ok';
      if ($AutoHide) {if (!$OnClick) $OnClick="this.style.visibility='hidden';";}
      break;
    case 'ok':     $OnClick="W.modalResult(true);"; break;
    case 'cancel': $OnClick="W.modalResult(false);"; break;
    case 'back':   $Href="$_SERVER[HTTP_REFERER]"; break;
#    default: break;
    }
  if ($OnClick) $OnClick="onClick=\"$OnClick\"";

  if (($Action)&&(!$Kind)) $Kind=$Action;
  switch ($Kind)
    {
    case 'submit': if (!$Caption) $Caption=$__['CAPTION_OK']; break;
    case 'ok': if (!$Caption) $Caption=$__['CAPTION_OK']; break;
    case 'add': if (!$Caption) $Caption=$__['CAPTION_ADD']; break;
    case 'cancel': if (!$Caption) $Caption=$__['CAPTION_CANCEL']; break;
    case 'delete': if (!$Caption) $Caption=$__['CAPTION_DELETE']; break;
    }

  $btnid=($ID)?" id='$ID'":"";
  $btnName=($Name)?" name='$Name'":"";
  $dis=($Disabled)?"disabled":"";

  if ($_THEME['Buttons'])
    {
    $ButtonStyle=$_THEME['Buttons'][$Style];

    if ($ButtonStyle['TwoPart']){
      list ($BtnPrefix)=explode (",",$ButtonStyle['TwoPart']);
      $Width=intval($ButtonStyle['Width']);
      $GlyphURL ="$SkinURL/$BtnPrefix-$Kind.gif";
      $GlyphFile="$SkinPath/$BtnPrefix-$Kind.gif";
      $f1="$SkinPath/$BtnPrefix-partleft.gif";
      if ($Href) {
        if (!$NewWindow) {
          $OnClick="onClick=\"$OnClick; location.href='$Href'; \"";
        }else{
          $now=time();
          $OnClick="onClick=\"$OnClick; window.open('$Href','$now','menubar=yes,alwaysRaised=yes,resizable=yes,scrollbars=yes,toolbar=yes');\"";
        }
      }

      if (file_exists($f1))
        {
        $img=($size)?"<img src='$ImgSrc' border=0 $size[3] align='absmiddle'>":"";
        $imgright="<img src='$SkinURL/$BtnPrefix-partright.gif'/>";
        $bgleft="$SkinURL/$BtnPrefix-partleft.gif";
        $ImgSrc="";
        if (file_exists($GlyphFile))
          {
          $gsize=@getimagesize($GlyphFile);
          if ($gsize)
            {
            $GlyphSrc="<img border='0' $gsize[3] src='$GlyphURL' align='absmiddle'/>";
            if ($Width) $Width-=$gsize[0];
            }
          }

        if (!$GlyphSrc)
          {
          $GlyphSrc="<img src='$SkinURL/$_THEME[Spacer]' width='10' align='absmiddle'/>";
          if ($Width) $Width-=10;
          }
        if ($ButtonStyle['CSS']) {list($t,$c)=get_css_pair($ButtonStyle['CSS']);}
        if ($Width) {$Width=" width='$Width' ";}

        $Caption=str_replace(' ','&nbsp;',$Caption);
        $result.="<button $dis $btnid $btnName $TypeString $OnClick style='border:none;background-color:transparent;cursor:hand;padding:0;'><table border=0 cellpadding=0 cellspacing=0><tr>
        <td style='background-image:url($bgleft);background-repeat:no-repeat;' nowrap><table width='100%' cellpadding=0 cellspacing=0 border=0>
        <tr><td>$GlyphSrc</td><td $c $Width align='center' nowrap>$Caption</td></tr></table></td>
        <td>$imgright</td></tr></table></button>";
        $ButtonStyle['CSS']="";
        } else {$ButtonStyle['CSS']="input.button";}
      }
    elseif ($ButtonStyle['ImgSrc'])
      {
#      $SkinURL=$cfg['PublicURL'].'/sys/skins/'.$_SYSSKIN_NAME;
      $ImgFile="$SkinPath/".$ButtonStyle['ImgSrc'];
      if (!$ImgFile) {
        print_developer_warning("Button image file not found",$ImgFile);
        }
      $size=@getimagesize($ImgFile);
      if ($size){
        $ImgURL ="$SkinURL/".$ButtonStyle['ImgSrc'];
        $ImgURLHover =$ButtonStyle['ImgSrc_hover'];
        $OnMouse="";
        if ($ImgURLHover) {$OnMouse=" onMouseOver='this.oldSrc=this.src; this.src=\"$SkinURL/$ImgURLHover\";' onMouseOut='this.src=this.oldSrc;'";}
        if ($Href) {
          $result.= "<a href='$Href'".(($NewWindow)?" target='_blank'.":"")."><img border=0 $OnMouse $btnid $btnName src='$ImgURL' $size[3]></a>";
        }else{
          $result.= "<a href='#' $OnClick><img border=0 $OnMouse $btnid $btnName src='$ImgURL' $size[3]></a>";
        }
        }else {$ButtonStyle['CSS']="input.button";}
      }
    } else {$ButtonStyle['CSS']="input";}

  if ($ButtonStyle['CSS'])
    {
    list($t,$c)=get_css_pair($ButtonStyle['CSS']);
    $result.="<$t$c $dis $btnid $btnName value='$Caption' $OnClick $TypeString/>";
    }

  if ($ToString) return $result; else print $result;
  }




?>
