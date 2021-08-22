<?
function core_PutPages(&$args)
  {
  $__=&$GLOBALS[_STRINGS][_];

  extract(param_extract(array(
    ToString=>"boolean",
    PageCount=>"int",
    PageNo=>'int=1',
    PageArray=>'array',
    ToForm=>'string',
    ToControlArg=>'string', # i.e.
    ToURL=>'string',
    Style=>'string=simpletext',
    Align=>'string=right',
    PagesCaption=>"string=$__[CAPTION_PAGES]",
    ButtonPrevStyle=>'string',
    ButtonNextStyle=>'string',
    ButtonPrevCaption=>'string',
    ButtonNextCaption=>'string',
    ),$args));

  $s="";
  switch ($Style)
    {
    case 'simpletext':
    if ($PageCount>1)
      {
      $centralpagestart=$PageNo-2;
      $centralpageend=$PageNo+2;
      $startpageend=3;

      if ($centralpagestart<$startpageend)
        {
        $startpageend=0; # no startpage serie
        $centralpagestart=1;
        $centralpageend=7;
        }

      $endpagestart=$PageCount-1;
      if (($endpagestart-$centralpageend)<0)
        {
        $endpagestart=0; # No endpages serie
        $centralpageend=$PageCount;
        }

      for ($i=1;$i<=$PageCount;$i++)
        {
        if (($startpageend)&&($i>$startpageend)&&($i<$centralpagestart))
          {
          $s.= "...";
          $i=$centralpagestart;
          }
        if (($endpagestart) && ($i>$centralpageend) && ($i<$endpagestart))
          {
          $s.="...";
          $i=$endpagestart;
          }

        $caption=(is_array($PageArray)) ? $PageArray[$i-1] : $i;
        if ($i==$PageNo)
          {
          $s.=" <b>$caption</b> ";
          }
        else
          {
          if (strlen($caption)>10) {$caption=substr($caption,0,7).'..';}
          if ($ToForm)
            {
            $s.=" <a href='javascript:openPage($ToForm,$i);' $ac>$caption</a> ";
            }
          elseif($ToControlArg)
            {
            $s.=" <a $ac href='$ToControlArg$i'>$caption</a> ";
            }
          elseif ($ToURL)
            {
            $s.=" <a $ac href='$ToURL".((strpos($ToURL,'?')===false)?"?":"&")."PageNo=$i'>$caption</a> ";
            }
          }
        }
       /*      if (($ButtonPrevStyle)&&($ButtonNextStyle))
        {
        $buttons="";
        if ($PageNo>1) {
          $buttons.=$_ENV->PutButton(array(
            Style=>$ButtonPrevStyle,
            Caption=>$ButtonPrevCaption,
            $Control->JSBPageID."|".$ControlPages['JSBPageControlID']."_p=".($PageNo-1)."$getargs",
            $Control->JSBPageControlID.'p',$_[TPAGEBUTTONS_CAPTION_PAGEPREV]);
          }
        if ($PageNo<$PageCount) {
          $buttons.=$this->_getButtonHtml($ButtonNext,$Control->JSBPageID."|".$ControlPages['JSBPageControlID']."_p=".($PageNo+1)."$getargs",$Control->JSBPageControlID.'n',$_[TPAGEBUTTONS_CAPTION_PAGENEXT]);
          }
        switch ($Align) {
          case 'right': $s=$s.$buttons; break;
          case 'center' : $s=$buttons."<br>".$s; break;
          default: $s=$buttons.$s; break;
          }
        }
        */
      $s="<table width='100%'><tr><td nowrap align='$Align'>$PagesCaption$s</td></tr></table>";
      }
    break;
    }

  if ($ToForm) {$s.="<input type='hidden' name='PageNo' value='$PageNo'><script>
  function openPage(form,p)
    {
    form.PageNo.value=p;
    form.submit();
    }
  </script>";}
  if ($ToString) return $s;
  print $s;
  }


?>
