<?
function core_PutFormOpen(&$Form)
  {
  global $cfg,$_SYSSKIN_NAME,$_THEME_NAME,$_ENVIRONMENT,$_THEME;
  $__=$GLOBALS['_STRINGS']['_'];
#  extract ($args);
  # Name       - form name
  # ShowDelete
  # ShowCancel - 1/0 - show cancel button
  # HideSubmit - 1/0 - hide or show Ok button
  # Action     - action url
  # Align      = left/center - horisonatl alignment of form content
  # Modal      = 1/0 - opens modal window to upload form data
  # AttrCSS
  # ValCSS
  # Enctype    = 'multipart/form-data' for files
  # ModalOkOnOk
  # OnSubmit
  # SubmitCaption
  # Width
  # Style = "??/vertical/clear
  # ButtonStyle
  # SubactionDefault
  # SubactionNulCaption
  # SubactionList
  # Title
  
  if ($_ENV->Form){
  	return;
  	#print_developer_error("Повторное открытие формы внутри другой");
  }

  if (!$Form['Name']) $Form['Name']='form'.rand(100,1000);
  if (!$Form['AttrCSS']) $Form['AttrCSS']="td.cle";
  if (!$Form['ValCSS']) $Form['ValCSS']="td.ce";

  if (!$_ENV->Form) 
    {
    ?>
  <script>
  var fChecker_TimeOut=0;
  var currentForm=""; // for replace
  var identchars='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-';
  var numchars='0123456789,.-eE ';
  
  function fChecker_Changed (formname,id,eventno)
    {
    // eventno = 1-onBlur 2-onKeyUp 3-onChange
    var fields=false,v,v2,c,i;
    try {fields=eval ("fChecker_f_"+formname);} catch(e){};
    if (!fields) {alert ("Form '"+formname+"' has no fields"); return;}

    var fdef=fields[id];
    if (!fdef) {alert("Detected illegal field "+id); return;}
    var type=fdef.t;
    var hot=type.match(/identifier|int|float|dim/gi);
    var usetimeout=type.match(/dim|email|enum.checks|int.bt/gi);
    try {if (fdef.r) usetimeout=true;} catch(e){}

    try{v2=v=document.getElementById("lfv_"+formname+id).value; }
      catch(e){ alert ("Field not found: 'lfv_"+formname+id+"'"); return;}
    
    if (hot)
      {
      switch (type)
        {
        case 'int.bt': case 'int.lt': case 'int.gt':
          var max=min="";
          if ((type=='int.bt')||(type=='int.gt')) {
          	var f=document.getElementById("lfv_"+formname+id+"_min");
          	if (!f) {alert ('Unable to find field ['+"lfv_"+formname+id+"_min");break;}
          	min0=f.value;
          	var min=Int(min0);
            if (min0!=min) f.value=min;
          }
          if ((type=='int.bt')||(type=='int.lt')) {
          	var f=document.getElementById("lfv_"+formname+id+"_max");
          	if (!f) {alert ('Unable to find field ['+"lfv_"+formname+id+"_max");break;}
          	var max0=f.value;
          	var max=Int(max0)
          	//if (type=='int.bt') if (max<min) max=min;
            if (max0!=max) f.value=max;
          }
          
          if ((min=="")&&(max==""))
            {
            v2="";
            }
          else
            {
            v2=min+":"+max;
            }
          break;
        
        case 'dim':
          var w0=document.getElementById("w_"+formname+id).value;
          var w=Int(w0);
          var h0=document.getElementById("h_"+formname+id).value;
          var h=Int(h0);
          if ((!w)&&(!h))
            {
            w=""; h=""; v2="";
            }
          else
            {
            v2=w+"x"+h;
            }
          if (w0!=w) document.getElementById("w_"+formname+id).value=w;
          if (h0!=h) document.getElementById("h_"+formname+id).value=h;
          break;
        case 'int': v2=parseInt(v); if (!v2) v2=0; break;
        case 'float':
          v2=''; for (i=0;i<v.length;i++){c=v.charAt(i);if(numchars.indexOf(c)!=-1){v2+=c;}}
          break;
        case 'identifier':
          v2=''; for (i=0;i<v.length;i++){c=v.charAt(i);if(identchars.indexOf(c)!=-1){v2+=c;}}
          break;
        }
      }

    if (v2!=v)
      {
      document.getElementById("lfv_"+formname+id).value=v2;
      }
    if (usetimeout)
      {
      if (fChecker_TimeOut) {window.clearTimeout(fChecker_TimeOut);}
      fChecker_TimeOut=window.setTimeout(" fChecker_CheckFields('"+formname+"');",600);
      }
    }

  function fChecker_CheckFields(formname)
    {
    currentForm=formname;
    var lfc,bad=false,fields=false,showred=true,fobj;
    try {fields=eval ("fChecker_f_"+formname);} catch(e){};
    if (!fields) {alert ("Form '"+formname+"' has no fields"); return;}
    var fdef,id,makered,makewhite;
    for (id in fields) {
      makered=false;makewhite=false;
      fobj=document.getElementById("lfv_"+formname+id);
      fdef=fields[id];
      try {
      	v=fobj.value;
        if (fdef.r) {makewhite=true; }
        if (fdef.r && ((!v)||(v==0))) {bad=true; makered=true;makewhite=false;}

        if (fdef.t=='email') {
          if (v!='') {
            var re=new RegExp("^[\\w-_\.]*[\\w-_\.]\@[\\w]\.+[\\w]+[\\w]$");
            if (!re.test(v)) {bad=true;makered=true;makewhite=false;} else {makewhite=true;}
          }
        }
        
        lfc=document.getElementById("lfc_"+formname+id);
        if (lfc!=undefined) fobj=lfc;
        lfc=document.getElementById("lfi_"+formname+id);
        if (lfc!=undefined) fobj=lfc;
        
        if (makered) {fobj.style.borderColor='#ff0000';}
        if (makewhite) {fobj.style.borderColor='#808080';}
      } catch(e){}
    }
    
    
    try {
    	var s,f,groupDivId,triggers=eval ("fChecker_t_"+formname);
    	if (triggers!==undefined) {
    		for (groupDivId in triggers) {
    			s=triggers[groupDivId];
    			s=s.replace (/\{([^}]*?)}/g,function (s,p1,p2) {
    				var r="?";
    				if (p1.indexOf(":")!=-1) {
    					var p=p1.split(":");
    					f=document.getElementById("check_"+currentForm+"Field"+p[0]+"_"+p[1]);
    					if (!f) {alert ("no field "+"check_"+currentForm+"Field"+p[0]+"_"+p[1]);}
    					r=(f.checked)?1:0;
    				} else {
    					f=document.getElementById("lfv_"+currentForm+"Field"+p1);
    					if (!f) {alert ("no field "+"lfv_"+currentForm+"Field"+p1);}
    					r=(f.value)?1:0;
    				}
    				return r;
    			});
    			var triggered=eval("("+s+")");
  				groupDiv=document.getElementById(groupDivId);
  				if (!groupDiv) {alert ("group not found "+groupDivId);}
  				if (groupDiv) groupDiv.style.display=(triggered)?"block":"none";
    		}
    	}
    } catch(e){};
    try {document.getElementById(formname+"_submitbtn").disabled=bad;} catch(e){}
    }
    
  function fChecker_EnumCheckClick (formName,id,value,checkall) {
  	var valfield=document.getElementById("lfv_"+formName+id);
  	var ids=document.getElementById("all_"+formName+id);

  	var newValue="";
  	var list=ids.value.split(','),valueid,cf;
  	for (var i in list) {
  		valueid=list[i];
  		cf=document.getElementById('check_'+formName+id+"_"+valueid);
  		if (value==0) {cf.checked=checkall;}
  		if (cf.checked) newValue+=((newValue)?",":"")+valueid;
  	}
  	valfield.value=newValue;
  	fChecker_Changed(formName,id,3);
  }
    
  </script>
    <?
    }
  $Width=$Form['Width'];
  $Align=$Form['Align'];
  if ($Align) {$Align=" style='text-align:$Align'";}
  if ($Form['Enctype']) $Enctype="enctype='$Form[Enctype]'";
  
  if ($Form['Modal'] || $Form['ModalOkOnOk'] || $Form['ReloadOnOk'])
    {
    $whatonok=($Form['ModalOkOnOk'])?"modalOkOnOk" : "reloadOnOk";
    $Form['_ButtonSubmitAction_']="$Form[Name].target=W.openModal({"."$whatonok:1}); $Form[Name].submit();";
    print "<form name='$Form[Name]' id='$Form[Name]' 
      onSubmit='".(($OnSubmit)?"v=$OnSubmit; if (!v) return false;":"")." var b=document.getElementById(\"$Form[Name]_submitbtn\"); if (b) if (b.disabled) {alert(\"Fill up the form\");return false;} this.target=W.openModal({"."$whatonok:1,Title:\"$__[TITLE_SAVING]\"})'
      $Enctype method='post' action='$Form[Action]' >";
    } else {
      $Form['_ButtonSubmitAction_']="$Form[Name].submit();";
    	print "<form name='$Form[Name]' id='$Form[Name]' ".(($Form['OnSubmit'])?"onSubmit='return $Form[OnSubmit]'":"")." 
    	  $Enctype method='post' action='$Form[Action]'>";
    }
  switch ($Form['Style']) {
  	case 'clear': break;
  	case 'vertical': 
  	  print "<table border='0' width='$Width' cellspacing='0' cellpadding='1'><tr><td class='bgdown' $Align>
  	    $Form[Title]<table width='100%' border='0' cellspacing='1' cellpadding='5'><tr><td class='bgup'>"; break;
  	default: print "<table border='0' width='$Width' cellspacing='0' cellpadding='0'>
  	<tr><td class='bgdown' $Align>$Form[Title]<table width='100%' border='0' cellspacing='1' cellpadding='5'>";
    }
  
  $_ENV->Form=$Form;
  }

