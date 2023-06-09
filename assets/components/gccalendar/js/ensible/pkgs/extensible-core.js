/*
 * Extensible 1.0.2
 * Copyright(c) 2010-2012 Extensible, LLC
 * licensing@ext.ensible.com
 * http://ext.ensible.com
 */
(function () {
    Ext.ns('Ext.ensible.ux', 'Ext.ensible.sample', 'Ext.ensible.plugins', 'Ext.ensible.cal');
    Ext.onReady(function () {
        if (Ext.getScrollBarWidth() < 3) {
            Ext.getBody().addClass('x-no-scrollbar');
        }
        if (Ext.isWindows) {
            Ext.getBody().addClass('x-win');
        }
    });
    Ext.apply(Ext.ensible, {
        version: '1.0.2',
        versionDetails: {major: 1, minor: 0, patch: 2},
        extVersion: '3.2.0',
        hasBorderRadius: !(Ext.isIE || Ext.isOpera),
        log: function (a) {
        },
        Date: {
            use24HourTime: false,
            diff: function (e, a, c) {
                var b = 1,
                    d = a.getTime() - e.getTime();
                if (c == 's') {
                    b = 1000;
                } else {
                    if (c == 'm') {
                        b = 1000 * 60;
                    } else {
                        if (c == 'h') {
                            b = 1000 * 60 * 60;
                        }
                    }
                }
                return Math.round(d / b);
            },
            diffDays: function (d, a) {
                var b = 1000 * 60 * 60 * 24,
                    c = a.clearTime(true).getTime() - d.clearTime(true).getTime();
                return Math.ceil(c / b);
            },
            copyTime: function (c, b) {
                var a = b.clone();
                a.setHours(c.getHours(), c.getMinutes(), c.getSeconds(), c.getMilliseconds());
                return a;
            },
            compare: function (c, b, a) {
                var e = c,
                    d = b;
                if (a !== true) {
                    e = c.clone();
                    e.setMilliseconds(0);
                    d = b.clone();
                    d.setMilliseconds(0);
                }
                return d.getTime() - e.getTime();
            },
            maxOrMin: function (a) {
                var e = a ? 0 : Number.MAX_VALUE,
                    c = 0,
                    b = arguments[1],
                    d = b.length;
                for (; c < d; c++) {
                    e = Math[a ? 'max' : 'min'](e, b[c].getTime());
                }
                return new Date(e);
            },
            max: function () {
                return this.maxOrMin.apply(this, [true, arguments]);
            },
            min: function () {
                return this.maxOrMin.apply(this, [false, arguments]);
            },
            isInRange: function (a, c, b) {
                return a >= c && a <= b;
            },
            rangesOverlap: function (f, b, e, a) {
                var c = f >= e && f <= a,
                    d = b >= e && b <= a,
                    g = f <= e && b >= a;
                return c || d || g;
            },
            isWeekend: function (a) {
                return a.getDay() % 6 === 0;
            },
            isWeekday: function (a) {
                return a.getDay() % 6 !== 0;
            },
        },
    });
})();
if (Ext.XTemplate) {
    Ext.override(Ext.XTemplate, {
        applySubTemplate: function (a, h, g, d, c) {
            var f = this,
                e,
                j = f.tpls[a],
                i,
                b = [];
            if ((j.test && !j.test.call(f, h, g, d, c)) || (j.exec && j.exec.call(f, h, g, d, c))) {
                return '';
            }
            i = j.target ? j.target.call(f, h, g) : h;
            e = i.length;
            g = j.target ? h : g;
            if (j.target && Ext.isArray(i)) {
                Ext.each(i, function (k, l) {
                    b[b.length] = j.compiled.call(f, k, g, l + 1, e);
                });
                return b.join('');
            }
            return j.compiled.call(f, i, g, d, c);
        },
    });
}
if (Ext.form.DateField) {
    Ext.override(Ext.form.DateField, {
        altFormats:
            'm/d/Y|n/j/Y|n/j/y|m/j/y|n/d/y|m/j/Y|n/d/Y|m-d-y|m-d-Y|m/d|m-d|md|mdy|mdY|d|Y-m-d|n-j|n/j',
        safeParse: function (b, c) {
            if (/[gGhH]/.test(c.replace(/(\\.)/g, ''))) {
                return Date.parseDate(b, c);
            } else {
                var a = Date.parseDate(b + ' ' + this.initTime, c + ' ' + this.initTimeFormat);
                if (a) {
                    return a.clearTime();
                }
            }
        },
    });
}
if (Ext.data.Store) {
    Ext.override(Ext.data.Store, {
        add: function (b) {
            var d, a, c;
            b = [].concat(b);
            if (b.length < 1) {
                return;
            }
            for (d = 0, len = b.length; d < len; d++) {
                a = b[d];
                a.join(this);
                if ((a.dirty || a.phantom) && this.modified.indexOf(a) == -1) {
                    this.modified.push(a);
                }
            }
            c = this.data.length;
            this.data.addAll(b);
            if (this.snapshot) {
                this.snapshot.addAll(b);
            }
            this.fireEvent('add', this, b, c);
        },
        insert: function (c, b) {
            var d, a;
            b = [].concat(b);
            for (d = 0, len = b.length; d < len; d++) {
                a = b[d];
                this.data.insert(c + d, a);
                a.join(this);
                if ((a.dirty || a.phantom) && this.modified.indexOf(a) == -1) {
                    this.modified.push(a);
                }
            }
            if (this.snapshot) {
                this.snapshot.addAll(b);
            }
            this.fireEvent('add', this, b, c);
        },
        createRecords: function (c, b, e) {
            var d = this.modified,
                g = b.length,
                a,
                f;
            for (f = 0; f < g; f++) {
                a = b[f];
                if (a.phantom && a.isValid()) {
                    a.markDirty();
                    if (d.indexOf(a) == -1) {
                        d.push(a);
                    }
                }
            }
            if (this.autoSave === true) {
                this.save();
            }
        },
    });
}
if (Ext.data.MemoryProxy) {
    Ext.data.MemoryProxy = function (b) {
        var a = {};
        a[Ext.data.Api.actions.read] = true;
        a[Ext.data.Api.actions.create] = true;
        a[Ext.data.Api.actions.update] = true;
        a[Ext.data.Api.actions.destroy] = true;
        Ext.data.MemoryProxy.superclass.constructor.call(this, {api: a});
        this.data = b;
    };
    Ext.extend(Ext.data.MemoryProxy, Ext.data.DataProxy, {
        doRequest: function (e, c, f, b, g, d, a) {
            g.call(d, null, a, true);
        },
    });
}
if (Ext.DomHelper) {
    Ext.apply(
        Ext.DomHelper,
        (function () {
            var v = null,
                j = /^(?:br|frame|hr|img|input|link|meta|range|spacer|wbr|area|param|col)$/i,
                l = /^table|tbody|tr|td$/i,
                d = /tag|children|cn|html$/i,
                r = /td|tr|tbody/i,
                n = /([a-z0-9-]+)\s*:\s*([^;\s]+(?:\s*[^;\s]+)*);?/gi,
                t = /end/i,
                q,
                m = 'afterbegin',
                o = 'afterend',
                c = 'beforebegin',
                p = 'beforeend',
                a = '<table>',
                h = '</table>',
                b = a + '<tbody>',
                i = '</tbody>' + h,
                k = b + '<tr>',
                u = '</tr>' + i;

            function g(z, B, A, C, y, w) {
                var x = q.insertHtml(C, Ext.getDom(z), s(B));
                return A ? Ext.get(x, true) : x;
            }

            function s(B) {
                var x = '',
                    w,
                    A,
                    z,
                    C;
                if (typeof B == 'string') {
                    x = B;
                } else {
                    if (Ext.isArray(B)) {
                        for (var y = 0; y < B.length; y++) {
                            if (B[y]) {
                                x += s(B[y]);
                            }
                        }
                    } else {
                        x += '<' + (B.tag = B.tag || 'div');
                        for (w in B) {
                            A = B[w];
                            if (!d.test(w)) {
                                if (typeof A == 'object') {
                                    x += ' ' + w + '="';
                                    for (z in A) {
                                        x += z + ':' + A[z] + ';';
                                    }
                                    x += '"';
                                } else {
                                    x += ' ' + ({cls: 'class', htmlFor: 'for'}[w] || w) + '="' + A + '"';
                                }
                            }
                        }
                        if (j.test(B.tag)) {
                            x += '/>';
                        } else {
                            x += '>';
                            if ((C = B.children || B.cn)) {
                                x += s(C);
                            } else {
                                if (B.html) {
                                    x += B.html;
                                }
                            }
                            x += '</' + B.tag + '>';
                        }
                    }
                }
                return x;
            }

            function f(D, A, z, B) {
                v.innerHTML = [A, z, B].join('');
                var w = -1,
                    y = v,
                    x;
                while (++w < D) {
                    y = y.firstChild;
                }
                if ((x = y.nextSibling)) {
                    var C = document.createDocumentFragment();
                    while (y) {
                        x = y.nextSibling;
                        C.appendChild(y);
                        y = x;
                    }
                    y = C;
                }
                return y;
            }

            function e(w, x, z, y) {
                var A, B;
                v = v || document.createElement('div');
                if ((w == 'td' && (x == m || x == p)) || (!r.test(w) && (x == c || x == o))) {
                    return;
                }
                B = x == c ? z : x == o ? z.nextSibling : x == m ? z.firstChild : null;
                if (x == c || x == o) {
                    z = z.parentNode;
                }
                if (w == 'td' || (w == 'tr' && (x == p || x == m))) {
                    A = f(4, k, y, u);
                } else {
                    if ((w == 'tbody' && (x == p || x == m)) || (w == 'tr' && (x == c || x == o))) {
                        A = f(3, b, y, i);
                    } else {
                        A = f(2, a, y, h);
                    }
                }
                z.insertBefore(A, B);
                return A;
            }

            q = {
                markup: function (w) {
                    return s(w);
                },
                applyStyles: function (w, x) {
                    if (x) {
                        var y;
                        w = Ext.fly(w);
                        if (typeof x == 'function') {
                            x = x.call();
                        }
                        if (typeof x == 'string') {
                            n.lastIndex = 0;
                            while ((y = n.exec(x))) {
                                w.setStyle(y[1], y[2]);
                            }
                        } else {
                            if (typeof x == 'object') {
                                w.setStyle(x);
                            }
                        }
                    }
                },
                insertHtml: function (B, w, C) {
                    var A = {},
                        y,
                        E,
                        D,
                        G,
                        z,
                        x,
                        F;
                    B = B.toLowerCase();
                    A[c] = ['BeforeBegin', 'previousSibling'];
                    A[o] = ['AfterEnd', 'nextSibling'];
                    if (w.insertAdjacentHTML) {
                        if (l.test(w.tagName) && (x = e(w.tagName.toLowerCase(), B, w, C))) {
                            return x;
                        }
                        A[m] = ['AfterBegin', 'firstChild'];
                        A[p] = ['BeforeEnd', 'lastChild'];
                        if ((y = A[B])) {
                            w.insertAdjacentHTML(y[0], C);
                            return w[y[1]];
                        }
                    } else {
                        D = w.ownerDocument.createRange();
                        E = 'setStart' + (t.test(B) ? 'After' : 'Before');
                        if (A[B]) {
                            D[E](w);
                            if (D.createContextualFragment) {
                                G = D.createContextualFragment(C);
                            } else {
                                (G = document.createDocumentFragment()), (F = document.createElement('div'));
                                G.appendChild(F);
                                F.outerHTML = C;
                            }
                            w.parentNode.insertBefore(G, B == c ? w : w.nextSibling);
                            return w[(B == c ? 'previous' : 'next') + 'Sibling'];
                        } else {
                            z = (B == m ? 'first' : 'last') + 'Child';
                            if (w.firstChild) {
                                D[E](w[z]);
                                G = D.createContextualFragment(C);
                                if (B == m) {
                                    w.insertBefore(G, w.firstChild);
                                } else {
                                    w.appendChild(G);
                                }
                            } else {
                                w.innerHTML = C;
                            }
                            return w[z];
                        }
                    }
                    throw 'Illegal insertion point -> "' + B + '"';
                },
                insertBefore: function (w, y, x) {
                    return g(w, y, x, c);
                },
                insertAfter: function (w, y, x) {
                    return g(w, y, x, o, 'nextSibling');
                },
                insertFirst: function (w, y, x) {
                    return g(w, y, x, m, 'firstChild');
                },
                append: function (w, y, x) {
                    return g(w, y, x, p, '', true);
                },
                overwrite: function (w, y, x) {
                    w = Ext.getDom(w);
                    w.innerHTML = s(y);
                    return x ? Ext.get(w.firstChild) : w.firstChild;
                },
                createHtml: s,
            };
            return q;
        })(),
    );
}
