<?php
class stdctrls_TRichtext
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. Standard site controls cartridge";
var $CopyrightURL="http://www.jhazz.com/jsb";
var $ComponentVersion="1.0";
var $Subscribers="BindTo";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][stdctrls];
  $this->About=$_[Richtext_ABOUT];
  $this->Propdefs=array(
    Align=>array(Type=>"Align"),
    BindTo=>array(Type=>"Binding",DataType=>"stdctrls.Richtext"),
    CSS_DocumentLink=>array(Type=>"CSS_Class",Caption=>$_[RT_CSS_DOCUMENTLINK],BaseCSSClass=>"a"),
    CSS_DocumentTitle=>array(Type=>"CSS_Class",Caption=>$_[RT_CSS_DOCUMENTLINK],BaseCSSClass=>"p",DefaultValue=>"h2"),
    SeparateToPages=>array(Type=>"Boolean",Caption=>$_[RT_SEPARATE_TO_PAGES]),
    TextIfEmpty=>array(Type=>"String",Caption=>$_[RT_TEXTIFEMPTY]),
#    EditableByOwner=>array(Type=>"Boolean",Caption=>$_[RT_EDITABLE_BY_OWNER]),
    ShowPageTitle=>array(Type=>"Boolean",DefaultValue=>1),
    );
  $this->Datadefs=array(
    Pages =>array(DataType=>"Pages",Caption=>"RichText Pages"),
    );
  }

function AfterInit(&$Control)
  {
  $BindTo=$Control->BindTo;
  $PBindTo=$Control->Properties['BindTo'];
  $Control->OpeningPage=$Control->Arguments['p'];
  if (!$Control->OpeningPage) {$Control->OpeningPage=1;}

  if ($Control->DesignMode)
    {
    global $_CORE;
    if (!$PBindTo)
      {
      $Control->EditableContent=true;
      }
    }

  if (($Control->Properties['EditableByOwner'])&&($PBindTo)&&($BindTo))
    {
    $info=load_document_info($BindTo);
    if ($info)
      {
      $IsOwner=($Control->BoundDocument->OwnerUserID == $_USER->UserID);
      if ($IsOwner)
        {
#        $_ENV->InitWindows();
        $Control->IsUserOwner=true;
        $Control->EditableContent=true;
        }
      }
    }

#    $Control->Data['Pages']=array(PageArray=>&$PageArray,PageNo=>$Control->OpeningPage);
  }

#
# METHOD Render