function core_PutFormClose($args=false)
  {
  $Form=&$_ENV->Form;
  
  if (!$Form)
    {
    print_developer_warning("You should use OpenForm() before CloseForm()");
    return false;
    }
  if ($args) {foreach($args as $k=>$v) $Form[$k]=$v;}
  
  switch ($Form['Style']) {
  	case 'clear': break;
  	case 'vertical': print "</td></tr></table><tr><td class='bgtop'>"; break;
  	default: print "</table></tr><tr><td class='bgtop'>";
    }
   
#  if (!$Form['WithoutTable']) print "</table></tr><tr><td class='bgtop'>";


  if (isset($Form['Buttons']) || $Form['ShowCancel'] || (!$Form['HideSubmit']) || $Form['ShowDelete'])
    {
    print "<table width='100%' border=0><tr>";
    if ($Form['Buttons'] || $Form['ShowDelete'])
      {
    	print "<td>\n\n\n";
			if ($Form['ShowDelete'])
			{
				$_ENV->PutButton(array(
				  Kind=>'delete',ButtonStyle=>$Form['ButtonStyle'],
				  OnClick=>"$Form[Name].action.value='delete'; $Form[_ButtonSubmitAction_]")
				  );
			}
    	print "</td>";
      }
    print "<td align='right'>";
    
    print "<table><tr><td>";
    if ($Form['Buttons']) foreach ($Form['Buttons'] as $Button)
      {
      	if ($Button['FormAction']) {
      		$Button['OnClick']="$Form[Name].action.value='$Button[FormAction]';$Form[_ButtonSubmitAction_]";
      	}
      $_ENV->PutButton($Button);
      print "</td><td>";
      }
    
		if ($Form['SubactionList']){
			$_ENV->PutValueSet(array(
			ValueSetName=>$Form['Name'].'_subaction',
			Values=>$Form['SubactionList']));

			$_ENV->PutFormField(array(
			Type=>'droplist',
			Style=>'clear',
			ValueSetName=>$Form['Name'].'_subaction',
			NullCaption=>$Form['SubactionNulCaption'],
			Size=>30,
			Name=>'subaction'));
			print "</td><td>";
		}
		
		
		
		if (!$Form['HideSubmit']) {
			$_ENV->PutButton(array(
		  Action=>'submit',
		  Style=>$Form['ButtonStyle'],
		  ID=>"$Form[Name]_submitbtn",
		  Caption=>$Form['SubmitCaption'],
		  ));
		}
		
    if ($Form['ShowCancel']) $_ENV->PutButton(array(Action=>'cancel'));
    print "</td></tr></table></td></tr></table>";
    }

  print "<input type='hidden' name='action' value='ok'>";
  #print "<input type='hidden' name='formaction' value='ok'>";
  print "<input type='hidden' name='form' value='$Form[Name]'>";
  
  switch ($Form['Style']) {
  	case 'clear': break;
  	case 'vertical': print "</td></tr></table>"; break;
  	default: print "</td></tr></table>";
    }
  print "</form>";
  $Fields=&$Form['Fields'];
  $Triggers=&$Form['Triggers'];
  if ($Fields)
    {
    print "<script>";
    $s="";
    foreach ($Fields as $fname=>$data) $s.=(($s)?",":"")."'$fname':{"."$data}";
    print "\nvar fChecker_f_$Form[Name]={"."$s};";
    
    if ($Triggers) {
    	$s="";
    	foreach ($Triggers as $fname=>$data) {
    		$s.=(($s)?",":"")."'$fname':\"$data\"\n";
    	}
	    print "\nvar fChecker_t_$Form[Name]={"."$s};";
    }
    print "\nwindow.setTimeout(\"fChecker_CheckFields('$Form[Name]')\",300);</script>";
    print "\n</script>";
    }
  $_ENV->Form=false;
  }

