gcCalendar.panel.Home = function (config) {
    config = config || {};
    Ext.apply(config, {
        border: false,
        baseCls: 'modx-formpanel',
        cls: 'container',
        items: [
            {
                html: '<h2>' + _('gccalendar.management') + '</h2>',
                border: false,
                cls: 'modx-page-header',
            },
            {
                xtype: 'modx-tabs',
                defaults: {border: false, autoHeight: true},
                border: true,
                items: [
                    {
                        title: _('gccalendar.event_list'),
                        defaults: {autoHeight: true},
                        items: [
                            {
                                html: '<p>' + _('gccalendar.event_management_desc') + '</p>',
                                border: false,
                                bodyCssClass: 'panel-desc',
                            },
                            {
                                xtype: 'gcCalendar-grid-gcCalendar',
                                cls: 'main-wrapper',
                                preventRender: true,
                            },
                        ],
                        listeners: {
                            activate: function () {
                                Ext.getCmp('gcCalendar-grid-gcCalendar').refresh();
                            },
                        },
                    },
                    {
                        title: _('gccalendar.ensible'),
                        defaults: {autoHeight: true},
                        items: [
                            {
                                html: '<p>' + _('gccalendar.ensible_management_desc') + '</p>',
                                border: false,
                                bodyCssClass: 'panel-desc',
                            },
                            {
                                xtype: 'calendar-remote',
                                cls: 'main-wrapper',
                                preventRender: true,
                            },
                        ],
                        listeners: {
                            activate: function () {
                                Ext.getCmp('calendar-remote-calendar').getActiveView().refresh(true);
                            },
                        },
                    },
                    {
                        title: _('gccalendar.s'),
                        defaults: {autoHeight: true},
                        items: [
                            {
                                html: '<p>' + _('gccalendar.cal_management_desc') + '</p>',
                                border: false,
                                bodyCssClass: 'panel-desc',
                            },
                            {
                                xtype: 'gcCalendar-grid-gcCalendarCals',
                                cls: 'main-wrapper',
                                preventRender: true,
                            },
                        ],
                    },
                    {
                        title: _('gccalendar.categories'),
                        defaults: {autoHeight: true},
                        items: [
                            {
                                html: '<p>' + _('gccalendar.cat_management_desc') + '</p>',
                                border: false,
                                bodyCssClass: 'panel-desc',
                            },
                            {
                                xtype: 'gcCalendar-grid-gcCalendarCats',
                                cls: 'main-wrapper',
                                preventRender: true,
                            },
                        ],
                    },
                ],
                // only to redo the grid layout after the content is rendered
                // to fix overflow components' panels, especially when scroll bar is shown up
                listeners: {
                    afterrender: function (tabPanel) {
                        tabPanel.doLayout();
                    },
                },
            },
        ],
    });
    gcCalendar.panel.Home.superclass.constructor.call(this, config);
};
Ext.extend(gcCalendar.panel.Home, MODx.Panel);
Ext.reg('gcCalendar-panel-home', gcCalendar.panel.Home);
