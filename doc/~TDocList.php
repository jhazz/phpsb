<?php
class doc_TDocList
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Partner city details";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][doc];
  $__=&$GLOBALS[_STRINGS][_];
  $this->Propdefs=array(
    SQLFilter   =>array (Type=>"Binding",DataType=>"SQLFilter"),
    TargetURL=>array (Type=>"LocalURL",Caption=>"Страница, которая отображает информацию о представительстве"),
    DocClassName=>array (Type=>"String",Caption=>"Класс документа для отображения"),
    ViewName=>array (Type=>"String",Caption=>"Вид документа"),
    NoDocumentsFound=>array(Type=>"String",Caption=>"Отсутствуют документы",DefaultValue=>"Отсутствуют документы списка"),
    OpeningContext=>array(Type=>"SysContext",Caption=>"Виртуальный контекст сайта для открывания документа"),
    ShowNumbers=>array(Type=>"Boolean",Caption=>"Показывать порядковые номера"),
    ColumnCount=>array(Type=>"Int",DefaultValue=>1),
    CSS_Href=>array(Type=>"CSS_Class",BaseCSSClass=>"a"),
    Style=>array(Type=>"List",Values=>array(
      Standard=>"Standard style",
      CaptionBelow=>"Only caption below image")),
    );
  }

function Render(&$Control)
  {
  $__=&$GLOBALS[_STRINGS][_];
  $_=&$GLOBALS[_STRINGS][um];
  global $cfg;
  global $_USER,$_HOMEURL;
  extract ($Control->Properties);

/*  $_ENV->ReadPublishedData($Control,'SQLFilter');
  if ((!$Control->SQLFilter) && (($Control->EditMode)))
    {
    return array(Error=>"SQLFilter have no binding to a filtering control");
    }
*/
  $qview=DBQuery ("SELECT SelectQuery,KeyField,PrintFormat,OrderByField,WhereClause,TnImageSocket,TnFormatNo
     FROM doc_Views WHERE ViewName='$ViewName' AND ClassName='$DocClassName'");

  if (!$qview)
    {
    return array(Error=>"Bad document view. Check table doc_Views",Details=>"ClassName='$DocClassName' , ViewName='$ViewName'");
    }

  $format=$qview->Top->PrintFormat;
  if (!$format)
    {
    return array(Error=>"Bad document view in table. Check table doc_Views. PrintFormat is empty. You should type it in",Details=>"ClassName='$DocClassName' , ViewName='$ViewName'");
    }

#  $OrderByField=$qview->Top->OrderByField;
#  $WhereClause=$qview->Top->WhereClause;
#  $SelectQuery=$qview->Top->SelectQuery;
#  $TnImageSocket=$qview->Top->TnImageSocket;
#  $TnFormatNo=
  extract(param_extract(array(
    OrderByField=>"string",
    WhereClause =>"unesc_string",
    SelectQuery=>"unesc_string",
    TnImageSocket=>"string",
    TnFormatNo=>"int",
    ),$qview->Top));


  $Filter=$Control->SQLFilter;
  if ($WhereClause)
    {
    $Filter=($Filter) ? "($Filter) AND ($WhereClause)" : $WhereClause;
    }
  if ($Filter)
    {
    $SelectQuery.=" WHERE ".$Filter;
    }
  if ($OrderByField) {
    $desc=substr($OrderByField,0,1);
    if ($desc=='-') {$SelectQuery.=" ORDER BY ".substr($OrderByField,1)." DESC ";}
    elseif ($desc=='+') {$SelectQuery.=" ORDER BY ".substr($OrderByField,1);}
    else $SelectQuery.=" ORDER BY $OrderByField";
    }
  $format=explode ("|",$format);
  $qdoc=DBQuery ($SelectQuery,$qview->Top->KeyField);

  $RowNo=1;
  if ($qdoc)
    {

    if ($TnImageSocket) {
      $SocketIds="";
      foreach ($qdoc->Rows as $index=>$row) {
        if ($SocketIds) {$SocketIds.=",";}
        $SocketIds.="'$TnImageSocket/$index'";
      }
      $qimg=DBQuery ("SELECT BindTo,Filenames FROM img_Documents
        WHERE BindTo IN ($SocketIds) ORDER BY OrderNo","BindTo");
    }



    list($at,$ac)=get_css_pair($CSS_Href,'a');
    print "<table border='0'>";
    $ColNo=1;
    foreach ($qdoc->Rows as $KeyID=>$data)
      {
      $ico="";
      if ($qimg)
        {
        $BindTo="$TnImageSocket/$KeyID";
        $idoc=$qimg->Rows[$BindTo];
        if ($idoc)
          {
          $ifiles=$_ENV->Unserialize($idoc->Filenames);
          $ico=$ifiles[$TnFormatNo];
          $file="$cfg[FilesPath]/img/$TnImageSocket/$ico";
          if (is_file($file))
            {
            $imgsize=getimagesize($file);
            $ico="<img border='0' src='$cfg[FilesURL]/img/$TnImageSocket/$ico' $imgsize[3]>";
            }
          else $ico="";
          }

        }
      $row="";
      $first=true;
      for ($i=0;$i<count($format);$i++)
        {
        list($prefix,$fieldname,$suffix)=explode ("~",$format[$i]);
        $v=$data->$fieldname;
        if ($v)
          {
          $DocURL=$_HOMEURL.'/'.$OpeningContext.'/'.$KeyID.'.'.$cfg['VirtualExtension'];
          if ($first) {$row.="<a $ac href='$DocURL'>$prefix$v$suffix</a><br>";}
            else $row.="$prefix$v$suffix";

          }
        $first=false;
        }
      if ($row)
        {
        switch ($Style)
          {
          case 'CaptionBelow':
            if ($ColNo==1) print "<tr>";
            print "<td align='center'><a href='$DocURL'>$ico</a><br/>$row</td>";
            if ($ColNo>=$ColumnCount) {$ColNo=1; print "</tr>";}
            $ColNo++;
            break;
          default:
            $pp=""; if ($ShowNumbers) {$pp="<td>$RowNo</td>";}
            print "<tr valign='top'>$pp<td><a href='$DocURL'>$ico</a></td><td>$row</td></tr>";
          }
        $RowNo++;
        }
      }
    print "</table>";
    }
  else
    {
    print $NoDocumentsFound;
    }
  }
}
?>