function core_PutFormField(&$args)
  {
  extract ($args);
  # Type     = string/int/droplist/inputmodal/langstring/langtext/checkbox/file
  # Name
  # Caption
  # Notice
  # Required
  # MaxLength
  # Size
  # Rows (for langtext)
  # Style (??/clear/vertical)
  # CellCSS
  # Action (for type 'call')
  # Value
  # Decimals
  # DefaultValue

  global $cfg,$_SYSSKIN_NAME,$_THEME_NAME,$_ENVIRONMENT,$_THEME;
  $Form=&$_ENV->Form;
  if (!$Form)
    {
    print_developer_warning("You should use OpenForm() before PutFormField() or use PutDropDown() without a form");
    return false;
    }

  $__=&$GLOBALS['_STRINGS']['_'];
  $result="";
  $Type=strtolower($Type);
  $id=str_replace(array('[',']',"/",":"),array('_','','_',''),$Name);
  $idName="lfv_$Form[Name]$id";
  $idCaption="lfc_$Form[Name]$id";
  if (($Type=='int')||($Type=='float')) {if (!$Size) $Size=10; if (!$MaxLength) $MaxLength=11;}
  if ($Type=='float') $Value=float_to_str($Value,$Decimals);

  if ($OnChange) $OnChange="$OnChange;"; else $OnChange="";
  $OnChange.="fChecker_Changed(\"$Form[Name]\",\"$id\",3) ";
  $JScriptBlurKeyUpChange=" onKeyUp='fChecker_Changed(\"$Form[Name]\",\"$id\",2)'
    onChange='$OnChange' ";

  if (($Type!='hidden')&&($Type!='checkbox'))
    {
    $s="t:'$Type'";
    if ($Required) $s.=",r:1";
    if ($MaxLength) $s.=",ml:$MaxLength";
    $Form['Fields'][$id]=$s;
    }

  switch ($Type)
    {
    case 'label':
      $s=$Value;
      break;
    case 'enum.checks': 
  		$s="
  		<table cellspacing='0' cellpadding='0'>
  		<tr><td><input type='checkbox' onClick='fChecker_EnumCheckClick(\"$Form[Name]\",\"$id\",0,this.checked)' id='$Form[Name]_call$id'><label for='$Form[Name]_call$id' style='cursor:hand'><span class='notice'>Выбрать все</span></label></td></tr><tr valign='top'>";
  		$i=0; $maxn=$n=count($DocFieldValues);
  		if ($Columns) $maxn=ceil($n / $Columns);
  		$s2="";
  		if (is_array($Value)) $ValueEnum=$Value; else {
  			$ValueEnum=false;
  			if (($Value)||($Value===0)) $ValueEnum=explode(",",$Value);
  		}
  		
    	foreach ($DocFieldValues as $aValue=>$aCaption) {
    		$ch=""; if ($ValueEnum && (array_search($aValue,$ValueEnum)!==false)) $ch="checked";
    		$s2.="<tr><td><input onClick='fChecker_EnumCheckClick(\"$Form[Name]\",\"$id\",$aValue)' 
    		  type='checkbox' id='check_$Form[Name]$id"."_$aValue' value='1' $ch></td><td><label style='cursor:hand' for='check_$Form[Name]$id"."_$aValue'>$aCaption</label></td></tr>";
    		$i++;
    		if ($i>=($maxn)) {$i=0; $s.="<td><table>$s2</table></td>"; $s2="";}
    		
    	}
    	if ($s2) $s.="<td><table>$s2</table></td>";
    	$s.="</tr></table><input type='hidden' name='$Name' id='$idName' value='$Value'>
    	<input type='hidden' id='all_$Form[Name]$id' value='".implode(",",array_keys($DocFieldValues))."'>";
    	break;
    case 'inputmodal': # arg: ModalCall
      $s="";
      if (!$ModalCall) {$s="<font color='red'>Error: Undefined ModalCall property definition</font>"; break;}
      $ModalCall=ActionURL($ModalCall,$ModalArgs);
      if (!$BtnImg) $BtnImg="btn-dropdown.gif";
      $BtnClear="btn-clear.gif";
      if ($Size) $s.=" size='$Size'";
      if ($MaxLength) $s.=" maxlength='$MaxLength'";
      $GlyphURL ="$_THEME[SkinURL]/$BtnImg";
      
      
      if (!$_ENV->Form['InitedModalBtnCallback'])
        {  $_ENV->Form['InitedModalBtnCallback']=1;
         ?><script>
         var modalBtnForm,modalBtnId;
         function btnPageClear(aform,id)
          {
          document.getElementById("lfv_"+aform+id).value="";
          document.getElementById("lfc_"+aform+id).innerHTML="";
          document.getElementById("clear_"+aform+id).style.display='none';
          fChecker_Changed (aform,id,3);
          }
         function modalBtnCallback(v){
           if (!v) return;
           var id=modalBtnForm+modalBtnId;
           var x=v.split("\n");
           document.getElementById("lfv_"+id).value=x[0];
           if (!x[1]) x[1]=x[0];
           document.getElementById("lfc_"+id).innerHTML=x[1];
           document.getElementById("clear_"+id).style.display='block';
           fChecker_Changed (modalBtnForm,modalBtnId,3)
           }
         </script><?
        }

      if ($InitCall)
        {
        list ($cart,$mod,$met)=explode (".",$InitCall,3);
        $intf=&$_ENV->LoadInterface("$cart.$mod");
        if (!is_object($intf))
          {
          print_developer_warning("Interface is not loaded","$cart.$mod");
          break;
          }
        if (!method_exists($intf,$met))
          {
          print_developer_warning("Method not found in the interface class","$cart.$mod.$met");
          break;
          }
        $result=$intf->$met(array(
          Name=>$Name,
          Value=>$Value,
          DefaultValue=>$DefaultValue,
          Required=>$Required,
          ToString=>1));
        if ($result['Error'])
          {
          $s="<font color='red'>$result[Error]</font>";
          break;
          }
        if ($result['Value']) $Value=$result['Value'];
        if ($result['Caption']) $ValueCaption=$result['Caption'];
        }

      $OnClickBtn="modalBtnId=\"$id\";
        modalBtnForm=\"$Form[Name]\";
        W.openModal({url:\"$ModalCall\",
        Value:document.getElementById(\"$idName\").value,
        callback:\"modalBtnCallback\",
        w:500,h:500});";
      if ($Editable)
        {
        $field="<input style='width:100%' type='text' $JScriptBlurKeyUpChange class='inputarea' value='$Value' name='$Name' id='$idName' $s>
        <div style='width:100%; padding:2; color:#808080' id='$idCaption'>$ValueCaption</div>";
        }
      else
        {
        $field="<div class='inputarea' style='overflow:hidden; border:2px groove; height:18px; width:100%; padding:1; cursor:hand;color:#000000'  id='$idCaption' onClick='$OnClickBtn'>$ValueCaption</div>
        <input type='hidden' value='$Value' name='$Name' id='$idName'>";
        }
      $ClearDisplay="style='display:".(($Value)?"block":"none")."'";
      $s="\n<table width='100%' cellpadding=0 cellspacing=0><tr valign='top'><td>$field</td>
      <td $ClearDisplay id='clear_$Form[Name]$id' width='16'><img onClick='btnPageClear(\"$Form[Name]\",\"$id\")' src='$_THEME[SkinURL]/$BtnClear'/></td>
      <td width='16'><img src='$GlyphURL' onClick='$OnClickBtn'></td></tr></table>";
      break;
		case 'int.bt': case 'int.lt': case 'int.gt':
			if (!$FromText) $FromText="от";
			if (!$ToText) $ToText="до";
      $s="<table id='$idCaption'><tr><td>";

			if (isset($From) && isset($To)) {
				$_ENV->PutValueSet(array(ValueSetName=>"FieldValueset$Name",EnumFrom=>$From,EnumTo=>$To,EnumStep=>$Step));
				list($MinValue,$MaxValue)=explode(":",$Value);
				if ($Type=='int.lt') $MaxValue=$Value;
				
	      $args=array(ToString=>1,
	        ValueSetName=>"FieldValueset$Name",
	        NullCaption=>$NullCaption,
	        Value=>$MinValue,
	        Size=>$Size,
	        Name=>$Name."_min",
	        Editable=>1,
	        DefaultValue=>$DefaultValue,
	        Form=>$Form['Name']);
	
	      $args['OnChange']="fChecker_Changed (\"$Form[Name]\",\"$id\",3);";
	      if ($Required) 	{$args['Required']=1;}
	
	      if ($Type=='int.bt') {$s.="$FromText</td><td>";}
	      if (($Type=='int.gt')||($Type=='int.bt')) {
				$s.=$_ENV->PutDropDown($args);	
				}
	      if ($Type=='int.bt') {$s.="</td><td> $ToText </td><td>";}
				
	      if (($Type=='int.lt')||($Type=='int.bt')) {
	        $args['Value']=$MaxValue;
	      	$args['Name']=$Name."_max";
					$s.=$_ENV->PutDropDown($args);	
				}
			} else {
	      if ($Type=='int.bt') {$s.="$FromText</td><td>";}
	      if (($Type=='int.gt')||($Type=='int.bt')) {
					$s.="<input class='inputarea' $JScriptBlurKeyUpChange size='4' type='text' id='lfv_$Form[Name]$id"."_min' value='$MinValue'>";	
				}
	      if ($Type=='int.bt') {$s.="</td><td> $ToText </td><td>";}
				
	      if (($Type=='int.lt')||($Type=='int.bt')) {
	        $args['Value']=$MaxValue;
	      	$args['Name']=$Name."_max";
					$s.="<input class='inputarea' $JScriptBlurKeyUpChange size='4' type='text' id='lfv_$Form[Name]$id"."_max' value='$MaxValue'>";	
				}

			
			}
      $s.="</td></tr></table><input type='hidden' name='$Name' id='$idName' value='$Value'>";
			
			
      break;
    case 'dim':
      list($w,$h)=explode ("x",$Value);
      $s="
      <input class='inputarea' $JScriptBlurKeyUpChange size='4' type='text' id='w_$Form[Name]$id' value='$w'>x
      <input class='inputarea' $JScriptBlurKeyUpChange size='4' type='text' id='h_$Form[Name]$id' value='$h'>
      <input type='hidden' name='$Name' id='$idName' value='$Value'>";
      if ($DefaultValue) $s.=" ($DefaultValue)";
      break;

    case 'droplist':
      $args=array(ToString=>1,
        ValueSetName=>$ValueSetName,
        NullCaption=>$NullCaption,
        Value=>$Value,
        Size=>$Size,
        Name=>$Name,
        Editable=>$Editable,
        DoEditValue=>$DoEditValue,
        DefaultValue=>$DefaultValue,
        Form=>$Form['Name']);
      $args['OnChange']=$OnChange; //"fChecker_Changed (\"$Form[Name]\",\"$id\",3);$OnChange";
      if ($Required) 	{$args['Required']=1;}
      $s=$_ENV->PutDropDown($args);
      break;
    case 'selectpage':
      $s="DEPRECATED FIELD TYPE[selectpage]";
      break;
    case 'checkbox':
      $s="<input type='checkbox' value=1 name='$Name' ".(($Value)?"checked":"").">";
      break;
    case 'hidden':
      $s="<input type='hidden' id='$idName' name='$Name' value='$Value'>";
      break;
    case 'identifier':
      $s="";
      if ($Size) $s.=" size='$Size'";
      if ($MaxLength) $s.=" maxlength='$MaxLength'";
      $s="\n<input $JScriptBlurKeyUpChange type='text' class='inputarea' value='$Value' name='$Name' id='$idName' $s>";
      break;
    case 'file':
      $s="";
      if ($Size) $s.=" size='$Size'";
      $s="\n<input type='file' $JScriptBlurKeyUpChange class='inputarea' value='$Value' name='$Name' id='$idName' $s>";
      if ($DefaultValue) $s.=" ($DefaultValue)";
    	
    	break;
    case 'int': case 'float': case 'string': case 'color': case 'email':
      $s="";
      if ($Width) $s.=" style='width:$Width'";
      if ($Size) $s.=" size='$Size'";
      if ($MaxLength) $s.=" maxlength='$MaxLength'";
      $Value=htmlspecialchars($Value,ENT_QUOTES);
      $s="\n<input type='text' $JScriptBlurKeyUpChange class='inputarea' value='$Value' name='$Name' id='$idName' $s>";
      if ($DefaultValue) $s.=" ($DefaultValue)";
      break;
    case 'text':
      $s="";
      $Value=htmlspecialchars($Value);
      if ($Size) $s.=" cols='$Size'";
      if ($Rows) {$s.=" rows='$Rows'";}
      if ($Width){$s.=" style='width:$Width'";}
      
      $s="\n<textarea $JScriptBlurKeyUpChange class='inputarea' name='$Name' id='$idName' $s>$Value</textarea>";
    	break;
    case 'langstring':
    case 'langtext':
      $s1="";
      $Value=htmlspecialchars($Value);
      if (!$MaxLength) $MaxLength=40;
      if ($Size) {if ($Type=='langtext')  $s1.=" cols='$Size'"; else $s1.=" size='$Size'";}
      if ($Rows) {if ($Type=='langtext')  $s1.=" rows='$Rows'";}
      $DefaultStr=langstr_get($Value,'default');

      $s="<input type='hidden' name='$Name' value='$Value' id='$idName' >";
      if ($Type=='langstring')
        {
        $s.="<input type='text' $s1 id='lfi_$Form[Name]$id' class='inputarea' value='$DefaultStr' onKeyUp='fCheckerLangFieldChanged(\"$Form[Name]\",\"$id\")' onChange='fCheckerLangFieldChanged(\"$Form[Name]\",\"$id\")'>";
        }
      else
        {
        $s.="<textarea onKeyUp='fCheckerLangFieldChanged(\"$Form[Name]\",\"$id\")' onChange='fCheckerLangFieldChanged(\"$Form[Name]\",\"$id\")' id='lfi_$Form[Name]$id' class='inputarea' $s1>$DefaultStr</textarea>";
        }
      $s.=$_ENV->PutLanguageSelector(array(ToString=>1,Name=>$id,OnChange=>$OnChange));
      if ($ShowDefaultStr) $s.="<div id='lfd_$Form[Name]$id' class='notice'>$DefaultStr</div>";
      break;
    case 'call':
      if (!$Action) {print_developer_warning("No action is defined for call of field '$Name'"); break;}
      list ($cart,$mod,$met)=explode (".",$Action,3);
      $intf=&$_ENV->LoadInterface("$cart.$mod");
      if (!$intf)
        {
        print_developer_warning("Interface is not loaded","$cart.$mod");
        break;
        }
      if (!method_exists($intf,$met))
        {
        print_developer_warning("Method not found in the interface class","$cart.$mod.$met");
        break;
        }
      $s=$intf->$met(array(
        Name=>$Name,
        Value=>$Value,
        DefaultValue=>$DefaultValue,
        Required=>$Required,ToString=>1));
      break;
    default:
      $s="<font color='red'>Unknown type: $Type</font>";
    }
  if (!$s) return;
  $cap2=$Caption.(($Required)?"<font color='red'>*</font>":"");
  list ($AttrTd,$AttrClass)=get_css_pair($Form['AttrCSS'],"th");
  list ($ValTd,$ValClass)  =get_css_pair($Form['ValCSS'],"td");

  if ($Type=='hidden')
    {
    $result=$s;
    }
  else
    {
    $ss=$Style; 
    if (!$ss) $ss=$Form['Style'];
    switch (strtolower($ss))
      {
      case 'vertical':
        $result="<p>".(($Caption)?"<b>$cap2</b><br/>":"").$s.(($Notice)?"<div class='notice'>$Notice</div>":"")."</p>";
        break;
      case 'clear':
        $result=$s;
        break;
      default:
        $result="<tr valign='top'><$AttrTd $AttrClass width='40%'>$cap2".(($Notice)?"<div class='notice'>$Notice</div>":"")."</$AttrTd>"
        ."<$ValTd $ValClass>$s</$ValTd>"
        ."</tr>";
      }
    }

  if ($ToString) return $result; else print $result;
  }

function core_PutLanguageSelector($args)
  {
  global $cfg,$_LANGUAGE_DISPATCHER,$_SYSSKIN_NAME,$_THEME_NAME,$_ENVIRONMENT,$_THEME;
  $__=$GLOBALS['_STRINGS']['_'];
  extract ($args);
  $id=str_replace(array('[',']'),array('_',''),$Name);
  $idName="lfv_$Form[Name]$id";

  $Form=&$_ENV->Form;
  # OnChange - what function should be called (i.e. fCheckerSetLang for forms)
#  $_ENV->InitWindows();
  if (!$ENV->fLangSelectorInited)
    {
    $_ENV->fLangSelectorInited=1;
    $Languages=&$_LANGUAGE_DISPATCHER->LoadLanguages();
    ?>
    <script>
    var FlagsURL='<? print $cfg['PublicURL'].'/sys/flags/'; ?>';
    var fCurrentLang='';
    function fCheckerLangFieldChanged(formname,fname)
      {
      fCheckerSetLangToField(fCurrentLang,formname,fname);
      fChecker_Changed(formname,fname,3);
      }

    function fCheckerSetLangToField(LangID,formname,fname)
      {
      var remu=/¨/;
      if (LangID=='default') LangID='';
      var flagsrc=FlagsURL+((LangID=='')?'default':LangID)+'.gif';
      var finput=document.getElementById("lfi_"+formname+fname);
      var fvalue=document.getElementById("lfv_"+formname+fname);
      var flag  =document.getElementById("fl_" +formname+fname);
      var deftext=document.getElementById("lfd_"+formname+fname);
      if (flag.src!=flagsrc) flag.src=flagsrc;
      var s=fvalue.value,res="",newval="",added=0,ls,cl,lse,j;
      if (s.search(remu)!=-1)
        {
        ls=s.split('¨');
        for (j=0; j<ls.length; j++)
          {
          lse=ls[j];
          if ((!lse)||(lse=='')) continue;
          lsa=lse.split('¦');
          cl=lsa[0];
          if (!cl) cl="";
          if (cl==LangID) newval=lsa[1];
          if (cl==fCurrentLang)
            {
            if (deftext) {if (fCurrentLang=='') {deftext.innerHTML=finput.value;}}
            if (finput.value!='') {res+="¨"+cl+"¦"+finput.value; added=1;}
            }
          else res+="¨"+cl+"¦"+lsa[1];
          }
        if ((!added)&&(finput.value!='')) res+="¨"+fCurrentLang+"¦"+finput.value;
        if (res!='') res+="¨";
        }
      else
        {
        if (finput.value!='') res="¨"+fCurrentLang+"¦"+finput.value+"¨";
        if (deftext) {if (fCurrentLang=='') {deftext.innerHTML=finput.value;}}
        }
      fvalue.value=res;
      if (LangID!=fCurrentLang) finput.value=newval;
      }

    function fCheckerSetLang(LangID,formname)
      {
      if (LangID=='default') LangID='';
      var fdef,fname,fields=false;
      try {fields=eval ("fChecker_f_"+formname);} catch(e){};
      if (!fields) {alert ("Form '"+formname+"' has no fields"); return;}
      for (fname in fields)
        {
        fdef=fields[fname];
        if (fdef.t.match(/langstring|langtext/gi)) fCheckerSetLangToField(LangID,formname,fname);
        }
      fCurrentLang=LangID;
      }

    function onLangSwitchClick(el,formname,id)
      {
      <?
  $s1="{"."act:'default',cap:'$__[CAPTION_DEFAULT_LANGUAGE]',img:'$cfg[PublicURL]/sys/flags/default.gif',cb:'fCheckerSetLang',cba:formname},{cap:'-'}";
  foreach($Languages as $LangID=>$Language)
    {
    if (!$Language['Enabled']) continue;
    $s1.=(($s1)?",":"")."{"."act:'$LangID',cap:'$Language[Caption]',img:'$cfg[PublicURL]/sys/flags/$LangID.gif',cb:'fCheckerSetLang',cba:formname}";
    }
    print "W.openPopupMenu(el,[$s1],true);"
      ?>
      }
    </script>
    <?
    }
  $result="<img onClick='onLangSwitchClick(this,\"$Form[Name]\",\"$id\")' id='fl_$Form[Name]$Name' src='$cfg[PublicURL]/sys/flags/default.gif' alt='$__[HINT_SWITCH_LANGUAGE]' width='18' height='12'>";
  if ($ToString) return $result; else print $result;
  }

function core_PutFormOpenGroup(&$args) {
	extract($args);
	
	$id=$_ENV->Form['Name']."_group$Name";
	if ($args['TriggerEnable']) {
		$style="style='display:none'";
		$_ENV->Form['Triggers'][$id]=$TriggerEnable;
	}
	$result="
	<!-- group open -->
	<tr><td colspan='2'><table width='100%' cellpadding='2' cellspacing='1' border=0 $style id='$id'>
    <tr><td colspan='2' class='bgdowndown'>$Caption</td></tr>
		<tr><td></td><td class='co'>$args[Header]</td></tr>";
	$_ENV->Form['OpenedGroup']=$Name;
  if ($ToString) return $result; else print $result;
}

function  core_PutFormCloseGroup (&$args) {
	extract($args);
	$_ENV->Form['OpenedGroup']="-closed-";
	$result="";
	if ($args['Footer']) $result="<tr><td colspan='2' class='notice'>$args[Footer]</td></tr>";
	$result.="
	<!-- group close -->
	</table></td></tr>";
  if ($ToString) return $result; else print $result;
}
  
?>
