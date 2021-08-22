class phpsbFlash.DockablePanel extends phpsbFlash.Control
{
	#include "ComponentVersion.as"
	static var symbolName:String = "DockablePanel";
	static var symbolOwner:Object = phpsbFlash.DockablePanel;
	var className:String = "DockablePanel";

	var _collapsed:Boolean=false;
	var dockedIn:Object;
	var stackedPos:Number;
	var stacked:Boolean;
	var mcPanelTitle_bg:MovieClip;
	var mcPanelTitle:MovieClip;
	var dockWidth:Number, dockHeight:Number;
	var heightSeparator:phpsbFlash.Separator;
	var widthSeparator:phpsbFlash.Separator;
	var btnCollapse:MovieClip;
	var window:MovieClip;

	[Inspectable(defaultValue="right", enumeration="top,right,bottom,left,float", type="String")]
	public var sideName:String;

	[Inspectable(defaultValue="100", type="Number")]
	public var minWidth:Number;

	[Inspectable(defaultValue="100", type="Number")]
	public var minHeight:Number;
	
	[Inspectable(defaultValue="false", type="Boolean")]
	function set collapsed(v:Boolean){
		_collapsed=v; 
		redraw();
	}
	function get collapsed():Boolean {return _collapsed};
	
	public var _caption:String;
	[Inspectable(defaultValue="Panel", type="String")]
	function set caption(v:String) {_caption=v;redraw();}
	function get caption():String {return _caption;}

	function init()
	{
		super.init();
		dockWidth=minWidth;
		dockHeight=minHeight;
		mcPanelTitle=attachMovie("skinPanelTitle","mcPanelTitle",2,{_x:0,_y:-20});
		mcPanelTitle_bg=mcPanelTitle.panelTitle_bg;
		btnCollapse=mcPanelTitle.btnCollapse;
		btnCollapse.onRollOver=onCollapseRollOver;
		btnCollapse.onRollOut=onCollapseRollOut;
		btnCollapse.onPress=onCollapsePress;
		btnCollapse.panel=this;
	}
	function onMouseWheel(delta,x,y)
	{
		trace (caption+" mouseWheel "+x+","+y);
	}
	
	function onCollapsePress()
	{
		var p:phpsbFlash.DockablePanel=phpsbFlash.DockablePanel(MovieClip(this).panel);
		p._collapsed=!p._collapsed;
		p.draw();
		p.dispatcher.rearrangeDockSides();
	}
	function onCollapseRollOver()
	{
		if (MovieClip(this).panel._collapsed) gotoAndStop(4); else gotoAndStop(2);
	}
	function onCollapseRollOut()
	{
		if (MovieClip(this).panel._collapsed) gotoAndStop(3); else gotoAndStop(1);
	}
	function size()
	{
		
		if (dockedIn!=undefined)
		{
			dockWidth=width;
			dockHeight=height+phpsbFlash.Dispatcher.PANEL_TITLE_HEIGHT;
		}
		btnCollapse._x=width-16;
		mcPanelTitle.panelLabel._width=width-30;
		super.size();
		mcPanelTitle_bg._width=width;
		window._width=width;
		window._height=height;
		window._x=window._y=0;
	}
	
	function draw()
	{
		super.draw();
		mcPanelTitle.panelLabel.text=_caption;
		
		window._visible=!_collapsed;
		if (_collapsed) 
		{
			btnCollapse.gotoAndStop(3); 
		}
		else
		{
			btnCollapse.gotoAndStop(1);			
		}
	}
}