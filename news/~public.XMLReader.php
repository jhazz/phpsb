<?
if (ACCEPT_INCLUDE!=1) {die ("You can't access this file directly"); }

class news_XMLReader
  {


  function LoadNewsText ($args) {
    global $cfg;
    extract(param_extract(array(
      NewsID=>'int',
      TnFormat=>'string=A',
      DateFormat=>'string=day month year',
      ),$args));

    $NewsHeadImgURL=$cfg['FilesURL'].'/img/news.Event/imghead';
    $NewsHeadImgPath=$cfg['FilesPath'].'/img/news.Event/imghead';

    $TnFormatNo=ord($TnFormat)-65;
    $q=DBQuery ("SELECT * FROM news_Events WHERE NewsID=$NewsID");
    if (!$q) {return array(XML=>"<page><body>Новость $NewsID удалена или отсуствует</body></page>");}
    $q2=DBQuery ("SELECT FileName FROM stdctrls_Richtexts WHERE BindTo='news/$NewsID'");
    $DateOfNews=format_date($DateFormat,$q->Top->DateOfNews);
    $result="<h2>".$q->Top->Title."</h2><h3>".$q->Top->Intro."</h3><p><date>$DateOfNews</date></p>";
    $text="";
    if ($q2) {
      $FileName=$cfg['FilesPath'].'/stdctrls.RichText/'.basename($q2->Top->FileName);
      if (file_exists($FileName))
        {
        $text="";
        $handle = fopen($FileName, "r");
        while (!feof($handle))
          {
          $s = fgets($handle, 4096);
          $text.=$s;
          }
        fclose($handle);
        }
      $result.="<p>$text</p>";
      }
    $result=str_replace("../storeproduct/","asfunction:_root._openproduct,",$result);
    $result="<page><body>$result</body></page>";

/*    $q3=DBQuery ("SELECT * FROM img_Documents WHERE BindTo='news.headimg/$NewsID'","ImgID");
    if ($q3) foreach ($q3->Rows as $ImgID=>$headico) {
      $TnSrcs=explode("|",$headico->TnNames,10);
      if ($TnFormatNo>=count($TnSrcs)) {$TnFormatNo=0;}
      $TnSrc=$TnSrcs[$TnFormatNo];
      $TnFilePath=$NewsHeadImgPath.'/'.$TnSrc;
      $imginfo=@getimagesize($TnFilePath);
      if ($imginfo) {
        $result.="<headico $imginfo[3] src='$NewsHeadImgURL/$TnSrc'>$headico->Caption</headico>";
      }
    }
*/
    return array(XML=>$result);
  }


  function LoadNewsList($args) {
    $_= &$GLOBALS[_STRINGS][news];
    $__=&$GLOBALS[_STRINGS][_];
    global $cfg;
    extract(param_extract(array(
      NewsGroupID=>'int',
      RowsPerPage=>'int=4',
      IntroLength=>'int=100',
      news_PageNo=>'int=1',
      TnFormat=>'string=B',
      DateFormat=>'string=day month year',
      ),$args));

    $qg=DBQuery ("SELECT Caption,ParentID FROM jsb_Pages WHERE JSBPageID=$NewsGroupID AND SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."'");
    $news_GroupName=$qg->Top->Caption;
    $result="";
    $TnFormatNo=ord($TnFormat)-65;

    $now=time();
    $qnews=DBQuery ("SELECT * FROM news_Events
       WHERE NewsGroupID=$NewsGroupID AND PubStatus=10 AND DateToShow<$now AND DateToHide>$now
       ORDER BY DateOfNews DESC
       LIMIT ".(($news_PageNo-1)*$RowsPerPage).",$RowsPerPage","NewsID");
    $NewsHeadIDs="";
    $NewsIDs="";
    if (!$qnews) {return;}
    foreach ($qnews->Rows as $NewsID=>$row)
      {
      if ($NewsHeadIDs) {$NewsHeadIDs.=","; $NewsIDs.=",";}
      $NewsHeadIDs.="news.Event/imghead/$NewsID";
      $NewsIDs.="'news.Event/$NewsID'";
      }
    $str="SELECT ImgID,BindTo,Filenames,Caption FROM img_Documents WHERE BindTo IN ($NewsHeadIDs)";
    $qico=DBQuery ($str,array("BindTo","ImgID"));
    $str="SELECT DocID,BindTo FROM stdctrls_Richtexts WHERE BindTo IN ($NewsIDs)";
    $qdocs=DBQuery ($str,array("BindTo","DocID"));
    foreach ($qnews->Rows as $NewsID=>$row) {
      $DateOfNews=format_date($DateFormat,$row->DateOfNews);
      $Intro=substr ($row->Intro,0,$IntroLength);
      if (strlen($Intro)!=strlen($row->Intro))
        {
        $pos=strrpos ($Intro," ");
        $Intro=substr ($Intro,0,$pos);
        $Intro.="...";
        }

      $result.="<event id='$NewsID'><title>$row->Title</title><date>$DateOfNews</date><intro>$Intro</intro>";
      $NewsHeadImgURL=$cfg['FilesURL'].'/img/news.Event/imghead';
      $NewsHeadImgPath=$cfg['FilesPath'].'/img/news.Event/imghead';
      $headicos=$qico->Rows["news.headimg/$NewsID"];
      $link_imgopen="";
      if ($headicos) {
        foreach ($headicos as $headicoid=>$headico) {
          if (!$headico->TnNames) {continue;}
          $TnSrcs=explode("|",$headico->TnNames,10);
          if ($TnFormatNo>=count($TnSrcs)) {$TnFormatNo=0;}
          $TnSrc=$TnSrcs[$TnFormatNo];
          $TnFilePath=$NewsHeadImgPath.'/'.$TnSrc;
          $imginfo=@getimagesize($TnFilePath);
          if ($imginfo) {
            $result.="<headico $imginfo[3] src='$NewsHeadImgURL/$TnSrc'>$headico->Caption</headico>";
            $link_imgopen="|newsimg|src=$NewsHeadImgURL/".$TnSrcs[0];
          }
        }
      }

      $hrefopen="";
      $doc=$qdocs->Rows["news/$NewsID"];
      if ($doc) {
        $hrefopen="\n\n<link open=\"slide_news$link_imgopen"
          ."|newsbrowser|call[]=news.XMLReader.LoadNewsList:NewsGroupID=$NewsGroupID,RowsPerPage=4"
          ."|newstext|call[]=news.XMLReader.LoadNewsText:NewsID=$NewsID"
          ."|newsalbum|call[]=img.IImgIndex.LoadXML:BindTo=news.album/$NewsID,TnFormat=B,TargetFrame=newsimg"
          ."\"/>";
        } else {$hrefopen="";}
      if ($row->URL) {$hrefopen=$row->URL;}
      $result.="$hrefopen</event>";
    }
    return array(XML=>$result);
  }

  }
