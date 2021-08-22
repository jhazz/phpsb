<?
function core_PutDropDown(&$args)
  {
  extract(param_extract(array(
    Caption=>"string",
    Name=>"string",
    ValueSetName=>"string",
    Value=>"string",
    BtnImg=>"string=btn-dropdown.gif",
    Size=>'int',
    NullCaption=>'string',
    Editable=>'int',
    DoEditValue=>'int',
    ToString=>'int',
    Form=>'string', # not required
    OnChange=>'',
    OnSelect=>'',
    DefaultValue=>"string",
    Required=>'int',
    ViewCaption=>'int',
    ),$args));

  global $cfg,$_SYSSKIN_NAME,$_THEME_NAME,$_ENVIRONMENT,$_THEME;

  if (!$Editable) $ViewCaption=1;
  if (!$_ENV->PutDropDownInited)
    {
    $_ENV->PutDropDownInited=1;
#    $_ENV->InitWindows();
    ?>
    <script>
    var dropFieldID,dropFieldValue,dropFieldValCaption,dropFields=new Array();
    function DropDown_Update(idname,first)
      {
      var field=dropFields[idname];
      if (!field) return;

      var v=field.fValue.value;

      try {var vc=eval('ValueSet_'+field.ValueSetName+'["'+v+'"]'); field.DropImg.style.visibility='visible';}
      catch(e) {vc=undefined;field.DropImg.style.visibility='hidden'; return;}
      if (vc==undefined) vc=(field.NullCaption)?field.NullCaption:'----';
      if ((!field.Editable)||(field.DoEditValue))
        {
        field.fCaption.title=field.fCaption.innerHTML=vc;
        }

      if (!first)
        {
        dropFieldID=idname;
        dropFieldValue=v;
        if (field.OnChange) eval (field.OnChange);
        }
      }

    function DropDown_Set(idname,value)
      {
      var field=dropFields[idname];
      if (!field) return;
      try {var vc=eval('ValueSet_'+field.ValueSetName+'["'+value+'"]'); field.DropImg.style.visibility='visible';}
      catch(e) {vc=undefined;field.DropImg.style.visibility='hidden'; return;}
      dropFieldValue=value;
      dropFieldValCaption=vc;
      dropFieldID=idname;

      if (field.Editable && (!field.DoEditValue)) {value=vc;}
      else {if (field.fCaption) {field.fCaption.innerHTML=vc; field.fCaption.title=vc;}}
      field.fValue.value=value;

      if (field.OnSelect) {eval (field.OnSelect);}
      DropDown_Update(idname);
      }

    function DropDown_Open (srce,idname,dv)
      {
      var field=dropFields[idname];
      if (!field) return;

      var v=field.fValue.value;
      var el=srce.srcElement;
      if (!el) {el=srce.target;}
      var s="",sel;
      var rc=0;
      try {var vs=eval('ValueSet_'+field.ValueSetName)} catch(e) {return;}

      if (field.NullCaption)
        {s="<option value=''"+(((v=="")||(v=="0"))?" selected":"")+">"+field.NullCaption+"</option>";
        rc++;
        }

      if (vs)
        {
        for (var i in vs)
          {
          if ((!field.DoEditValue)&&(field.Editable)) sel=(vs[i]==v)?"selected":""; else sel=(v==i)?"selected":"";
          s+="<option value='"+i+"' "+sel+">"+((dv!=undefined && dv!="")?((dv==i)?" * ":"&nbsp;&nbsp;&nbsp;"):"")+vs[i]+"</option>";
          rc++;
          }
        }
      if (rc>10) rc=10;
      if (rc<2) rc=2;

      if (s)
        {
        s="<select class='inputarea' size='"+rc+"' onChange='popupCallerWindow.DropDown_Set(\""+idname+"\",this.options[this.selectedIndex].value);' id='DropDownSelect'>"+s+"</select>";
        P$.openPopupUnder(el,s);
        }
      }

    </script>
    <?
    }

  $SkinPath=$_THEME['SkinPath'];
  $SkinURL=$_THEME['SkinURL'];
  $GlyphURL ="$SkinURL/$BtnImg";
  $GlyphFile="$SkinPath/$BtnImg";

  list($w,$h,$t,$imgwh)=@getimagesize($GlyphFile);
  $idName=$Form.preg_replace(array('/\[/','/\]/','/\//'),array('_','','_'),$Name);

  if ($DefaultValue) $dv=",\"$DefaultValue\""; else $dv=",false";
  $OnClickBtn="DropDown_Open(event,\"$idName\"$dv)";

  if ($Editable)
    {
    $result.="<table cellspacing=0 cellpadding=0 border=0><tr valign='top'><td>";
    $onch=" onKeyUp='dropFieldID=\"$idName\"; dropFieldValue=this.value; $OnChange' onChange='
      dropFieldID=\"$idName\";
      dropFieldValue=this.value;
      $OnChange'";
    $result.="<input size='$Size' class='inputarea' name='$Name' id='lfv_$idName' type='text' $onch value='$Value'>";
    if ($DoEditValue) $result.="<div id='lfc_$idName' class='notice'></div>";
    }
  else
    {
    $s1=($Size)?"":"width='100%'";
    $s2=($Size)?$Size*5:"100%";
    $result.="<table $s1 cellspacing=0 cellpadding=0 border=0><tr valign='top'><td>";
    $result.="<div nowrap class='inputarea' style='overflow:hidden; border:2px groove; height:18px; width:$s2; padding:1; cursor:hand;color:#000000'  id='lfc_$idName' onClick='$OnClickBtn' title='".addslashes($Caption)."'>$Caption</div>
    <input type='hidden' id='lfv_$idName' name='$Name' value='$Value'>";
    }

  $result.="</td><td align='right' width='1'><img id='drop_$idName' src='$GlyphURL' $imgwh onClick='$OnClickBtn'></td></tr>";
  $sc="";
  $field=array(
    idName=>"'$idName'",
    fValue=>"document.getElementById(\"lfv_$idName\")",
    DropImg=>"document.getElementById(\"drop_$idName\")",
    ValueSetName=>"'$ValueSetName'",
    Editable=>intval($Editable),
    DoEditValue=>intval($DoEditValue));
  if ((!$Editable)||($DoEditValue)) $field['fCaption']="document.getElementById(\"lfc_$idName\")";

  if ($NullCaption) $field['NullCaption']="'$NullCaption'";
  if ($OnChange) $field['OnChange']="'$OnChange'";
  if ($OnSelect) $field['OnSelect']="'$OnSelect'";

  foreach ($field as $k=>$v)
    {
    if ($v) $sc.=(($sc)?",":"")."$k:$v";
    }
  $result.="<script>dropFields['$idName']={"."$sc}; DropDown_Update('$idName',1)</script></table>";
  if ($ToString) return $result; else print $result;
  }


?>