function Render(&$Control)
  {
  $_=&$GLOBALS[_STRINGS][stdctrls];
  global $cfg;
  extract ($Control->Properties);
  $BindTo=$Control->BindTo;

  if($BindTo)
    {
    $BindToInfo=BindPathInfo($BindTo);
    if (!$BindToInfo) $BindTo=false;
    else $q=DBQuery ("SELECT * FROM stdctrls_Richtexts WHERE BindTo='$BindTo' ORDER BY OrderNo");
    }

  if (($Control->Properties['BindTo'])&&(!$BindTo))
    {
    if ($Control->DesignMode)
      {
      print $_['RT_DYNAMIC_BIND_SAMPLE']." [".$Control->Properties['BindTo'].']';
      return;
      }
    }

  $DummyMode=false;
  if ((!$q)&&(!$Control->Properties['BindTo']))
    {
    # bind to itself on a page
    if ($Control->LayoutControlPath)
      {
      $lq="BindTo='$Control->LayoutControlPath' OR ";
      }
    $q=DBQuery ("SELECT * FROM stdctrls_Richtexts WHERE $lq BindTo='$Control->PageControlPath' ORDER BY OrderNo","DocID");
    if ($q)
      {
      $BindTo=$LayoutBindTo=false;
      foreach ($q->Rows as $DocID=>$doc)
        {
        if ($doc->BindTo==$Control->LayoutControlPath) $LayoutBindTo=$doc->BindTo;
        if ($doc->BindTo==$Control->PageControlPath) $BindTo=$doc->BindTo;
        }
      if (!$BindTo)
        {
        # no page document. Get layout document
        $BindTo=$LayoutBindTo;
        }
      else
        {
        # kill all layout documents if webpage's related document exists
        foreach ($q->Rows as $DocID=>$doc)
          {
          if ($doc->BindTo==$Control->LayoutControlPath) $q->Rows[$DocID]=false;
          }
        }
      }
    else
      {
      $BindTo=$Control->PageControlPath;
      }
    }

  if ($BindTo && !$q)
    {
    print $TextIfEmpty."<br>";
    }

  if (!$ShowPageTitle) $CSS_DocumentTitle=false;

  if ((!$q)&&($Control->DesignMode))
    {
    # PRINT BOUND DUMMY
    if ($Control->Properties['BindTo'])
      {
      print "<p>$_[SITETEXT_TEXT_SAMPLE] ".$Control->Properties['BindTo']."</p>";
      }
    }

  # Print page tabs
  $PageLinks="";
  $PageCount=0;
  $PageIndex=array();
  if ($q) foreach ($q->Rows as $DocID=>$row)
    {
    if (!$row) {continue; }# removed layout
    $PageCount++;
    $PageIndex[$PageCount]=$DocID;
    $link=($SeparateToPages)? $Control->JSBPageID."|".$Control->JSBPageControlID."_p=$PageCount.".$cfg['VirtualExtension'] : "#$Control->JSBPageControlID~$PageNo";
    $Title=$row->Title; if (!$Title) $Title="Unnamed page";
    if (($Control->OpeningPage==$PageCount)&&($SeparateToPages))
      {
      $edit="";
      $PageLinks.="<li>$Title</li>";
      }
    else {
      list($t,$c)=get_css_pair($CSS_DocumentLink,'a');
      $PageLinks.="<li><$t$c href='$link'>$Title</$t></li>";
      }
    }


  print "<table border='0' width='100%' cellpadding='0' cellspacing='0'><tr><td>";
  if ($Control->EditableContent && $SeparateToPages)
    {
    print "<b>$_[RT_SEPARATE_TO_PAGES]</b>";
    }

  if ($PageCount>1) print "<ul>".$PageLinks."</ul>";

  if ($Control->EditableContent)
    {
    print "</td><td width='5%' align='right' valign='bottom' class='bgdown'>";
    if ($PageCount>0) #&&(!$LayoutBindTo))
      {
      $script="W.openModal({url:'".ActionURL("stdctrls.IRichtext.Add.f",
       array(BindTo=>$Control->PageControlPath,NewPageTitle=>"Page ".($PageCount+1)))."',w:700,h:530,reloadOnOk:1});";
      $_ENV->PutButton(array(Caption=>$_['RT_ADD_PAGE'],Kind=>'add',OnClick=>$script));
      }
    else
      {
      $script="W.openModal({url:'".ActionURL("stdctrls.IRichtext.Edit.f",array(BindTo=>$Control->PageControlPath))."',w:700,h:530,reloadOnOk:1});";
      $_ENV->PutButton(array(Caption=>$_['RT_EDITCONTENT'],OnClick=>$script));
      }
    }
  print "</td></tr></table>";

  # print all pages if not separated mode
  $PageOpening=$Control->OpeningPage;
  if (!$PageOpening) $PageOpening=1;

  if ($SeparateToPages)
    {
    $doc=$q->Rows[$PageIndex[$PageOpening]];
    $this->PrintDocument($doc,$CSS_DocumentTitle);
    }
  else
    {
    foreach ($PageIndex as $PageNo=>$doc)
      {
      $doc=$q->Rows[$PageIndex[$PageNo]];
      print "<a name='$Control->JSBPageControlID~$PageNo' ></a>";

      if ($Control->EditableContent)
        {
        $script="W.openModal({url:'".ActionURL("stdctrls.IRichtext.Edit.f",
         array(DocID=>$doc->DocID, BindTo=>$Control->PageControlPath))."',w:700,h:530,reloadOnOk:1});";
        $Buttons=$_ENV->PutButton(array(Caption=>$_['RT_EDITCONTENT'],OnClick=>$script,ToString=>1));

        $script="if (confirm('Are you sure?')) W.openModal({url:'".ActionURL("stdctrls.IRichtext.Remove_Page.f",
         array(BindTo=>$Control->PageControlPath,DocID=>$doc->DocID))."',w:700,h:530,reloadOnOk:1});";
        $Buttons.=$_ENV->PutButton(array(Caption=>$_['RT_DELETEPAGE'],Kind=>'delete',OnClick=>$script,ToString=>1));
        }

      $this->PrintDocument($doc,$CSS_DocumentTitle,$Buttons);
      }
    }
  }

function PrintDocument(&$doc,$CSS_DocumentTitle,$Buttons=false)
  {
  global $cfg;
  if (($CSS_DocumentTitle)&&($doc->Title))
    {
    list($t,$c)=get_css_pair($CSS_DocumentTitle,'p');
    print "<$t$c>$doc->Title</$t>";
    }
#  $s=preg_replace ("/\[(.*)\]/", $doc->Content;
  
  print $doc->Content;
  if ($Buttons) { print "<div class='bgdown' align='right'>$Buttons</div>"; }
  }
}

?>
