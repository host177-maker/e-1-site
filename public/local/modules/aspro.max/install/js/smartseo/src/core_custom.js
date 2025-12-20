BX.adminTabControl.prototype.PreInit = function (bSkipInit)
{
  for (var tab = 0; tab < this.aTabs.length; tab++)
  {
    this.aTabs[tab].CONTENT = BX(this.aTabs[tab]["DIV"]);

    var tbl = BX(this.aTabs[tab]["DIV"] + '_edit_table');
    if (!!tbl)
    {
      for (var k = 0; k < tbl.tBodies.length; k++)
      {
        var n = tbl.tBodies[k].rows.length;
        for (var i = 0; i < n; i++)
        {
          if (tbl.tBodies[k].rows[i].cells.length > 1)
          {
            BX.addClass(tbl.tBodies[k].rows[i].cells[0], 'adm-detail-content-cell-l');
            BX.addClass(tbl.tBodies[k].rows[i].cells[1], 'adm-detail-content-cell-r');
          }
        }
      }

      this.aTabs[tab].EDIT_TABLE = tbl;
      this.aTabs[tab].CONTENT_BLOCK = tbl.parentNode;
    }
  }

  if (!bSkipInit)
  {
    BX.ready(BX.defer(this.Init, this));
  }
}

BX.CFixer.prototype.Start = function()
{
	if (this.bStarted)
		return;

	this.pos = BX.pos(this.node);

	BX.bind(window, 'scroll', BX.proxy(this._scroll_listener, this));
	BX.bind(window, 'resize', BX.proxy(this._scroll_listener, this));
	BX.bind(window, 'resize', BX.proxy(this._recalc_pos, this));

	BX.addCustomEvent('onAdminFilterToggleRow', BX.proxy(this._recalc_pos, this));
	BX.addCustomEvent('onAdminFilterToggleRow', BX.proxy(this._scroll_listener, this));
	BX.addCustomEvent('onAdminPanelFix', BX.defer(this._scroll_listener, this));
	BX.addCustomEvent('onAdminPanelChange', BX.defer(this._scroll_listener, this));
  BX.addCustomEvent('onAdminTabsChange', BX.defer(this._recalc_pos, this));
	BX.addCustomEvent(BX.adminMenu, 'onAdminMenuResize', BX.proxy(this._recalc_pos, this)); 
 
	this._scroll_listener();

	this.bStarted = true;
}

BX.CFixer.prototype._recalc_pos = function()
{
	this.pos = BX.pos(this.gutter || this.node);
	var node_pos = BX.pos(this.node);

	if (this.bFixed)
	{
		if (this.params.type == 'top' || this.params.type == 'bottom')
		{
			this.node.style.width = this.pos.width + 'px';
      //this.gutter.style.height = node_pos.height + 'px';
		}
	}

	this._scroll_listener();
};