<?php
class stdctrls_TColumns
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard controls";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $Detected=0;

function InitComponent()
  {
  $_=&$GLOBALS['_STRINGS']['stdctrls'];
  global $_THEME;

  $this->Propdefs=array(
    Style         =>array(Type=>"ThemeElement",DefaultValue=>'default',Section=>"ColumnStyles",Caption=>$_['TCOLUMNS_STYLE']),
    ColumnCount   =>array(Type=>"Int",DefaultValue=>2,Caption=>$_['TCOLUMNS_COUNT']),
    Padding       =>array(Type=>"Int",DefaultValue=>5,Caption=>$_['TCOLUMNS_PADDING']),
    VerticalSeparator=>array(Type=>"ThemeElement",Section=>"VerticalSeparators",Caption=>$_['VERTICAL_SEPARATOR_STYLE']),
    SeparatorWidth=>array(Type=>"Int",DefaultValue=>0,Caption=>"0-use predefined style width"),
    Width         =>array(Type=>"String",DefaultValue=>"100%"),
    ShowHeader    =>array(Type=>"Boolean",Caption=>$_['TCOLUMNS_SHOW_HEADER']),
    SlotNamePrefix=>array(Type=>"String",DefaultValue=>"Columns".($this->Detected+1)."-",Caption=>"Unique word that differ from existing slot names and columns",Required=>true),
    ColumnWidths  =>array(Type=>"String",Caption=>$_['TCOLUMNS_WIDTHS']),
    HeaderColors =>array(Type=>"String",Caption=>$_['TCOLUMNS_HEADER_COLORS']),
    ColumnColors =>array(Type=>"String",Caption=>$_['TCOLUMNS_COLUMN_COLORS']),
    );
  }

function Init(&$Control)
  {
  $this->Detected++;
  $SocketAddress=$Control->Properties['SocketAddress'];
  $Control->Data['SocketAddress']=$SocketAddress;
  }

