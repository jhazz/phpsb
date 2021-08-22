var traceWindow=false;
function traceText(text,type,toffs)
{
var c='t'+type; if (type==4){restart=true;}
if (!traceWindow)
  {
  var w=screen.width/3,h=150;
  var restart=true;
  traceWindow=window.open("","traceWindow","alwaysRaised=yes,resizable=yes,scrollbars=yes,toolbar=no,left=0,top="+(screen.height-55-h)+",width="+w+",height="+h);
  if (!traceWindow) {return;} // popup possibly blocked
  try
    {
    if (traceWindow.lasttime!=undefined)
      {
      var d=new Date();
      var now=d.getTime();
      if ((now-traceWindow.lasttime)>10000)  // 10 seconds to reset
        restart=true
      else
        {
        restart=false;
        }
      }
    } catch(e){}
  }

if (restart)
  {
//  traceWindow.focus(); DO NOT DO IT!!!!!
  var d=traceWindow.document;
  var s="<scr"+"ipt>"
  +"var lasttime;"
  +"function resetTime(){var d=new Date(); lasttime=d.getTime();}resetTime();"
  +"function typetext(s,c,toffs)"
    +"{resetTime(); var e=document.createElement('DIV');"
    +"e.innerHTML='<table width=100% border=0 cellspacing=1 cellpadding=1><tr valign=top><td width=1% class=toffs>'+toffs+'</td><td class='+c+'>'+s+'</td></tr></table>';"
    +"document.body.appendChild(e);"
    +"window.scrollTo(0,document.body.scrollHeight);}</scr"+"ipt><style>"


  +".overt{color:#101010;text-align:right;}"
  +".toffs{background-color:#c8c4c0; font-size:9px; color:#444444; font-family:arial,helvetica;text-align:right;}"
  +".t0{background-color:#e8e8e8; font-size:9px; color:#000000; font-family:verdana,arial,helvetica;}"
  +".t1{background-color:#707070; font-size:9px; color:#ffffff; font-family:verdana,arial,helvetica; font-weight:bold;}"
  +".t2{background-color:#f8f0e8; font-size:9px; color:#a00000; font-family:verdana,arial,helvetica; font-weight:bold;}"
  +".t3{background-color:#f0f0e8; font-size:9px; color:#000000; font-family:arial,helvetica; }</style>"
  +"<body leftmargin=0 topmargin=0 marginwidth=0 marginheight=0><title>"+siteName+" - Trace window</title>";
  d.open();
  d.write(s);
  d.close();
  }
traceWindow.typetext(text,c,toffs);
}