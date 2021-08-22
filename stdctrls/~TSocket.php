<?php
class stdctrls_TSocket
{
var $CopyrightText="(c)2003 JhAZZ Site Builder. News system cartridge";
var $CopyrightURL="www.jhazz.com/jsb";
var $ComponentVersion="1.0";

function InitComponent()
  {
  $_=&$GLOBALS[_STRINGS][stdctrls];
  $this->About=$_[TSOCKET_ABOUT];
  $this->Propdefs=array(
    SocketAddress=>array(Type=>"String",Caption=>$_[TSOCKET_SOCKETADDRESS]),
    );
  $this->Datadefs=array(
    SocketAddress=>array(DataType=>"Socket",Caption=>$_[TSOCKET_SOCKETADDRESS]),
    );
  }

function Init(&$Control)
  {
  $SocketAddress=$Control->Properties['SocketAddress'];
  $Control->Data['SocketAddress']=$SocketAddress;
  }
function Render(&$Control)
  {
  $SocketAddress=$Control->Data['SocketAddress'];
  if ($Control->DesignMode)
    {
    print "Providing socket address: '$SocketAddress'";
    }
  }
}
