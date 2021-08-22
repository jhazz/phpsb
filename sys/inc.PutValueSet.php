<?
function core_PutValueSet(&$args)
  {
  extract(param_extract(array(
    ValueSetName=>"string",
    Values=>"array",       # using Values or Recordset->Rows[]->CaptionField
    Recordset=>"object",   #
    CaptionField=>"string",#
    ToString=>"int",
    NullCaption=>"string",
    EnumFrom=>'int',
    EnumTo=>'int',
    ),$args));

  $MaxSize=0;

  $s="";
  if ($NullCaption)
    {
    $NullCaption=addslashes($NullCaption);
    $s.="0:'$NullCaption'";
    }

  if ($Values)
    {
    foreach($Values as $k=>$v)
      {
      $v=langstr_get($v);
      $size=strlen($v);
      if ($size>$MaxSize) $MaxSize=$size;
      $v=addslashes ($v);
      $s.=(($s)?",":"")."'$k':'$v'";
      }
    }
  elseif ($Recordset)
    {
    $kk=array_keys($Recordset->Rows);
    foreach($kk as $k)
      {

      $v=langstr_get($Recordset->Rows[$k]->$CaptionField);
      $size=strlen($v);
      if ($size>$MaxSize) $MaxSize=$size;
      $v=addslashes ($v);
      $s.=(($s)?", ":"")."'$k':'$v'";
      }
    }
  if ($s) $result="\n<script>var ValueSet_$ValueSetName={"."$s}; var ValueSetSize_$ValueSetName=$MaxSize;</script>\n";

  if (($EnumFrom)||($EnumTo))
    {
    if (!$EnumStep) $EnumStep=1;
    $result="\n<script>var ValueSet_$ValueSetName=new Array(); for(var i=$EnumFrom;i<=$EnumTo;i+=$EnumStep)ValueSet_$ValueSetName"."[i]=i; var ValueSetSize_$ValueSetName=$MaxSize;</script>\n";
    }
  if ($ToString) return $result;
  print $result;
  }


?>
