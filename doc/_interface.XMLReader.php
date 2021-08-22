<?

if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }


class doc_XMLReader {

  function doc_XMLReader () {
    $this->CopyrightText="(c)2003 JhAZZ Site Builder. Document interface";
    $this->CopyrightURL="http://www.jhazz.com/jsb";
    $this->ComponentVersion="1.0";
  }

  function BriefView ($args) {
    global $cfg;
    extract(param_extract(array(
      ClassName  =>'string',
      View       =>'string',
      IfEmpty    =>'string',
      PageNo     =>'int=1',
      RowsPerPage=>'int=4',
      Filter=>'nonesc_string',
      ),$args));


    $qv=DBQuery ("SELECT SelectQuery, PrintFormat, FlashLink, KeyField,TnImageSocket,TnFormatNo,OrderByField, WhereClause
       FROM doc_Views WHERE ClassName='$ClassName' AND ViewName='$View'");
    if (!$qv) {print "<body><b>Error: View '$View' not found</b></body>"; return; }
    $SelectQuery=$qv->Top->SelectQuery;
    $KeyField=$qv->Top->KeyField;
    $TnImageSocket=$qv->Top->TnImageSocket;
    $FlashLink=$qv->Top->FlashLink;
    $WhereClause=$qv->Top->WhereClause;
    $OrderByField=$qv->Top->OrderByField;

    $TnFormatNo=intval($qv->Top->TnFormatNo);


    if ($Filter) {
      $Filter=preg_replace (array("'.eq.'i" , "'.lt.'i", "'.gt.'i"), array("=","<",">"),$Filter);
      }

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

    $qd=DBQuery ($SelectQuery,$KeyField);
#    print "<hr>$SelectQuery <hr>$Filterz";
    if (!$qd) {
      if ($IfEmpty) {print "<record id='0'><title>$IfEmpty</title></record>";}
      return;
      }

    if ($TnImageSocket) {
      $SocketIds="";
      foreach ($qd->Rows as $index=>$row) {
        if ($SocketIds) {$SocketIds.=",";}
        $SocketIds.="'$TnImageSocket/$index'";
      }
      $qimg=DBQuery ("SELECT BindTo,ImgID,ImgName,TnNames FROM img_Documents WHERE BindTo IN ($SocketIds) ORDER BY OrderNo","BindTo");
    }

    $fields=explode ("|",$qv->Top->PrintFormat);
    foreach ($qd->Rows as $index=>$row) {
      print "<record id='$index'>";
      $ImgURL=""; $TnURL="";
      $imgrow=$qimg->Rows["$TnImageSocket/$index"];

      if ($imgrow) {
        $ImgNameArr=explode("|",$imgrow->TnNames);
        if ($TnFormatNo) {
          $TnURL =$cfg['FilesURL']."/img/$TnImageSocket/".$ImgNameArr[$TnFormatNo-1];
          $TnPath=$cfg['FilesPath']."/img/$TnImageSocket/".$ImgNameArr[$TnFormatNo-1];
          $size=@getimagesize($TnPath);
          if ($size) {
            print "<tn src=\"$TnURL\" $size[3] />\n";
          }
        }
        $ImgURL=$cfg['FilesURL'].'/img/$TnImageSocket/$imgrow->ImgName';

      }

      $s=$FlashLink;
      if ($s) {
        $pat=array();         $rep=array();
        $pat[]="'#imgsrc#'i"; $rep[]=$ImgURL;
        $pat[]="'#tnsrc#'i";  $rep[]=$TnURL;

        for ($i=0;$i<$qd->FieldCount;$i++) {
          $FieldName=$qd->Fields[$i];
          $FieldValue=$row->$FieldName;
          $pat[]="'#$FieldName#'i";
          $rep[]="$FieldValue";
        }
        $s=preg_replace ($pat,$rep,$s);
        print "\n<open>$s</open>\n";
        }


      $d=$t=false;
      for($i=0;$i<count($fields);$i++) {
        list ($prefix,$fieldName,$suffix,$type)=explode ('~',$fields[$i]);
        switch ($fieldName) {
          case "imgsrc": $value=$ImgURL; break;
          case "tnsrc": $value=$TnURL; break;
          default: $value=$row->$fieldName;
        }
        if ($value) {
          switch($type) {
            case 'date': $value=format_date ('day month year',$value); break;
            case 's100': $s=substr($value,0,100);
              if (strlen($s) != strlen($value)) {$p=strrpos($s,' '); $value=substr($s,0,$p);}
              break;
          }
          if ($i==0) {print "<title>";}
          if (($t)&&(!$d)) {$d=true; print "<description>";}
          print $prefix.$value.$suffix;
          if ($i==0) {print "</title>";$t=true;}

        } // if value
      } // for $i
      if ($d) {print "</description>";}

      print "</record>\n";
    } //foreach $qd->Rows

  }

  function View ($args) {
    global $cfg;
    extract(param_extract(array(
      ClassName  =>'string',
      View       =>'string',
      ),$args));

  }
}
?>