function Render(&$Control)
  {
  global $cfg,$_THEME,$_THEME_NAME;
  if ($Control->Properties) extract($Control->Properties);
  $Padding=intval($Padding);
  if ($ColumnWidths) $widths=explode (",",$ColumnWidths);

  $sdata=$_THEME['ColumnStyles'][$Style];
  $sepdata=$_THEME['VerticalSeparators'][$VerticalSeparator];

  $SkinURL=$_THEME['SkinURL'];
  $Spacer=$SkinURL.'/'.$_THEME['Spacer'];
  $SkinPath=$_THEME['SkinPath'].'/';
  
  print "\n\n";
  list ($Separator_tag,$Separator_class)=get_css_pair ($sepdata['CSS'],"td");
  if (!$SeparatorWidth) $SeparatorWidth=$sepdata['Width'];
  list($innertag,$innerclass)=get_css_pair($sdata['InnerCSS'],'td');
  if ($HeaderColors) $arrayHeaderColors=explode(",",$HeaderColors);
  if ($ColumnColors) $arrayColumnColors=explode(",",$ColumnColors);

#  $s= !empty($sdata['headBgColor'])?"background-color:$sdata[headBgColor];":"";
  $s.=!empty($sdata['headTextColor'])?"color:$sdata[headTextColor];":"";
  if ($Padding) $s.="padding:$Padding;";
  if ($s) $headStyle=" style='$s' ";

#  $s= !empty($sdata['cellBgColor'])?"background-color:$sdata[cellBgColor];":"";
  $s=!empty($sdata['cellTextColor'])?"color:$sdata[cellTextColor];":"";
  if ($sdata['bg']) $s.="background-image:url($SkinURL/$sdata[bg]); background-repeat:no-repeat; ";
  if ($Padding) $s.="padding:$Padding;";
  if ($s) $columnStyle=" style='$s' ";

  if ($sdata)
    {
    if ($sdata['tl']) {
      	if (!file_exists($SkinPath.$sdata['tl'])) {
      		print_developer_warning("Column design image (topleft) not found. Check out ColumnStyles section in theme.php",$SkinPath.$sdata['tl']);
      		return;
      	} else {
		      list ($tl_w,$tl_h)=@getimagesize($SkinPath.$sdata['tl']);
		      list ($t_w,$t_h)=@getimagesize($SkinPath.$sdata['t']);
		      list ($tr_w,$tr_h)=@getimagesize($SkinPath.$sdata['tr']);
		      list ($l_w,$l_h)=@getimagesize($SkinPath.$sdata['l']);
		      list ($r_w,$r_h)=@getimagesize($SkinPath.$sdata['r']);
		      list ($bl_w,$bl_h)=@getimagesize($SkinPath.$sdata['bl']);
		      list ($b_w,$b_h)=@getimagesize($SkinPath.$sdata['b']);
		      list ($br_w,$br_h)=@getimagesize($SkinPath.$sdata['br']);
		      if ($sdata['htl']) {
			      list ($htl_w,$htl_h)=@getimagesize($SkinPath.$sdata['htl']);
			      list ($ht_w,$ht_h)=@getimagesize($SkinPath.$sdata['ht']);
			      list ($htr_w,$htr_h)=@getimagesize($SkinPath.$sdata['htr']);
			      list ($hl_w,$hl_h)=@getimagesize($SkinPath.$sdata['hl']);
			      list ($hr_w,$hr_h)=@getimagesize($SkinPath.$sdata['hr']);
		      }
		
		      if (($sdata['htl'])&&($ShowHeader)) {
			      $element_h_1="
			        <td colspan='3' valign='bottom'>
			        <table cellpadding='0' cellspacing='0' border='0' width='100%'><td ";
			       $element_h_2="><table cellpadding='0' cellspacing='0' width='100%' border='0'>
			          <tr valign='top'>
			          <td width='0%'  background='$SkinURL/$sdata[hl]'><img width='$htl_w' height='$htl_h' src='$SkinURL/$sdata[htl]'/></td>
			          <td width='100%' ".(($sdata['h'])?"background='$SkinURL/$sdata[h]'":"").">
			            <table cellpadding='0' cellspacing='0' width='100%' border='0'>
			              <tr><td background='$SkinURL/$sdata[ht]'><img src='$Spacer' width='1' height='$ht_h'></td></tr>
			              <tr>";
			      $element_h_3="</tr>
			        </table></td>
  			        <td width='0%' background='$SkinURL/$sdata[hr]'><img width='$htr_w' height='$htr_h' src='$SkinURL/$sdata[htr]'/></td>
			        </tr></table></td></tr></table></td>";
		      }
		      $element_1="\$s=\"<td \$columnColor colspan='3'>
		        <table cellpadding='0' cellspacing='0' border='0' width='100%'>
		          <tr><td width='1%'><img src='\$SkinURL/\$sdata[tl]' width='\$tl_w' height='\$tl_h'/></td>
		          <td background='\$SkinURL/\$sdata[t]'><img src='\$Spacer' width='1' height='\$t_h'/></td>
		          <td width='1%'><img src='\$SkinURL/\$sdata[tr]' width='\$tr_w' height='\$tr_h'/></td></tr></table></td>\";";
		      $element_2_part1="\$s=\"<td \$columnColor background='\$SkinURL/\$sdata[l]' width='1%'><img src='\$Spacer' width='\$l_w'/></td><td \$columnColor \$innerclass \$columnStyle \$widthString>\";";
		      $element_2_part2="\$s=\"</td><td width='1%' \$columnColor background='\$SkinURL/\$sdata[r]' ><img src='\$Spacer' width='\$r_w'/></td>\";";
		      $element_3="\$s=\"<td colspan='30' \$columnColor><table cellpadding='0' cellspacing='0' width='100%'>
		        <tr>
		          <td width='0.01%'><img src='\$SkinURL/\$sdata[bl]' width='\$bl_w' height='\$bl_h'/></td>
		          <td width='100%'background='\$SkinURL/\$sdata[b]'><img src='\$Spacer' height='\$br_h' width='1'/></td>
		          <td width='0.01%'><img src='\$SkinURL/\$sdata[br]' width='\$br_w' height='\$br_h'></td>
		        </tr>
		        </table></td>\";";
      	}
      }
    }
  print "\n\n\n";
  if ($sdata['tl']) {
	 	# Draw column design
		print "<table cellpadding='0' cellspacing='0' border='0'".(($Width)?" width='$Width":"").">";
  	if (($sdata['htl'])&& $ShowHeader) {
  		print "<tr valign='bottom'>";
		  for ($i=0;$i<$ColumnCount;$i++) {
		  	print $element_h_1;
		  	$headColor=$arrayHeaderColors[$i];
		  	if ($sdata['headBgColor']) $headColor=$sdata['headBgColor'];
		  	if ($headColor) print " bgcolor='$headColor'";
		  	print $element_h_2;
		  	$ts=" background='$SkinURL/$sdata[h]' style='background-repeat:repeat-x;$headColor";
		    print "<td $ts $innerclass $headStyle>";
		  	JSB_RenderSlot ($SlotNamePrefix.'Head-'.$i,true);
		  	print "<img src='$Spacer' width='1' height='".($htl_h-$ht_h)."'/></td eee>";
		    print $element_h_3;
		  }
  		print "</tr>";
	  }
	  print "<tr>";
	  for ($i=0;$i<$ColumnCount;$i++) {
			$columnColor=$sdata['columnBgColor']; 
	  	if (is_array($arrayColumnColors)) $columnColor=$arrayColumnColors[$i];
	  	if ($columnColor) $columnColor=" bgcolor='$columnColor'";	  	
	    eval ($element_1);
	  	print $s;
	  }
	  print "</tr><tr valign='top'>";
	  for ($i=0;$i<$ColumnCount;$i++) {
	  	$widthString=is_array($widths)?"width='".$widths[$i]."'":"";
			$columnColor=$sdata['columnBgColor']; 
	  	if (is_array($arrayColumnColors)) $columnColor=$arrayColumnColors[$i];
	  	if ($columnColor) $columnColor=" bgcolor='$columnColor'";	  	
	  	eval ($element_2_part1);
	  	print $s;
	    JSB_RenderSlot ($SlotNamePrefix.$i,true);
	    eval ($element_2_part2);
	    print "</td>".$s;
	  }
	  print "</tr><tr>";
	  for ($i=0;$i<$ColumnCount;$i++) {
			$columnColor=$sdata['columnBgColor']; 
	  	if (is_array($arrayColumnColors)) $columnColor=$arrayColumnColors[$i];
	  	if ($columnColor) $columnColor=" bgcolor='$columnColor'";	  	
	  	eval($element_3);
	  	print $s;
	  }
	  print "</tr></table>";
  } else {
  	# No column design
		print "\n\n<table cellpadding='0' cellspacing='0' border='0' ".(($Width)?" width='$Width'":"").">";
		if ($ShowHeader) {
			print "<tr valign='bottom'>";
		  for ($i=0;$i<$ColumnCount;$i++) {
		  	if (is_array($arrayHeaderColors)) {
			  	$headColor=$arrayHeaderColors[$i];
			  	if ($sdata['headBgColor']) $headColor=$sdata['headBgColor'];
			  	if ($headColor) $headColor=" bgcolor='$headColor'";
		  	} else $headColor="";
		  	
		  	print "<td $innerclass $headStyle$headColor>";
		  	JSB_RenderSlot ($SlotNamePrefix.'Head-'.$i,true);
		  	print "</td>";
		  	if (($i!=($ColumnCount-1))&&($sepdata['CSS'])) print "<td></td>";
		  }
		  print "</tr>";
		}
		print "<tr valign='top'>";
	  for ($i=0;$i<$ColumnCount;$i++) {
	  	$widthString=is_array($widths)?"width='".$widths[$i]."'":"";
	  	if (is_array($arrayColumnColors)) {
		  	$columnColor=$arrayColumnColors[$i];
		  	if ($sdata['columnBgColor']) $columnColor=$sdata['columnBgColor'];
		  	if ($columnColor) $columnColor=" bgcolor='$columnColor'";
	  	} else $columnColor="";
	  	print "<td $widthString $columnColor $innerclass $columnStyle>";
	  	JSB_RenderSlot ($SlotNamePrefix.$i,true);
	  	print "</td>";
	  	if (($i!=($ColumnCount-1))&&($sepdata['CSS']))
	      print "<td $Separator_class width='$SeparatorWidth'><img src='$Spacer' width='$SeparatorWidth'></td>";
	  }
	  print "</tr></table>";
  }
  
#    if (($i>0)&&($sepdata['CSS']))
#      print "<td $Separator_class width='$SeparatorWidth'><img src='$Spacer' width='$SeparatorWidth'></td>";


/*
  if ($sdata)
    {
    print "</td></tr></table>";

    if (($sdata['tl'])&&(file_exists($SkinPath.$sdata['tl'])))
      {
      list ($w,$h)=@getimagesize($SkinPath.$sdata['bl']);
      list ($w1,$h1)=@getimagesize($SkinPath.$sdata['br']);
      print "<td background='$SkinURL/$sdata[r]'></td></tr>
        <tr valign='top'><td><img width='$w' height='$h' src='$SkinURL/$sdata[bl]'/></td>
        <td background='$SkinURL/$sdata[b]'></td><td><img width='$w1' height='$h1' src='$SkinURL/$sdata[br]'/></td></tr>
        </table>";
      }
    }
  else
    {
    print "</tr></table>";
    }
    */
    print "\n\n";

  }
}
