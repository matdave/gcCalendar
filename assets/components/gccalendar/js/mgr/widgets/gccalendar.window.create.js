gcCalendar.window.CreategcCalendar = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        title: _('gccalendar.gcCalendar_create'),
        autoHeight: false,
        height: Ext.getBody().getViewSize().height * 0.75,
        width: Ext.getBody().getViewSize().width * 0.65,
        autoScroll: false,
        url: gcCalendar.config.connectorUrl,
        xtype: 'form',
        layout: 'fit',
        closeAction: 'close',
        anchor: '100%',
        plain: true,
        baseParams: {
            action: 'mgr/gcCalendar/create',
        },
        defaults: {
            layout: 'form',
            defaultType: 'textfield',
            labelAlign: 'left',
            anchor: '100%',
            labelStyle: 'float:left;',
            labelWidth: '100',
        },
        fields: [
            {
                xtype: 'container',
                layout: 'border',
                plain: true,
                autoHeight: false,
                autoScroll: true,
                anchor: '100%',
                height: '100%',
                items: [
                    {
                        id: 'create-left-col',
                        xtype: 'panel',
                        margins: '3 3 3 0',
                        region: 'west',
                        width: 350,
                        split: false,
                        collapsible: true,
                        autoHeight: false,
                        autoScroll: true,
                        height: '100%',
                        style: {borderRight: '1px solid #c3c3c3'},
                        title: 'Time Information',
                        items: [
                            {
                                xtype: 'container',
                                layout: 'column',
                                border: false,
                                defaults: {
                                    // applied to each contained item
                                    // nothing this time
                                    anchor: '100%',
                                    layout: 'form',
                                    labelWidth: '100',
                                    cellCls: 'valign-center',
                                },
                                anchor: '95%',
                                autoHeight: true,
                                style: {marginTop: '15px', paddingLeft: '10px'},
                                items: [
                                    {
                                        split: false,
                                        columnWidth: 1,
                                        items: [
                                            {
                                                xtype: 'textfield',
                                                fieldLabel: _('gccalendar.title'),
                                                name: 'title',
                                                anchor: '100%',
                                                value: config.record.title,
                                            },
                                            {
                                                xtype: 'checkbox',
                                                labelStyle: 'float:left;',
                                                fieldLabel: _('gccalendar.ad'),
                                                name: 'ad',
                                                id: 'createallday',
                                                checked: config.record.ad ? true : false,
                                                listeners: {
                                                    check: {
                                                        fn: function (tht, value) {
                                                            var adFlag = Ext.getCmp('createallday');
                                                            var ed = Ext.getCmp('createtimestart');
                                                            var sd = Ext.getCmp('createtimeend');

                                                            //Ext.getCmp('cstartdate_fields').label.update(('mxcalendars.startdate_col_label'));

                                                            if (adFlag.getValue() === true) {
                                                                if (ed.getValue() !== sd.getValue()) {
                                                                    ed.setValue(sd.getValue());
                                                                }
                                                                ed.hide();
                                                                sd.hide();
                                                            } else {
                                                                sd.show();
                                                                ed.show();
                                                            }
                                                        },
                                                    },
                                                },
                                            },
                                            {fieldLabel: _('gccalendar.start')},
                                            {
                                                xtype: 'container',
                                                layout: 'column',
                                                anchor: '100%',
                                                items: [
                                                    {
                                                        xtype: 'datefield',
                                                        anchor: '50%',
                                                        split: true,
                                                        minValue: new Date().clearTime(),
                                                        columnWidth: 0.5,
                                                        name: 'startymd',
                                                        id: 'createdatestart',
                                                        value: config.record.startymd,
                                                        listeners: {
                                                            change: {
                                                                fn: function (e, nv, ov) {
                                                                    var ed = Ext.getCmp('createdateend');
                                                                    console.log('ED:' + ed.getValue());
                                                                    if (ed.getValue() == '') {
                                                                        ed.setValue(nv);
                                                                    }
                                                                },
                                                            },
                                                        },
                                                    },
                                                    {
                                                        xtype: 'timefield',
                                                        anchor: '50%',
                                                        width: '100%',
                                                        split: true,
                                                        columnWidth: 0.5,
                                                        hidden: false,
                                                        name: 'starthis',
                                                        id: 'createtimestart',
                                                        value: config.record.starthis,
                                                    },
                                                ],
                                            },
                                            {
                                                fieldLabel: _('gccalendar.end'),
                                                labelStyle: 'margin-top:10px;display: block;margin-bottom: 0;',
                                            },
                                            {
                                                xtype: 'container',
                                                layout: 'column',
                                                anchor: '100%',
                                                items: [
                                                    {
                                                        xtype: 'datefield',
                                                        minValue: new Date().clearTime(),
                                                        anchor: '50%',
                                                        split: true,
                                                        columnWidth: 0.5,
                                                        name: 'endymd',
                                                        id: 'createdateend',
                                                        value: config.record.endymd,
                                                    },
                                                    {
                                                        xtype: 'timefield',
                                                        anchor: '50%',
                                                        width: '100%',
                                                        split: true,
                                                        hidden: false,
                                                        columnWidth: 0.5,
                                                        name: 'endhis',
                                                        id: 'createtimeend',
                                                        value: config.record.endhis,
                                                    },
                                                ],
                                            },
                                            {
                                                xtype: 'superboxselect',
                                                displayField: 'title',
                                                valueField: 'id',
                                                forceSelection: true,
                                                labelStyle: 'margin-top:10px;display: block;margin-bottom: 0;',
                                                store: new Ext.data.JsonStore({
                                                    root: 'results',
                                                    idProperty: 'id',
                                                    url: gcCalendar.config.connectorUrl,
                                                    baseParams: {
                                                        action: 'mgr/store/getCals',
                                                    },
                                                    fields: ['id', 'title'],
                                                }),
                                                mode: 'remote',
                                                triggerAction: 'all',
                                                fieldLabel: _('gccalendar.calendar'),
                                                name: 'cid[]',
                                                hiddenName: 'cid[]',
                                                id: 'createcid',
                                                allowBlank: false,
                                                typeAhead: true,
                                                minChars: 1,
                                                emptyText: 'Select Calendar',
                                                valueNotFoundText: 'Calendar Not Found',
                                                anchor: '100%',
                                                value: config.record.cid,
                                            },
                                            {
                                                xtype: 'superboxselect',
                                                displayField: 'ctitle',
                                                valueField: 'id',
                                                forceSelection: true,
                                                allowAddNewData: true,
                                                addNewDataOnBlur: true,
                                                store: new Ext.data.JsonStore({
                                                    root: 'results',
                                                    idProperty: 'id',
                                                    url: gcCalendar.config.connectorUrl,
                                                    baseParams: {
                                                        action: 'mgr/store/getCategories',
                                                    },
                                                    fields: [
                                                        {name: 'id', type: 'int'},
                                                        {name: 'ctitle', type: 'string'},
                                                    ],
                                                }),
                                                mode: 'remote',
                                                triggerAction: 'all',
                                                fieldLabel: _('gccalendar.category'),
                                                name: 'cat[]',
                                                hiddenName: 'cat[]',
                                                id: 'createcat',
                                                allowBlank: false,
                                                typeAhead: true,
                                                minChars: 1,
                                                emptyText: 'Select Category',
                                                valueNotFoundText: 'Category Not Found',
                                                anchor: '100%',
                                                value: config.record.cat,
                                                listeners: {
                                                    newitem: function (bs, v, f) {
                                                        v = v + '';
                                                        v = v.slice(0, 1).toUpperCase() + v.slice(1).toLowerCase();
                                                        var newObj = {
                                                            ctitle: v,
                                                        };
                                                        bs.addNewItem(newObj);
                                                        //console.log(gcCalendar.config.connectorUrl + v);
                                                        var conurl = gcCalendar.config.connectorUrl;
                                                        var catData = {
                                                            ctitle: v,
                                                            HTTP_MODAUTH: MODx.siteId,
                                                            action: 'mgr/gcCalendar/createCats',
                                                        };

                                                        Ext.Ajax.request({
                                                            url: conurl,
                                                            params: catData,
                                                            scope: this,
                                                            success: function (response, opts) {
                                                                //console.log('Success.');
                                                            },
                                                            failure: function (response, opts) {
                                                                //console.log('Failure.');
                                                            },
                                                        });
                                                    },
                                                },
                                            },
                                        ],
                                    },
                                ],
                            },
                        ],
                    },
                    {
                        xtype: 'tabpanel',
                        region: 'center',
                        defaults: {border: false, autoHeight: true},
                        border: true,
                        anchor: '95%',
                        autoHeight: true,
                        margins: '3 3 3 0',
                        padding: '5px',
                        activeTab: 0,
                        items: [
                            {
                                title: 'Details',
                                id: 'create-details-tab',
                                items: [
                                    {
                                        xtype: 'panel',
                                        layout: 'form',
                                        border: false,
                                        defaults: {
                                            // applied to each contained item
                                            // nothing this time
                                            anchor: '100%',
                                            layout: 'form',
                                            labelWidth: '100',
                                            cellCls: 'valign-center',
                                        },
                                        anchor: '95%',
                                        autoHeight: true,
                                        items: [
                                            {
                                                xtype: 'hidden',
                                                name: 'previmage',
                                                id: 'createupdate-image',
                                                value: config.record.previmage,
                                            },
                                            {fieldLabel: 'Image'},
                                            {
                                                xtype: 'box',
                                                id: 'createphotoPreview',
                                                anchor: 0,
                                                hidden: !config.record.previmage,
                                                autoEl: {
                                                    tag: 'img',
                                                    src: config.record.previmage,
                                                    style: {height: '175px'},
                                                },
                                            },
                                            {
                                                xtype: 'container',
                                                layout: 'column',
                                                anchor: '95%',
                                                items: [
                                                    {
                                                        xtype: 'button',
                                                        anchor: '50%',
                                                        id: 'createupdate-image-button',
                                                        columnWidth: 0.5,
                                                        split: true,
                                                        cls: 'x-btn-text bmenu',
                                                        style: {marginBottom: '15px'},
                                                        disabled: false,
                                                        text: 'Browse Images',
                                                        listeners: {
                                                            click: {
                                                                fn: function (btn) {
                                                                    if (Ext.isEmpty(this.browser)) {
                                                                        this.browser = MODx.load({
                                                                            xtype: 'modx-browser',
                                                                            returnEl: null,
                                                                            id: 'createupdate-image-browser',
                                                                            multiple: true,
                                                                            config: MODx.config,
                                                                            source: MODx.config.default_media_source || MODx.source,
                                                                            allowedFileTypes: 'gif,jpg,jpeg,png',
                                                                            listeners: {
                                                                                select: {
                                                                                    fn: function (data) {
                                                                                        Ext.getCmp('createupdate-image').setValue(
                                                                                            '/' + data.fullRelativeUrl,
                                                                                        );
                                                                                        Ext.getCmp('createphotoPreview').el.dom.src =
                                                                                            '/' + data.fullRelativeUrl;
                                                                                        Ext.getCmp('createphotoPreview').show();
                                                                                        Ext.getCmp('createupdate-photo-remove').show();
                                                                                        Ext.getCmp('create-details-tab').show();
                                                                                        //alert(Ext.encode(data));
                                                                                    },
                                                                                    scope: this,
                                                                                },
                                                                            },
                                                                        });
                                                                    }
                                                                    this.browser.show(btn);
                                                                    return true;
                                                                },
                                                                scope: this,
                                                            },
                                                        },
                                                    },
                                                    {
                                                        xtype: 'button',
                                                        anchor: '50%',
                                                        hidden: !config.record.previmage,
                                                        columnWidth: 0.5,
                                                        split: true,
                                                        id: 'createupdate-photo-remove',
                                                        cls: 'x-btn-text bmenu',
                                                        style: {marginBottom: '15px'},
                                                        disabled: false,
                                                        text: 'Clear Image',
                                                        listeners: {
                                                            click: {
                                                                fn: function (btn) {
                                                                    Ext.getCmp('createupdate-image').setValue('');
                                                                    Ext.getCmp('createphotoPreview').el.dom.src = '';
                                                                    Ext.getCmp('createphotoPreview').hide();
                                                                    Ext.getCmp('createupdate-photo-remove').hide();
                                                                    Ext.getCmp('create-details-tab').show();

                                                                    return true;
                                                                },
                                                                scope: this,
                                                            },
                                                        },
                                                    },
                                                ],
                                            },
                                            {
                                                xtype: 'textfield',

                                                fieldLabel: _('gccalendar.url'),
                                                name: 'link',
                                                anchor: '95%',
                                                value: config.record.link,
                                            },
                                            {
                                                fieldLabel: _('gccalendar.notes'),
                                                xtype: 'textarea',
                                                name: 'notes',
                                                id: 'create-notes' + config.record.window,
                                                anchor: '95%',
                                                value: config.record.notes,
                                            },
                                        ],
                                    },
                                ],
                            },
                            {
                                title: 'Location',
                                id: 'create-location-tab',
                                items: [
                                    {
                                        xtype: 'panel',
                                        layout: 'form',
                                        border: false,
                                        defaults: {
                                            // applied to each contained item
                                            // nothing this time
                                            anchor: '100%',
                                            layout: 'form',
                                            labelWidth: '100',
                                            cellCls: 'valign-center',
                                        },
                                        anchor: '95%',
                                        autoHeight: true,
                                        items: [
                                            {
                                                fieldLabel: 'Contact Name',
                                                xtype: 'textfield',
                                                name: 'locationcontact',
                                                value: config.record.locationcontact,
                                            },
                                            {
                                                fieldLabel: 'Contact Phone',
                                                xtype: 'textfield',
                                                name: 'locationphone',
                                                value: config.record.locationphone,
                                            },
                                            {
                                                fieldLabel: 'Contact Email',
                                                xtype: 'textfield',
                                                name: 'locationemail',
                                                value: config.record.locationemail,
                                                vtype: 'email',
                                            },
                                            {
                                                fieldLabel: 'Location Name',
                                                xtype: 'textfield',
                                                name: 'locationname',
                                                value: config.record.locationname,
                                            },
                                            {
                                                fieldLabel: 'Address',
                                                xtype: 'textfield',
                                                name: 'locationaddr',
                                                value: config.record.locationaddr,
                                            },
                                            {
                                                xtype: 'container',
                                                layout: 'column',
                                                border: false,
                                                defaults: {
                                                    // applied to each contained item
                                                    // nothing this time
                                                    anchor: '100%',
                                                    layout: 'form',
                                                    labelWidth: '100',
                                                    cellCls: 'valign-left',
                                                },
                                                anchor: '100%',
                                                autoHeight: true,
                                                style: {marginTop: '15px'},
                                                items: [
                                                    {
                                                        split: true,
                                                        columnWidth: 0.5,
                                                        html: '<label class="x-form-item-label">City</label>',
                                                    },
                                                    {
                                                        split: true,
                                                        columnWidth: 0.2,
                                                        html: '<label class="x-form-item-label">State</label>',
                                                    },
                                                    {
                                                        split: true,
                                                        html: '<label class="x-form-item-label">Zip</label>',
                                                    },
                                                    {
                                                        split: true,
                                                        columnWidth: 0.5,
                                                        xtype: 'textfield',
                                                        name: 'locationcity',
                                                        value: config.record.locationcity,
                                                    },
                                                    {
                                                        split: true,
                                                        columnWidth: 0.2,
                                                        xtype: 'textfield',
                                                        name: 'locationstate',
                                                        value: config.record.locationstate,
                                                    },
                                                    {
                                                        split: true,
                                                        columnWidth: 0.3,
                                                        xtype: 'textfield',
                                                        name: 'locationzip',
                                                        value: config.record.locationzip,
                                                    },
                                                ],
                                            },
                                        ],
                                    },
                                ],
                            }
                        ],
                    },
                ],
            },
        ],
    });
    gcCalendar.window.CreategcCalendar.superclass.constructor.call(this, config);
    this.on('afterrender', function () {
        MODx.loadRTE('create-notes' + config.record.window);
        //get rid of scroll bars
        var w = this.getWidth() + 2;
        this.setWidth(w);
    });
};
Ext.extend(gcCalendar.window.CreategcCalendar, MODx.Window);
Ext.reg('gcCalendar-window-gcCalendar-create', gcCalendar.window.CreategcCalendar);