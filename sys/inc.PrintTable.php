<?
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }
function PrintTableEditor (&$q,$args)
{
	#        &$ThisObject - object that will be default for calling hooks
	#        $Fields,
	#				 $Action
	#        $Modal           = 1/0 - 
	#        $ModalOkOnOk     = 1/0 : 0 - reloadOnOk from called modal window, 1 - modalOkOnOk
	#        $FormName,       - if not present table generates random name
	#        $FieldHrefs
	#        $FieldHrefsTarget
	#        $SubactionList   - dropdown list near button [Ok]
	#        $SubactionDefault- default subaction value
	#        $SubactionNulCaption
	#        $ShowCheckers    - boolean : enable/disable checkers in rows
	#        $ShowDelete      - boolean : delete button at bottom
	#        $FieldHooks      - array(Field1=>callback1,Field2=>callback2,..) callback functions called in table cells of corresponding fields
	#        $FieldHookArgs   - passing arguments to callback functions called in table cells
	#        $HideSubmit      - 
	#        $ShowCancel      - put button [Cancel] in the bottom
	#        $Width
	#        $TableStyle      - int=0,1,2,... number of table style
	#        $Buttons         - array(Button1Action=>Button1Caption, ...) (alternative to $SubactionList)
	#        $ColAligns       - array(Col1Align,Col2Align,...)
	#        $ColWidths       - array(Col1Width,Col2Width,...)
	#        $HiddenFields    - filter form and table form hidden fields
	#        $ButtonEdit      - edit row button array(Caption,ModalWindowAction,KeyName)
	#        $ButtonAdd       - add button at bottom
	#        $Pages           - array(PageCount,PageNo) or array(RowCount,RowsPerPage,PageNo)
	#        $PutKeyFieldsList- boolean - put hidden field with string containing comma-sep. ID of each shown rows
	#        $FieldTypes      - array of editfield types used to generate input fields (droplist,int,float,string)
	#        $NoForm
	#        $OnRowFilter
	#        $OnLastRow       - callback (&$row)
	#        $OnBeforeStart
	#        $OnGetCellStyle  - callback function that returns 'Red' or "style='....'"


	$__=&$GLOBALS['_STRINGS']['_'];
	global $_THEME;
	$ButtonAddString="";
	extract ($args);

	if ($ButtonAdd)
	{
		$Caption=isset($ButtonAdd['Caption'])?$ButtonAdd['Caption']:$__['CAPTION_ADD'];
		$OnClick=$ButtonAdd['OnClick'];
		if ((!$OnClick)&&($ButtonAdd['ModalWindowURL']))
		{
			$s="";
			if ($ButtonAdd['Width']) $s.=",w:".intval($ButtonAdd['Width']);
			if ($ButtonAdd['Height']) $s.=",h:".intval($ButtonAdd['Height']);
		$OnClick="W.openModal({url:'$ButtonAdd[ModalWindowURL]',reloadOnOk:1$s})";
		$Buttons[]=array(Kind=>'add',Caption=>$Caption,OnClick=>$OnClick);
		}
		#$ButtonAddString=$_ENV->PutButton(array(ToString=>1,Kind=>'add',Caption=>$Caption,OnClick=>$OnClick));
		
	}
	$BgColor_Hovered='#fff0e0';
	$BgColor_Checked='#ffffe0';
	$FormName="tabform".rand(1,100);

	if (!$SelectActionCaption) $SelectActionCaption=$__['CAPTION_SELECTACTION'];

	$ts=false;
	if (!$TableStyle) $TableStyle=0;
	if ($_THEME) $ts=$_THEME['TableStyles'][$TableStyle];

	if (!$ts) {$ts=array(Even=>"td",Odd=>"td",Top=>"th",Table=>"table"); }
	else
	{
		if ($ts['BgColor_Hovered']) $BgColor_Hovered=$ts['BgColor_Hovered'];
		if ($ts['BgColor_Checked']) $BgColor_Checked=$ts['BgColor_Checked'];
	}
	if ($FieldTypes)
	{
		foreach ($FieldTypes as $FieldTypeName=>$FieldType)
		{
			if (strtolower($FieldType['Type'])=='droplist')
			{
				$qr=$FieldType['Recordset'];
				if ($qr)
				{
					$_ENV->PutValueSet(array(
					ValueSetName=>$FieldTypeName,
					Recordset=>&$qr,
					CaptionField=>$FieldType['CaptionField']));
				}
				elseif (isset($FieldType['Values']))
				{
					$_ENV->PutValueSet(array(
					ValueSetName=>$FieldTypeName,
					Values=>$FieldType['Values']));
				}
			}
		}
	}

	if (!$_ENV->TableScriptInited)
	{
		$_ENV->TableScriptInited=1;
  ?>
  <script>
  function tabrow_mouseOver(el)
  {
  	var e;
  	for (var i in el.childNodes)
  	{
  		e=el.childNodes[i];
  		if (e.nodeName=='TD')
  		{
  			if (!e.style._inited) {e.style._initbg=e.style.backgroundColor; e.style._inited=1;}
  			if (!e.style.checked) e.style.backgroundColor="<? print $BgColor_Hovered ?>";
  		}
  	}
  }
  function tabrow_mouseOut(el)
  {
  	for (var i in el.childNodes)
  	{
  		e=el.childNodes[i];
  		if (e.nodeName=='TD')
  		{
  			if (!e.style.checked) e.style.backgroundColor=e.style._initbg;
  		}
  	}
  }

  function tab_CheckAll(checker,form)
  {
  	// if (checker.checked)
  	var s="";
  	for (var el in form.elements)
  	{
  		if (el.substr(0,6)=='check[') {form.elements(el).checked=!checker.checked; form.elements(el).click();}
  	}
  }
  function tabrow_checked(checker)
  {
  	var el=checker.parentElement.parentElement;
  	if (el.tagName=='TR')
  	{

  		for (var i in el.childNodes)
  		{
  			e=el.childNodes[i];
  			if (e.nodeName=='TD')
  			{
  				if (!e.style._inited) {e.style._initbg=e.style.backgroundColor; e.style._inited=1;}

  				if (checker.checked)
  				{
  					e.style.backgroundColor="<? print $BgColor_Checked ?>";
  					e.style.checked=1;
  				}
  				else
  				{
  					e.style.backgroundColor=e.style._initbg;
  					e.style.checked=0;
  				}
  			}
  		}
  	}
  }

  </script>
  <?
	}
	if (!$Width) {$Width="100%";}
	print "<table width='$Width' cellpadding=5 border='0'><tr><td class='bgup'>";

	if (($Filters)||($Pages))
	{
		print "<table width='100%' border='0'><form method='get' id='f$FormName' name='f$FormName'><tr valign='bottom'><td>";

		if ($Filters)
		{
			foreach ($Filters as $Filter)
			{
				$Variable=$Filter['Variable'];
				$Value=$Filter['Value'];
				$fstr="";
				switch (strtolower($Filter['Type']))
				{
					case 'radio':
						foreach ($Filter['Values'] as $k=>$v)
						{
							$oc=($FiltersAutoReload)?" onClick='f$FormName.submit();' ":"";
							$fstr.="<tr><td><input type='radio' $oc name='$Variable' ".(("$Value"=="$k")?"checked":"")." value='$k'/></td><td>$v<td></tr>";
						}
						if ($fstr) $fstr="<b>$Filter[Caption]</b><table border=0 cellpadding=0 cellspacing=0>$fstr</table>";
						break;
				}

				print $fstr;
			}
			if (!$FiltersAutoReload)
			{
				$_ENV->PutButton(array(Action=>'submit'));
			}
		}

		print "</td><td align='right'>";
		if ($Pages)
		{
			$PageCount=intval($Pages['PageCount']);
			if ((!$PageCount)&&($Pages['RowCount']))
			{
				$Pages['RowCount']=intval($Pages['RowCount']);
				$Pages['RowsPerPage']=intval($Pages['RowsPerPage']);
				if (!$Pages['RowsPerPage']) $Pages['RowsPerPage']=20;
				$PageCount=ceil($Pages['RowCount']/$Pages['RowsPerPage']);
			}
			$PageNo=intval($Pages['PageNo']);
			if (!$PageNo) $PageNo=intval($_GET['PageNo']);
			if (!$PageNo) $PageNo=1;
			if (($PageNo>$PageCount)||($PageNo==-1)) $PageNo=$PageCount;
			if ($PageCount) $_ENV->PutPages(array(PageCount=>$PageCount,PageNo=>$PageNo,ToForm=>"f$FormName"));
		}
		if ($HiddenFields)
		{
			foreach ($HiddenFields as $k=>$v) {print "<input type='hidden' name='$k' value='$v'>";}
		}
		print "</td></tr></form></table>";
	}


	if ($q)
	{
		list ($t,$c)=get_css_pair($ts['Table'],"table");
		if (!$NoForm)
		{
			$_ENV->OpenForm(array(
			Name=>$FormName,
			Modal=>$Modal,
			ModalOkOnOk=>$ModalOkOnOk,
			ReloadOnOk=>$ReloadOnOk,
			SubactionList=>$SubactionList,
			ShowDelete=>$ShowDelete,
			Action=>$Action,
			Buttons=>$Buttons,
			HideSubmit=>$HideSubmit,
			Style=>"clear"));
			if ($HiddenFields) foreach ($HiddenFields as $k=>$v) {print "\n<input type='hidden' name='$k' value='$v'>";}
		}
		if ($OnBeforeStart) {$OnBeforeStart($ThisObject);}
		$ts=$_ENV->ParseTableStyle($TableStyle);
		
		print "<table width='100%' border='0'><tr>"; # open rows table
		if ($ShowCheckers) print "<$ts[tt]$ts[ct] width='1%'><input type='checkbox' value='1' name='check_all' onClick='tab_CheckAll(this,$FormName)'></$ts[tt]>";
		if ($ButtonEdit) print "<$ts[tt]$ts[ct]></$ts[tt]>";
		foreach ($Fields as $fieldname=>$fieldcaption)
		{
			$w=""; if ($ColWidths) {$w=$ColWidths[$fieldname];}
			if ($w) $w=" width='$w'";
			print "<$ts[tt]$ts[ct]$w>$fieldcaption</$ts[tt]>";
		}
		print "</tr>";

		$rowno=0;
		
/*		list($te,$ce)=get_css_pair($ts['Even'],"td");
		list($to,$co)=get_css_pair($ts['Odd'] ,"td");
		$tre=$te; $cre=$ce; $tro=$to; $cro=$co;
		if ($ts['RightEven']) {list($tre,$cre)=get_css_pair($ts['RightEven'],"td");}
		if ($ts['RightOdd'] ) {list($tro,$cro)=get_css_pair($ts['RightOdd'] ,"td");}
		$tle=$te; $cle=$ce; $tlo=$to; $clo=$co;
		if ($ts['LeftEven']) {list($tle,$cle)=get_css_pair($ts['LeftEven'],"td");}
		if ($ts['LeftOdd'] ) {list($tlo,$clo)=get_css_pair($ts['LeftOdd'] ,"td");}
*/

		if (!is_array($q->Rows))
		{
			return;
		}
		foreach ($q->Rows as $RowID=>$row)
		{
#			print "[[$RowID]]";
			if ($OnRowFilter)
			{
				if (!$ThisObject->$OnRowFilter($row)) {continue;}
			}
			if ($PutKeyFieldsList) $Keys.=(($Keys)?",":"").$RowID;
			if ($rowno%2) {$t=$ts['tlo']; $c=$ts['clo'];} else {$t=$ts['tle']; $c=$ts['cle'];}
			print "\n<tr valign='top' onMouseOver='tabrow_mouseOver(this)' onMouseOut='tabrow_mouseOut(this)'>";
			if ($ShowCheckers)
			{
				print "<$t$c width='1%'><input type='checkbox' name='check[$RowID]' value=1 onClick='tabrow_checked(this)'></$t>";
			}
			if ($ButtonEdit)
			{
				print "<$t$c>";
				$c=$ButtonEdit['Caption'];
				if (!$c) $c=$__['CAPTION_EDIT'];
				$OnClick=$ButtonEdit['OnClick'];
				if (!$OnClick)
				{
					$eargs=array($ButtonEdit['KeyName']=>$RowID);
					if ($HiddenFields)
					{
						foreach ($HiddenFields as $k=>$v) {$eargs[$k]=$v;}
					}
					if ($ButtonEdit['ModalWindowAction']) {
						$url=ActionURL($ButtonEdit['ModalWindowAction'],$eargs);
						$s="";
						if ($ButtonEdit['Width']) $s.=",w:".intval($ButtonEdit['Width']);
						if ($ButtonEdit['Height']) $s.=",h:".intval($ButtonEdit['Height']);
						$OnClick="W.openModal({url:'$url',reloadOnOk:1$s})";
					} elseif ($ButtonEdit['Action']) {
						$url=ActionURL($ButtonEdit['Action'],$eargs);
						$OnClick="href='$url'";
					}
				}
				print "<a href='javascript:;' onClick=\"$OnClick\">$c</a></$t>";
				#        $_ENV->PutButton(array(Caption=>$c,Kind=>'view',OnClick=>$OnClick));
			}
			$ColIndex=0; $ColCount=count($Fields);
			foreach ($Fields as $fieldname=>$fieldcaption)
			{
				if ($ColIndex==($ColCount-1))  { 
					if ($rowno%2) {$t=$ts['tro']; $c=$ts['cro'];} else {$t=$ts['tre']; $c=$ts['cre'];}
				}	elseif (!$ColIndex) {
					if ($rowno%2) {$t=$ts['tlo']; $c=$ts['clo'];} else {$t=$ts['tle']; $c=$ts['cle'];}
				} else {
					if ($rowno%2) {$t=$ts['to']; $c=$ts['co'];} else {$t=$ts['te']; $c=$ts['ce'];}
				}
				

				$addstyle="";
				if (isset($OnGetCellStyle))
				{
					$st=$ThisObject->$OnGetCellStyle($RowID,$q->Rows[$RowID],$fieldname,$FieldHookArgs);
					switch ($st)
					{
						case 'Red': if ($ts['Red'] ) {list($t,$c)=get_css_pair($ts['Red'] ,"td"); } else {$c=" style='color:red;'";} break;
						case 'Highlight': 
							if ($ts['HiEven'] ) {
								if ($rowno%2) {$t=$ts['tho']; $c=$ts['cho'];} else {$t=$ts['the']; $c=$ts['che'];}
							} else {$c="style='background-color:#ffff80 ";}
							break;
						default: $addstyle=$st;
					}
				};
				$a=""; if ($ColAligns) {$a=$ColAligns[$fieldname]; if ($a) {$a=" align='$a'";}}
				if (isset($FieldHooks[$fieldname]))
				{
					print "<$t$c$a $addstyle>";
					if (is_object($ThisObject))
					{
						$ThisObject->$FieldHooks[$fieldname]($RowID,$q->Rows[$RowID],$fieldname,$FieldHookArgs);
					}
					else
					{
						$FieldHooks[$fieldname]($RowID,$q->Rows[$RowID],$fieldname,$FieldHookArgs);
					}
					print "</$t>";
				}
				else
				{
					$s=$q->Rows[$RowID]->$fieldname;
					$Href=$OnClick=$NullCaption="";
					
					if (isset($FieldTypes[$fieldname]))
					{
						$fta=false;
						$ft=$FieldTypes[$fieldname];
						if (is_array($ft))
						{
							$fta=$ft;
							$ft=$ft['Type'];
						}
						else
						{
							list($ft,$p1,$p2,$p3)=explode (":",$ft,4);
						}

						if ($fta['Action']) {
							$eargs=array($fta['KeyName']=>$RowID);
							if ($HiddenFields) foreach ($HiddenFields as $k=>$v) {$eargs[$k]=$v;}
							$Href=ActionURL($fta['Action'],$eargs);
							$ModalArgs=",reloadOnOk:1";
							if ($fta['Modal']) {
								if ($fta['Width']) $ModalArgs.=",w:".intval($fta['Width']);
								if ($fta['Height']) $ModalArgs.=",h:".intval($fta['Height']);
								$OnClick="W.openModal({url:'$Href'$ModalArgs})"; $Href="";
							}
						}
						if ($fta['NullCaption']) $NullCaption=$fta['NullCaption'];
						
						$al="";
						switch(strtolower($ft))
						{
							case 'langstring':
								$s=langstr_get($s);
								break;
							case 'float':
								$s=float_to_str($s,$p1);
								break;
							case 'checkbox':
								$s="<center><input name='$fieldname"."[$RowID]' type='checkbox' value=1 ".(($s)?"checked":"")."/></center>";
								break;

							case 'inputfloat':
								$s=float_to_str($s,$p1);
								$p1=$p2;
								if (!$p1) $p1='10';
								$s=$_ENV->PutFormField(array(ToString=>1,Style=>'clear',Size=>$p1,Type=>"float",Name=>$fieldname."[$RowID]",Value=>$s));
								break;
							case 'inputint':
								if (!$p1) $p1='8';
								#$al="style='text-align:right'";
								$s=$_ENV->PutFormField(array(ToString=>1,Style=>'clear',Size=>$p1,Type=>"int",Name=>$fieldname."[$RowID]",Value=>$s));
								break;
							case 'inputstring':
								$maxlength=$p1;
								if (!$maxlength) $maxlength=255;
								$size=$maxlength;
								if ($size>30) $size=30;
								$s="<input type='text' style='width:100%' size='$size' maxlength='$maxlength' class='inputarea' name='$fieldname"."[$RowID]' $al value='$s'/>";
								break;
							case 'lookup':
								$ql=&$fta['Recordset'];
								$row=$ql->Rows[$s];
								$lc=$fta['LookupCaption'];
								$s=langstr_get($row->$lc);
								break;
							case 'lookupvalue':
								if ($s) {$v=$fta['Values'][$s];}
								$s=($v)?$v:"[$s]";
								break;
							case 'shortdate':
								$s=format_date('shortdate',$s);
								break;
							case 'normaldate':
								$s=format_date('normaldate',$s);
								break;
							case 'numericdate':
								$s=format_date('numericdate',$s);
								break;
							case 'time':
								$s=format_date('hh:mm:ss',$s);
								break;
							case 'datetime':
								$s=format_date('shortdate hh:mm',$s);
								break;
							case 'inputmodal':
								$args=array(
								Type=>'inputmodal',
								ToString=>1,
								Value=>$s,
								Style=>'clear',
								ModalCall=>$fta['ModalCall'],
								Required=>$fta['Required'],
								NullCaption=>$fta['NullCaption'],
								Name=>"$fieldname"."[$RowID]");
								if ($fta['InitCall']) $args['InitCall']=$fta['InitCall'];
								$s=$_ENV->PutFormField($args);
								break;
							case 'droplist':
								$vset=$fta['ValueSetName'];
								if (!$vset) $vset=$fieldname;
								$s=$_ENV->PutFormField(array(
								Type=>'droplist',
								ToString=>1,
								ValueSetName=>$vset,
								Value=>$s,
								Style=>'clear',
								Size=>$fta['Size'],
								Required=>$fta['Required'],
								NullCaption=>$fta['NullCaption'],
								Name=>"$fieldname"."[$RowID]"));
								break;
						}
						if (!$s && $NullCaption) $s=$NullCaption;
					}
					else
					{
						if ($q->FieldType[$fieldname]=='float') {
							$s=str_replace (' ','&nbsp;',number_format($s,2,'.',' '));
							$c.=" align='right'";
						}
						else $s=langstr_get($s);
						if (!$s && $NullCaption) $s=$NullCaption;
						
						if ($FieldHrefs[$fieldname])
						{
							$tg=$FieldHrefsTarget[$fieldname];
							if ($tg) {$tg=" target='$tg'";}
							if (!$s) {$s=$Fields[$fieldname];}
							$s="<a href='".$FieldHrefs[$fieldname]."$RowID' $tg>$s</a>";
						}
					}

					if ($Href || $OnClick) {
						if ($OnClick) {$s="<a href='javascript:;' onClick=\"$OnClick\">$s</a>";}
						else {$s="<a href='$Href'>$s</a>";}
					}
					print "<$t$c$a $addstyle>$s</$t>";
				}
				$ColIndex++;
			}
			print "</tr>";
			$rowno++;
		}

		if ($OnLastRow) {$OnLastRow($ThisObject,$ts['tb'],$ts['cb']);}

/*		
		print "<tr><$tb$cb colspan='50'><table width='100%' border=2 cellpadding=0 cellspacing=0><tr><td>";
		if ($ShowDelete)
		{
			$_ENV->PutButton(array(Kind=>'delete',OnClick=>"$FormName.action.value='delete'; submitTableForm($FormName,$Modal);"));
		}
		print "</td>";

		if (($Buttons)||($SubactionList)||($ShowOk))
		{
			print "<td align='right'><table><tr valign='bottom'><td align='left'>";
			if ($Buttons)
			{
				print "<input type='hidden' name='subaction'>";
				foreach ($Buttons as $FormAction=>$Button)
				{
					if (is_array($Button)) {
						if (!$Button['OnClick']) $Button['OnClick']="$FormName.action.value='$FormAction'; submitTableForm($FormName,$Modal);";
						$_ENV->PutButton($Button);
					} else {
						$_ENV->PutButton(array(Caption=>$Button,OnClick=>"$FormName.action.value='$FormAction'; submitTableForm($FormName,$Modal);"));
					}
				}
			}
			elseif ($SubactionList)
			{
				$_ENV->PutValueSet(array(
				ValueSetName=>$FormName.'_subaction',
				Values=>$SubactionList));
				$_ENV->PutFormField(array(Type=>"droplist",Value=>$SubactionDefault,Style=>'vertical',
				Name=>'subaction',Caption=>$SelectActionCaption,Size=>30,ValueSetName=>$FormName.'_subaction'));
			}
			print "</td><td>";
			if ($ShowOk)
			{
				$_ENV->PutButton(array(Kind=>'ok',Name=>'submitbtn',OnClick=>"$FormName.action.value='ok'; submitTableForm($FormName,$Modal);"));
			}
			if ($ShowCancel)
			{
				$_ENV->PutButton('cancel');
			}
			print "</td></tr></table>";
			print "</td>";
		}
		print "</tr></table>zz"; # close buttons table
		*/
		print "</table>"; # close rows table ... </td></tr></table> still left

		if ($Pages['RowCount'] || $ButtonAdd)
		{
			print "<table width='100%'><tr><td class='tiny'>";
			if ($Pages['RowCount'])
			{
				print "Total:$Pages[RowCount] records<br/>";
			}
			print "$ButtonAddString</td></tr></table>";
		}

		if ($PutKeyFieldsList) print "<input type='hidden' name='Keys' value='$Keys'>";
		if (!$NoForm) $_ENV->CloseForm();
		print "</td></tr></table>";
		
	} # if ($q)
	else {print "</td></tr></table>";}
}


?>
