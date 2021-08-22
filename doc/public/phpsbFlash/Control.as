class phpsbFlash.Control extends MovieClip
{
	#include "ComponentVersion.as"
	static var symbolName:String = "Control";
	static var symbolOwner:Object = phpsbFlash.Control;
	var className:String = "Control";
	private var invalidateFlag:Boolean=false;
	private var initialized:Boolean;
	private var eventListeners:Array;
	private var __width:Number;
	private var __height:Number;
	public var width:Number;
	public var cursor:String;
	public var height:Number;
	var dispatcher:phpsbFlash.Dispatcher;
	
	private var doLaters:Array;
	private var doLatersNext:Array;

	function Control()
	{
		init();
		initialized=true;
		if (invalidateFlag==true)	draw();
	}
	
	function init()
	{
		dispatcher=_root.dispatcher;
		width=__width = _width;
		height=__height = _height;
		_xscale = _yscale=100;
	}

	function invalidate()
	{
		invalidateFlag=true;
		onEnterFrame = doLaterDispatcher;
	}
	
	function setSize(w:Number, h:Number)
	{
		width=w;
		height=h;
		size();
	}

	// receives message to draw
	function draw()
	{
		// if any data changed and its representation should be redrawn
	}
	function redraw()
	{
		if (initialized==true) draw(); else	invalidateFlag=true;
	}
	function size()
	{
		// rearrange content
	}
	/**
	*
	**/
	function addEventListener(eventType:String, func, obj) {
		if (eventListeners==undefined) eventListeners=new Array();
		if (eventListeners[eventType] == undefined) {
			eventListeners[eventType] = new Array();
		}
		if (obj==undefined) obj=this;
		eventListeners[eventType].push({o:obj, f:func});
	}

	function dispatchEvent(eventObj:Object) {
		var q = eventListeners[eventObj.type];
		if (q != undefined) {
			for (var i in q) {
				var handler = q[i];
				handler.o[handler.f](eventObj);
			}
		}
	}

	function removeEventListener(event:String, func, obj):Void {
		if (eventListeners[event] != undefined) {
			if (obj==undefined) obj=this;
			var l:Number = eventListeners.length;
			var i:Number;
			for (i=0; i<l; i++) {
				var handler = eventListeners[i];
				if ((handler.o == obj) && (handler.f == func)) {
					eventListeners.splice(i, 1);
					return;
				}
			}
		}
	}
	
	function doLater(funcName:String, args, earlier:Boolean,checkExist:Boolean):Boolean
	{
		var e:Array;
		var i:Number;
		if (!doLatersNext)
		{
			doLatersNext = new Array();
		}
		else
		{
			if ((doLatersNext.length>0)&&(checkExist==true))
			{
				for (i in doLatersNext)
				{
					e=doLatersNext[i];
					if (e[0]==funcName)
					{
						return false;
					}
				}
			}
		}
		
		if (earlier==true)
		{
			doLatersNext.unshift([funcName, args]);
		}
		else
		{
			doLatersNext.push([funcName, args]);
		}
		onEnterFrame = doLaterDispatcher;
		return true;
	}

	function doLaterDispatcher()
	{
		var fa:Object;
		delete onEnterFrame;
		if (invalidateFlag)
		{
			invalidateFlag=false;
			redraw();
		}
		if ((doLatersNext==undefined)||(doLatersNext.length==0)) return;
		doLaters=doLatersNext;
		doLatersNext=new Array();
		while (fa=doLaters.shift())
		{
			this[fa[0]](fa[1]);
		}
	}

	

}