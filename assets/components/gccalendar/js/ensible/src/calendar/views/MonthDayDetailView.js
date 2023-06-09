/*!
 * Extensible 1.0.2
 * Copyright(c) 2010-2012 Extensible, LLC
 * licensing@ext.ensible.com
 * http://ext.ensible.com
 */
/*
 * This is the view used internally by the panel that displays overflow events in the
 * month view. Anytime a day cell cannot display all of its events, it automatically displays
 * a link at the bottom to view all events for that day. When clicked, a panel pops up that
 * uses this view to display the events for that day.
 */
Ext.ensible.cal.MonthDayDetailView = Ext.extend(Ext.BoxComponent, {
    initComponent: function () {
        Ext.ensible.cal.CalendarView.superclass.initComponent.call(this);

        this.addEvents({
            eventsrendered: true,
        });

        if (!this.el) {
            this.el = document.createElement('div');
        }
    },

    afterRender: function () {
        this.tpl = this.getTemplate();

        Ext.ensible.cal.MonthDayDetailView.superclass.afterRender.call(this);

        this.el.on({
            click: this.view.onClick,
            mouseover: this.view.onMouseOver,
            mouseout: this.view.onMouseOut,
            scope: this.view,
        });
    },

    getTemplate: function () {
        if (!this.tpl) {
            this.tpl = new Ext.XTemplate(
                '<div class="ext-cal-mdv x-unselectable">',
                '<table class="ext-cal-mvd-tbl" cellpadding="0" cellspacing="0">',
                '<tbody>',
                '<tpl for=".">',
                '<tr><td class="ext-cal-ev">{markup}</td></tr>',
                '</tpl>',
                '</tbody>',
                '</table>',
                '</div>',
            );
        }
        this.tpl.compile();
        return this.tpl;
    },

    update: function (dt) {
        this.date = dt;
        this.refresh();
    },

    refresh: function () {
        if (!this.rendered) {
            return;
        }
        var eventTpl = this.view.getEventTemplate(),
            templateData = [];

        evts = this.store.queryBy(function (rec) {
            var thisDt = this.date.clearTime(true).getTime(),
                M = Ext.ensible.cal.EventMappings,
                recStart = rec.data[M.StartDate.name].clearTime(true).getTime(),
                startsOnDate = thisDt == recStart,
                spansDate = false,
                calId = rec.data[M.CalendarId.name],
                calRec = this.calendarStore ? this.calendarStore.getById(calId) : null;

            if (calRec && calRec.data[Ext.ensible.cal.CalendarMappings.IsHidden.name] === true) {
                // if the event is on a hidden calendar then no need to test the date boundaries
                return false;
            }

            if (!startsOnDate) {
                var recEnd = rec.data[Ext.ensible.cal.EventMappings.EndDate.name].clearTime(true).getTime();
                spansDate = recStart < thisDt && recEnd >= thisDt;
            }
            return startsOnDate || spansDate;
        }, this);

        Ext.ensible.cal.CalendarView.prototype.sortEventRecordsForDay.call(this, evts);

        evts.each(function (evt) {
            var item = evt.data,
                M = Ext.ensible.cal.EventMappings;

            item._renderAsAllDay =
                item[M.IsAllDay.name] ||
                Ext.ensible.Date.diffDays(item[M.StartDate.name], item[M.EndDate.name]) > 0;
            item.spanLeft = Ext.ensible.Date.diffDays(item[M.StartDate.name], this.date) > 0;
            item.spanRight = Ext.ensible.Date.diffDays(this.date, item[M.EndDate.name]) > 0;
            item.spanCls = item.spanLeft
                ? item.spanRight
                    ? 'ext-cal-ev-spanboth'
                    : 'ext-cal-ev-spanleft'
                : item.spanRight
                    ? 'ext-cal-ev-spanright'
                    : '';

            templateData.push({markup: eventTpl.apply(this.getTemplateEventData(item))});
        }, this);

        this.tpl.overwrite(this.el, templateData);
        this.fireEvent('eventsrendered', this, this.date, evts.getCount());
    },

    getTemplateEventData: function (evt) {
        var data = this.view.getTemplateEventData(evt);
        data._elId = 'dtl-' + data._elId;
        return data;
    },
});

Ext.reg('extensible.monthdaydetailview', Ext.ensible.cal.MonthDayDetailView);
