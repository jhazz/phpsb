<?
class news_IReview
{
var $CopyrightText="(c)2005 JhAZZ Site Builder. News publishing cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $RoleAccess=array(NewsBackend=>"View",ChangeSettings=>"EditGroups");

function Browse($args)
  {
  $_=$GLOBALS['_STRINGS']['news'];
  global $cfg;

  print "<h2>$_[CAPTION_BROWSE_GROUPS]</h2>";
  $q=DBQuery("SELECT JSBPageID,Caption,Title
        FROM jsb_Pages
        WHERE State=1 AND SysContext='".$cfg['Settings']['news']['NewsGroupsContext']."' ORDER BY OrderNo","JSBPageID");

  if ($q)
    {
    $now=time();
    $qc=DBQuery ("SELECT NewsGroupID,PubStatus, COUNT(*) AS PubCount,
      (DateToShow>$now)*1 + (DateToHide<$now)*2 + ((DateToShow<$now) AND (DateToHide>$now)) * 4 AS DateGroup
      FROM news_Events
      GROUP BY NewsGroupID,DateGroup,PubStatus",array("NewsGroupID","DateGroup","PubStatus"));

    print "<table border='0' width='500' cellspacing='1' cellpadding='5'><tr><th>$_[TNEWSEVENT_D_THENEWSGROUP]</th>
      <th colspan='3' width='20%'>$_[PRESENT_DATE]</th>
      <th colspan='3' width='20%'>$_[FUTURE_DATE]</th>
      <th colspan='3' width='20%'>$_[PAST_DATE]</th></tr>";

    foreach ($q->Rows as $JSBPageID=>$r)
      {
      $title="<a href='".ActionURL("news.IGroup.Edit.bm",
        array(NewsGroupID=>$JSBPageID,DateGroup=>-1))."'>".langstr_get($r->Caption)."</a>";
      print "<tr align='center' bgcolor='#ffffff'><td>$title</td>";

      $dg=$qc->Rows[$JSBPageID];
      $flags=false;
      $s="";

      $dge=$dg[4]; # now
      if ($dge)
        {
        $row=$dge[0];  $s.=($row)?"<td bgcolor='#f00000'>$row->PubCount</td>":"<td>&nbsp;</td>";
        $row=$dge[5];  $s.=($row)?"<td bgcolor='#f0f000'>$row->PubCount</td>":"<td>&nbsp;</td>";
        $row=$dge[10]; $s.=($row)?"<td bgcolor='#00f000'>$row->PubCount</td>":"<td>&nbsp;</td>";
        }  else $s.="<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";


      $dge=$dg[1]; # future
      if ($dge)
        {
        $row=$dge[0];  $s.=($row)?"<td bgcolor='#fff0f0'>$row->PubCount</td>":"<td>&nbsp;</td>";
        $row=$dge[5];  $s.=($row)?"<td bgcolor='#fffff0'>$row->PubCount</td>":"<td>&nbsp;</td>";
        $row=$dge[10]; $s.=($row)?"<td bgcolor='#f0fff0'>$row->PubCount</td>":"<td>&nbsp;</td>";
        }  else $s.="<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";

      $dge=$dg[2]; # past
      if ($dge)
        {
        $row=$dge[0];  $s.=($row)?"<td bgcolor='#908080'>$row->PubCount</td>":"<td>&nbsp;</td>";
        $row=$dge[5];  $s.=($row)?"<td bgcolor='#909080'>$row->PubCount</td>":"<td>&nbsp;</td>";
        $row=$dge[10]; $s.=($row)?"<td bgcolor='#809080'>$row->PubCount</td>":"<td>&nbsp;</td>";
        }  else $s.="<td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>";

      print "$s</tr>";
      }
    print "</table>";
    }

  }

}



?>
