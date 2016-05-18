/*!
 * FullCalendar v1.6.4
 * Docs & License: http://arshaw.com/fullcalendar/
 * (c) 2013 Adam Shaw
 */
(function(au, w) {
    var N = {
        defaultView: "month",
        aspectRatio: 1.35,
        header: {
            left: "title",
            center: "",
            right: "today prev,next"
        },
        weekends: true,
        weekNumbers: false,
        weekNumberCalculation: "iso",
        weekNumberTitle: "W",
        allDayDefault: true,
        ignoreTimezone: true,
        lazyFetching: true,
        startParam: "start",
        endParam: "end",
        titleFormat: {
            month: "MMMM yyyy",
            week: "MMM d[ yyyy]{ '&#8212;'[ MMM] d yyyy}",
            day: "dddd, MMM d, yyyy"
        },
        columnFormat: {
            month: "ddd",
            week: "ddd M/d",
            day: "dddd M/d"
        },
        timeFormat: {
            "": "h(:mm)t"
        },
        isRTL: false,
        firstDay: 0,
        monthNames: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
        monthNamesShort: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"],
        dayNames: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
        dayNamesShort: ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"],
        buttonText: {
            prev: "<span class='fc-text-arrow'>&lsaquo;</span>",
            next: "<span class='fc-text-arrow'>&rsaquo;</span>",
            prevYear: "<span class='fc-text-arrow'>&laquo;</span>",
            nextYear: "<span class='fc-text-arrow'>&raquo;</span>",
            today: "today",
            month: "month",
            week: "week",
            day: "day"
        },
        theme: false,
        buttonIcons: {
            prev: "circle-triangle-w",
            next: "circle-triangle-e"
        },
        unselectAuto: true,
        dropAccept: "*",
        handleWindowResize: true
    };
    var S = {
        header: {
            left: "next,prev today",
            center: "",
            right: "title"
        },
        buttonText: {
            prev: "<span class='fc-text-arrow'>&rsaquo;</span>",
            next: "<span class='fc-text-arrow'>&lsaquo;</span>",
            prevYear: "<span class='fc-text-arrow'>&raquo;</span>",
            nextYear: "<span class='fc-text-arrow'>&laquo;</span>"
        },
        buttonIcons: {
            prev: "circle-triangle-e",
            next: "circle-triangle-w"
        }
    };
    var aC = au.fullCalendar = {
        version: "1.6.4"
    };
    var af = aC.views = {};
    au.fn.fullCalendar = function(aO) {
        if (typeof aO == "string") {
            var aN = Array.prototype.slice.call(arguments, 1);
            var aP;
            this.each(function() {
                var aR = au.data(this, "fullCalendar");
                if (aR && au.isFunction(aR[aO])) {
                    var aQ = aR[aO].apply(aR, aN);
                    if (aP === w) {
                        aP = aQ;
                    }
                    if (aO == "destroy") {
                        au.removeData(this, "fullCalendar");
                    }
                }
            });
            if (aP !== w) {
                return aP;
            }
            return this;
        }
        aO = aO || {};
        var aM = aO.eventSources || [];
        delete aO.eventSources;
        if (aO.events) {
            aM.push(aO.events);
            delete aO.events;
        }
        aO = au.extend(true, {},
        N, (aO.isRTL || aO.isRTL === w && N.isRTL) ? S: {},
        aO);
        this.each(function(aS, aQ) {
            var aR = au(aQ);
            var aT = new u(aR, aO, aM);
            aR.data("fullCalendar", aT);
            aT.render();
        });
        return this;
    };
    function aj(aM) {
        au.extend(true, N, aM);
    }
    function u(a6, be, bh) {
        var bA = this;
        bA.options = be;
        bA.render = bp;
        bA.destroy = bC;
        bA.refetchEvents = a1;
        bA.reportEvents = a3;
        bA.reportEventChange = bE;
        bA.rerenderEvents = aP;
        bA.changeView = aX;
        bA.select = bB;
        bA.unselect = aY;
        bA.prev = bl;
        bA.next = aO;
        bA.prevYear = bD;
        bA.nextYear = aN;
        bA.today = a7;
        bA.gotoDate = bo;
        bA.incrementDate = bb;
        bA.formatDate = function(bK, bJ) {
            return D(bK, bJ, be);
        };
        bA.formatDates = function(bL, bK, bJ) {
            return l(bL, bK, bJ, be);
        };
        bA.getDate = bc;
        bA.getView = bi;
        bA.option = bx;
        bA.trigger = bn;
        bA.sidebar = ba;
        bA.updatesize = a9;
        t.call(bA, be, bh);
        var aM = bA.isFetchNeeded;
        var bH = bA.fetchEvents;
        var by = a6[0];
        var aR;
        var bq;
        var a0;
        var bI;
        var aZ;
        var bm;
        var aU;
        var br = 0;
        var bz = 0;
        var bF = new Date();
        var bg = [];
        var aS;
        A(bF, be.year, be.month, be.date);
        function bp(bJ) {
            if (!a0) {
                bf();
            } else {
                if (bv()) {
                    bs();
                    bG(bJ);
                }
            }
        }
        function bf() {
            bI = be.theme ? "ui": "fc";
            a6.addClass("fc");
            if (be.isRTL) {
                a6.addClass("fc-rtl");
            } else {
                a6.addClass("fc-ltr");
            }
            if (be.theme) {
                a6.addClass("ui-widget");
            }
            a0 = au("<div class='fc-content' style='position:relative'/>").prependTo(a6);
            aR = new T(bA, be);
            bq = aR.render();
            if (bq) {
                a6.prepend(bq);
            }
            aX(be.defaultView);
            if (be.handleWindowResize) {
                au(window).resize(a2);
            }
            if (!a5()) {
                aW();
            }
        }
        function aW() {
            setTimeout(function() {
                if (!aZ.start && a5()) {
                    bd();
                }
            },
            0);
        }
        function bC() {
            if (aZ) {
                bn("viewDestroy", aZ, aZ, aZ.element);
                aZ.triggerEventDestroy();
            }
            au(window).unbind("resize", a2);
            aR.destroy();
            a0.remove();
            a6.removeClass("fc fc-rtl ui-widget");
        }
        function bv() {
            return a6.is(":visible");
        }
        function a5() {
            return au("body").is(":visible");
        }
        function aX(bJ) {
            if (!aZ || bJ != aZ.name) {
                bt(bJ);
            }
        }
        function bt(bJ) {
            bz++;
            if (aZ) {
                bn("viewDestroy", aZ, aZ, aZ.element);
                aY();
                aZ.triggerEventDestroy();
                aQ();
                aZ.element.remove();
                aR.deactivateButton(aZ.name);
            }
            aR.activateButton(bJ);
            aZ = new af[bJ](au("<div class='fc-view fc-view-" + bJ + "' style='position:relative'/>").appendTo(a0), bA);
            bd();
            a4();
            bz--;
        }
        function bd(bJ) {
            if (!aZ.start || bJ || bF < aZ.start || bF >= aZ.end) {
                if (bv()) {
                    bG(bJ);
                }
            }
        }
        function bG(bJ) {
            bz++;
            if (aZ.start) {
                bn("viewDestroy", aZ, aZ, aZ.element);
                aY();
                bj();
            }
            aQ();
            aZ.render(bF, bJ || 0);
            bw();
            a4(); (aZ.afterRender || ar)();
            a8();
            bk();
            bn("viewRender", aZ, aZ, aZ.element);
            aZ.trigger("viewDisplay", by);
            bz--;
            aV();
        }
        function a9() {
            if (bv()) {
                aY();
                bj();
                bs();
                bw();
                aT();
            }
        }
        function bs() {
            if (be.contentHeight) {
                aU = be.contentHeight;
            } else {
                if (be.height) {
                    aU = be.height - (bq ? bq.height() : 0) - H(a0);
                } else {
                    aU = Math.round(a0.width() / Math.max(be.aspectRatio, 0.5));
                }
            }
        }
        function bw() {
            if (aU === w) {
                bs();
            }
            bz++;
            aZ.setHeight(aU);
            aZ.setWidth(a0.width());
            bz--;
            bm = a6.outerWidth();
        }
        function a2() {
            if (!bz) {
                if (aZ.start) {
                    var bJ = ++br;
                    setTimeout(function() {
                        if (bJ == br && !bz && bv()) {
                            if (bm != (bm = a6.outerWidth())) {
                                bz++;
                                a9();
                                aZ.trigger("windowResize", by);
                                bz--;
                            }
                        }
                    },
                    200);
                } else {
                    aW();
                }
            }
        }
        function a1() {
            bj();
            bu();
        }
        function aP(bJ) {
            bj();
            aT(bJ);
        }
        function aT(bJ) {
            if (bv()) {
                aZ.setEventData(bg);
                aZ.renderEvents(bg, bJ);
                aZ.trigger("eventAfterAllRender");
            }
        }
        function bj() {
            aZ.triggerEventDestroy();
            aZ.clearEvents();
            aZ.clearEventData();
        }
        function aV() {
            if (!be.lazyFetching || aM(aZ.visStart, aZ.visEnd)) {
                bu();
            } else {
                aT();
            }
        }
        function bu() {
            bH(aZ.visStart, aZ.visEnd);
        }
        function a3(bJ) {
            bg = bJ;
            aT();
        }
        function bE(bJ) {
            aP(bJ);
        }
        function a8() {
            aR.updateTitle(aZ.title);
        }
        function bk() {
            var bJ = new Date();
            if (bJ >= aZ.start && bJ < aZ.end) {
                aR.disableButton("today");
            } else {
                aR.enableButton("today");
            }
        }
        function bB(bL, bJ, bK) {
            aZ.select(bL, bJ, bK === w ? true: bK);
        }
        function aY() {
            if (aZ) {
                aZ.unselect();
            }
        }
        function bl() {
            bd( - 1);
        }
        function aO() {
            bd(1);
        }
        function bD() {
            ai(bF, -1);
            bd();
        }
        function aN() {
            ai(bF, 1);
            bd();
        }
        function a7() {
            bF = new Date();
            bd();
        }
        function ba() {}
        function bo(bK, bL, bJ) {
            if (bK instanceof Date) {
                bF = O(bK);
            } else {
                A(bF, bK, bL, bJ);
            }
            bd();
        }
        function bb(bK, bJ, bL) {
            if (bK !== w) {
                ai(bF, bK);
            }
            if (bJ !== w) {
                q(bF, bJ);
            }
            if (bL !== w) {
                aE(bF, bL);
            }
            bd();
        }
        function bc() {
            return O(bF);
        }
        function aQ() {
            a0.css({
                width: "100%",
                height: a0.height(),
                overflow: "hidden"
            });
        }
        function a4() {
            a0.css({
                width: "",
                height: "",
                overflow: ""
            });
        }
        function bi() {
            return aZ;
        }
        function bx(bJ, bK) {
            if (bK === w) {
                return be[bJ];
            }
            if (bJ == "height" || bJ == "contentHeight" || bJ == "aspectRatio") {
                be[bJ] = bK;
                a9();
            }
        }
        function bn(bJ, bK) {
            if (be[bJ]) {
                return be[bJ].apply(bK || by, Array.prototype.slice.call(arguments, 2));
            }
        }
        if (be.droppable) {
            au(document).bind("dragstart",
            function(bL, bM) {
                var bJ = bL.target;
                var bN = au(bJ);
                if (!bN.parents(".fc").length) {
                    var bK = be.dropAccept;
                    if (au.isFunction(bK) ? bK.call(bJ, bN) : bN.is(bK)) {
                        aS = bJ;
                        aZ.dragStart(aS, bL, bM);
                    }
                }
            }).bind("dragstop",
            function(bJ, bK) {
                if (aS) {
                    aZ.dragStop(aS, bJ, bK);
                    aS = null;
                }
            });
        }
    }
    function T(aP, aY) {
        var aX = this;
        aX.render = aN;
        aX.destroy = aT;
        aX.updateTitle = aR;
        aX.activateButton = aM;
        aX.deactivateButton = aV;
        aX.disableButton = aO;
        aX.enableButton = aS;
        var aQ = au([]);
        var aU;
        function aN() {
            aU = aY.theme ? "ui": "fc";
            var aZ = aY.header;
            if (aZ) {
                aQ = au("<table class='fc-header' style='width:100%'/>").append(au("<tr/>").append(aW("left")).append(aW("center")).append(aW("right")));
                return aQ;
            }
        }
        function aT() {
            aQ.remove();
        }
        function aW(aZ) {
            var a1 = au("<td class='fc-header-" + aZ + "'/>");
            var a0 = aY.header[aZ];
            if (a0) {
                au.each(a0.split(" "),
                function(a3) {
                    if (a3 > 0) {
                        a1.append("<span class='fc-header-space'/>");
                    }
                    var a2;
                    au.each(this.split(","),
                    function(a6, a5) {
                        if (a5 == "title") {
                            a1.append("<span class='fc-header-title'><h2>&nbsp;</h2></span>");
                            if (a2) {
                                a2.addClass(aU + "-corner-right");
                            }
                            a2 = null;
                        } else {
                            var a4;
                            if (aP[a5]) {
                                a4 = aP[a5];
                            } else {
                                if (af[a5]) {
                                    a4 = function() {
                                        a7.removeClass(aU + "-state-hover");
                                        aP.changeView(a5);
                                    };
                                }
                            }
                            if (a4) {
                                var a8 = aY.theme ? G(aY.buttonIcons, a5) : null;
                                var a9 = G(aY.buttonText, a5);
                                var a7 = null;
                                if (a5 == "today") {
                                    a7 = au("<span class='fc-button fc-button-" + a5 + " " + aU + "-state-default'>" + (a8 ? "<span class='fc-icon-wrap'>" + "<span class='ui-icon ui-icon-" + a8 + "'/>" + "</span>": a9) + "</span>").click(function() {
                                        if (!a7.hasClass(aU + "-state-disabled")) {
                                            a4();
                                        }
                                    }).mousedown(function() {
                                        a7.not("." + aU + "-state-active").not("." + aU + "-state-disabled").addClass(aU + "-state-down");
                                    }).mouseup(function() {
                                        a7.removeClass(aU + "-state-down");
                                    }).hover(function() {
                                        a7.not("." + aU + "-state-active").not("." + aU + "-state-disabled").addClass(aU + "-state-hover");
                                    },
                                    function() {
                                        a7.removeClass(aU + "-state-hover").removeClass(aU + "-state-down");
                                    }).appendTo(a1);
                                } else {
                                    a7 = au("<span class='fc-button fc-button-" + a5 + " " + aU + "-state-default'>" + (a8 ? "<span class='fc-icon-wrap'>" + "<span class='ui-icon ui-icon-" + a8 + "'/>" + "</span>": a9) + "</span>").click(function() {
                                        if (!a7.hasClass(aU + "-state-disabled")) {
                                            a4();
                                        }
                                    }).mousedown(function() {
                                        a7.not("." + aU + "-state-active").not("." + aU + "-state-disabled").addClass(aU + "-state-down");
                                    }).mouseup(function() {
                                        a7.removeClass(aU + "-state-down");
                                    }).hover(function() {
                                        a7.not("." + aU + "-state-active").not("." + aU + "-state-disabled").addClass(aU + "-state-hover");
                                    },
                                    function() {
                                        a7.removeClass(aU + "-state-hover").removeClass(aU + "-state-down");
                                    }).appendTo(a1);
                                }
                                aL(a7);
                                if (!a2) {
                                    a7.addClass(aU + "-corner-left");
                                }
                                a2 = a7;
                            }
                        }
                    });
                    if (a2) {
                        a2.addClass(aU + "-corner-right");
                    }
                });
            }
            return a1;
        }
        function aR(aZ) {
            aQ.find("h2").html(aZ);
        }
        function aM(aZ) {
            aQ.find("span.fc-button-" + aZ).addClass(aU + "-state-active");
        }
        function aV(aZ) {
            aQ.find("span.fc-button-" + aZ).removeClass(aU + "-state-active");
        }
        function aO(aZ) {
            aQ.find("span.fc-button-" + aZ).addClass(aU + "-state-disabled");
        }
        function aS(aZ) {
            aQ.find("span.fc-button-" + aZ).removeClass(aU + "-state-disabled");
        }
    }
    aC.sourceNormalizers = [];
    aC.sourceFetchers = [];
    var o = {
        dataType: "json",
        cache: false
    };
    var W = 1;
    function t(aS, a0) {
        var a2 = this;
        a2.isFetchNeeded = aU;
        a2.fetchEvents = a1;
        a2.addEventSource = aP;
        a2.removeEventSource = aQ;
        a2.updateEvent = a5;
        a2.renderEvent = aY;
        a2.removeEvents = bh;
        a2.clientEvents = bb;
        a2.normalizeEvent = aX;
        var a4 = a2.trigger;
        var aM = a2.getView;
        var a7 = a2.reportEvents;
        var a9 = {
            events: []
        };
        var aN = [a9];
        var be, ba;
        var aO = 0;
        var bf = 0;
        var bd = 0;
        var a3 = [];
        for (var bc = 0; bc < a0.length; bc++) {
            a6(a0[bc]);
        }
        function aU(bj, bi) {
            return ! be || bj < be || bi > ba;
        }
        function a1(bm, bj) {
            be = bm;
            ba = bj;
            a3 = [];
            var bl = ++aO;
            var bi = aN.length;
            bf = bi;
            for (var bk = 0; bk < bi; bk++) {
                aT(aN[bk], bl);
            }
        }
        function aT(bj, bi) {
            a8(bj,
            function(bl) {
                if (bi == aO) {
                    if (bl) {
                        if (aS.eventDataTransform) {
                            bl = au.map(bl, aS.eventDataTransform);
                        }
                        if (bj.eventDataTransform) {
                            bl = au.map(bl, bj.eventDataTransform);
                        }
                        for (var bk = 0; bk < bl.length; bk++) {
                            bl[bk].source = bj;
                            aX(bl[bk]);
                        }
                        a3 = a3.concat(bl);
                    }
                    bf--;
                    if (!bf) {
                        a7(a3);
                    }
                }
            });
        }
        function a8(bi, bs) {
            var bn;
            var br = aC.sourceFetchers;
            var bp;
            for (bn = 0; bn < br.length; bn++) {
                bp = br[bn](bi, be, ba, bs);
                if (bp === true) {
                    return;
                } else {
                    if (typeof bp == "object") {
                        a8(bp, bs);
                        return;
                    }
                }
            }
            var bu = bi.events;
            if (bu) {
                if (au.isFunction(bu)) {
                    aW();
                    bu(O(be), O(ba),
                    function(bw) {
                        bs(bw);
                        bg();
                    });
                } else {
                    if (au.isArray(bu)) {
                        bs(bu);
                    } else {
                        bs();
                    }
                }
            } else {
                var bj = bi.url;
                if (bj) {
                    var bt = bi.success;
                    var bq = bi.error;
                    var bk = bi.complete;
                    var bv;
                    if (au.isFunction(bi.data)) {
                        bv = bi.data();
                    } else {
                        bv = bi.data;
                    }
                    var bm = au.extend({},
                    bv || {});
                    var bo = aJ(bi.startParam, aS.startParam);
                    var bl = aJ(bi.endParam, aS.endParam);
                    if (bo) {
                        bm[bo] = Math.round( + be / 1000);
                    }
                    if (bl) {
                        bm[bl] = Math.round( + ba / 1000);
                    }
                    aW();
                    au.ajax(au.extend({},
                    o, bi, {
                        data: bm,
                        success: function(bx) {
                            bx = bx || [];
                            var bw = J(bt, this, arguments);
                            if (au.isArray(bw)) {
                                bx = bw;
                            }
                            bs(bx);
                        },
                        error: function() {
                            J(bq, this, arguments);
                            bs();
                        },
                        complete: function() {
                            J(bk, this, arguments);
                            bg();
                        }
                    }));
                } else {
                    bs();
                }
            }
        }
        function aP(bi) {
            bi = a6(bi);
            if (bi) {
                bf++;
                aT(bi, aO);
            }
        }
        function a6(bi) {
            if (au.isFunction(bi) || au.isArray(bi)) {
                bi = {
                    events: bi
                };
            } else {
                if (typeof bi == "string") {
                    bi = {
                        url: bi
                    };
                }
            }
            if (typeof bi == "object") {
                aZ(bi);
                aN.push(bi);
                return bi;
            }
        }
        function aQ(bi) {
            aN = au.grep(aN,
            function(bj) {
                return ! aV(bj, bi);
            });
            a3 = au.grep(a3,
            function(bj) {
                return ! aV(bj.source, bi);
            });
            a7(a3);
        }
        function a5(bn) {
            var bl, bj = a3.length,
            bo, bi = aM().defaultEventEnd,
            bm = bn.start - bn._start,
            bk = bn.end ? (bn.end - (bn._end || bi(bn))) : 0;
            for (bl = 0; bl < bj; bl++) {
                bo = a3[bl];
                if (bo._id == bn._id && bo != bn) {
                    bo.start = new Date( + bo.start + bm);
                    if (bn.end) {
                        if (bo.end) {
                            bo.end = new Date( + bo.end + bk);
                        } else {
                            bo.end = new Date( + bi(bo) + bk);
                        }
                    } else {
                        bo.end = null;
                    }
                    bo.title = bn.title;
                    bo.url = bn.url;
                    bo.allDay = bn.allDay;
                    bo.className = bn.className;
                    bo.receive = bn.receive;
                    bo.editable = bn.editable;
                    bo.color = bn.color;
                    bo.backgroundColor = bn.backgroundColor;
                    bo.borderColor = bn.borderColor;
                    bo.textColor = bn.textColor;
                    aX(bo);
                }
            }
            aX(bn);
            a7(a3);
        }
        function aY(bj, bi) {
            aX(bj);
            if (!bj.source) {
                if (bi) {
                    a9.events.push(bj);
                    bj.source = a9;
                }
                a3.push(bj);
            }
            a7(a3);
        }
        function bh(bj) {
            if (!bj) {
                a3 = [];
                for (var bi = 0; bi < aN.length; bi++) {
                    if (au.isArray(aN[bi].events)) {
                        aN[bi].events = [];
                    }
                }
            } else {
                if (!au.isFunction(bj)) {
                    var bk = bj + "";
                    bj = function(bl) {
                        return bl._id == bk;
                    };
                }
                a3 = au.grep(a3, bj, true);
                for (var bi = 0; bi < aN.length; bi++) {
                    if (au.isArray(aN[bi].events)) {
                        aN[bi].events = au.grep(aN[bi].events, bj, true);
                    }
                }
            }
            a7(a3);
        }
        function bb(bi) {
            if (au.isFunction(bi)) {
                return au.grep(a3, bi);
            } else {
                if (bi) {
                    bi += "";
                    return au.grep(a3,
                    function(bj) {
                        return bj._id == bi;
                    });
                }
            }
            return a3;
        }
        function aW() {
            if (!bd++) {
                a4("loading", null, true, aM());
            }
        }
        function bg() {
            if (!--bd) {
                a4("loading", null, false, aM());
            }
        }
        function aX(bj) {
            var bk = bj.source || {};
            var bi = aJ(bk.ignoreTimezone, aS.ignoreTimezone);
            bj._id = bj._id || (bj.id === w ? "_fc" + W++:bj.id + "");
            if (bj.date) {
                if (!bj.start) {
                    bj.start = bj.date;
                }
                delete bj.date;
            }
            bj._start = O(bj.start = aa(bj.start, bi));
            bj.end = aa(bj.end, bi);
            if (bj.end && bj.end < bj.start) {
                bj.end = null;
            }
            bj._end = bj.end ? O(bj.end) : null;
            if (bj.allDay === w) {
                bj.allDay = aJ(bk.allDayDefault, aS.allDayDefault);
            }
            if (bj.className) {
                if (typeof bj.className == "string") {
                    bj.className = bj.className.split(/\s+/);
                }
            } else {
                bj.className = [];
            }
        }
        function aZ(bk) {
            if (bk.className) {
                if (typeof bk.className == "string") {
                    bk.className = bk.className.split(/\s+/);
                }
            } else {
                bk.className = [];
            }
            var bj = aC.sourceNormalizers;
            for (var bi = 0; bi < bj.length; bi++) {
                bj[bi](bk);
            }
        }
        function aV(bj, bi) {
            return bj && bi && aR(bj) == aR(bi);
        }
        function aR(bi) {
            return ((typeof bi == "object") ? (bi.events || bi.url) : "") || bi;
        }
    }
    aC.addDays = aE;
    aC.cloneDate = O;
    aC.parseDate = aa;
    aC.parseISO8601 = p;
    aC.parseTime = aI;
    aC.formatDate = D;
    aC.formatDates = l;
    var Q = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"],
    ax = 86400000,
    al = 3600000,
    U = 60000;
    function ai(aN, aO, aM) {
        aN.setFullYear(aN.getFullYear() + aO);
        if (!aM) {
            e(aN);
        }
        return aN;
    }
    function q(aP, aQ, aO) {
        if ( + aP) {
            var aM = aP.getMonth() + aQ,
            aN = O(aP);
            aN.setDate(1);
            aN.setMonth(aM);
            aP.setMonth(aM);
            if (!aO) {
                e(aP);
            }
            while (aP.getMonth() != aN.getMonth()) {
                aP.setDate(aP.getDate() + (aP < aN ? 1 : -1));
            }
        }
        return aP;
    }
    function aE(aP, aQ, aO) {
        if ( + aP) {
            var aM = aP.getDate() + aQ,
            aN = O(aP);
            aN.setHours(9);
            aN.setDate(aM);
            aP.setDate(aM);
            if (!aO) {
                e(aP);
            }
            aG(aP, aN);
        }
        return aP;
    }
    function aG(aN, aM) {
        if ( + aN) {
            while (aN.getDate() != aM.getDate()) {
                aN.setTime( + aN + (aN < aM ? 1 : -1) * al);
            }
        }
    }
    function k(aM, aN) {
        aM.setMinutes(aM.getMinutes() + aN);
        return aM;
    }
    function e(aM) {
        aM.setHours(0);
        aM.setMinutes(0);
        aM.setSeconds(0);
        aM.setMilliseconds(0);
        return aM;
    }
    function O(aM, aN) {
        if (aN) {
            return e(new Date( + aM));
        }
        return new Date( + aM);
    }
    function h() {
        var aM = 0,
        aN;
        do {
            aN = new Date(1970, aM++, 1);
        } while ( aN . getHours ());
        return aN;
    }
    function aB(aN, aM) {
        return Math.round((O(aN, true) - O(aM, true)) / ax);
    }
    function A(aN, aP, aM, aO) {
        if (aP !== w && aP != aN.getFullYear()) {
            aN.setDate(1);
            aN.setMonth(0);
            aN.setFullYear(aP);
        }
        if (aM !== w && aM != aN.getMonth()) {
            aN.setDate(1);
            aN.setMonth(aM);
        }
        if (aO !== w) {
            aN.setDate(aO);
        }
    }
    function aa(aN, aM) {
        if (typeof aN == "object") {
            return aN;
        }
        if (typeof aN == "number") {
            return new Date(aN * 1000);
        }
        if (typeof aN == "string") {
            if (aN.match(/^\d+(\.\d+)?$/)) {
                return new Date(parseFloat(aN) * 1000);
            }
            if (aM === w) {
                aM = true;
            }
            return p(aN, aM) || (aN ? new Date(aN) : null);
        }
        return null;
    }
    function p(aQ, aN) {
        var aM = aQ.match(/^([0-9]{4})(-([0-9]{2})(-([0-9]{2})([T ]([0-9]{2}):([0-9]{2})(:([0-9]{2})(\.([0-9]+))?)?(Z|(([-+])([0-9]{2})(:?([0-9]{2}))?))?)?)?)?$/);
        if (!aM) {
            return null;
        }
        var aP = new Date(aM[1], 0, 1);
        if (aN || !aM[13]) {
            var aO = new Date(aM[1], 0, 1, 9, 0);
            if (aM[3]) {
                aP.setMonth(aM[3] - 1);
                aO.setMonth(aM[3] - 1);
            }
            if (aM[5]) {
                aP.setDate(aM[5]);
                aO.setDate(aM[5]);
            }
            aG(aP, aO);
            if (aM[7]) {
                aP.setHours(aM[7]);
            }
            if (aM[8]) {
                aP.setMinutes(aM[8]);
            }
            if (aM[10]) {
                aP.setSeconds(aM[10]);
            }
            if (aM[12]) {
                aP.setMilliseconds(Number("0." + aM[12]) * 1000);
            }
            aG(aP, aO);
        } else {
            aP.setUTCFullYear(aM[1], aM[3] ? aM[3] - 1 : 0, aM[5] || 1);
            aP.setUTCHours(aM[7] || 0, aM[8] || 0, aM[10] || 0, aM[12] ? Number("0." + aM[12]) * 1000 : 0);
            if (aM[14]) {
                var aR = Number(aM[16]) * 60 + (aM[18] ? Number(aM[18]) : 0);
                aR *= aM[15] == "-" ? 1 : -1;
                aP = new Date( + aP + (aR * 60 * 1000));
            }
        }
        return aP;
    }
    function aI(aO) {
        if (typeof aO == "number") {
            return aO * 60;
        }
        if (typeof aO == "object") {
            return aO.getHours() * 60 + aO.getMinutes();
        }
        var aM = aO.match(/(\d+)(?::(\d+))?\s*(\w+)?/);
        if (aM) {
            var aN = parseInt(aM[1], 10);
            if (aM[3]) {
                aN %= 12;
                if (aM[3].toLowerCase().charAt(0) == "p") {
                    aN += 12;
                }
            }
            return aN * 60 + (aM[2] ? parseInt(aM[2], 10) : 0);
        }
    }
    function D(aN, aO, aM) {
        return l(aN, null, aO, aM);
    }
    function l(aY, aX, aW, aZ) {
        aZ = aZ || N;
        var aN = aY,
        aP = aX,
        aQ, aR = aW.length,
        aT, aO, aV, aS = "";
        for (aQ = 0; aQ < aR; aQ++) {
            aT = aW.charAt(aQ);
            if (aT == "'") {
                for (aO = aQ + 1; aO < aR; aO++) {
                    if (aW.charAt(aO) == "'") {
                        if (aN) {
                            if (aO == aQ + 1) {
                                aS += "'";
                            } else {
                                aS += aW.substring(aQ + 1, aO);
                            }
                            aQ = aO;
                        }
                        break;
                    }
                }
            } else {
                if (aT == "(") {
                    for (aO = aQ + 1; aO < aR; aO++) {
                        if (aW.charAt(aO) == ")") {
                            var aM = D(aN, aW.substring(aQ + 1, aO), aZ);
                            if (parseInt(aM.replace(/\D/, ""), 10)) {
                                aS += aM;
                            }
                            aQ = aO;
                            break;
                        }
                    }
                } else {
                    if (aT == "[") {
                        for (aO = aQ + 1; aO < aR; aO++) {
                            if (aW.charAt(aO) == "]") {
                                var aU = aW.substring(aQ + 1, aO);
                                var aM = D(aN, aU, aZ);
                                if (aM != D(aP, aU, aZ)) {
                                    aS += aM;
                                }
                                aQ = aO;
                                break;
                            }
                        }
                    } else {
                        if (aT == "{") {
                            aN = aX;
                            aP = aY;
                        } else {
                            if (aT == "}") {
                                aN = aY;
                                aP = aX;
                            } else {
                                for (aO = aR; aO > aQ; aO--) {
                                    if (aV = aA[aW.substring(aQ, aO)]) {
                                        if (aN) {
                                            aS += aV(aN, aZ);
                                        }
                                        aQ = aO - 1;
                                        break;
                                    }
                                }
                                if (aO == aQ) {
                                    if (aN) {
                                        aS += aT;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        return aS;
    }
    var aA = {
        s: function(aM) {
            return aM.getSeconds();
        },
        ss: function(aM) {
            return ab(aM.getSeconds());
        },
        m: function(aM) {
            return aM.getMinutes();
        },
        mm: function(aM) {
            return ab(aM.getMinutes());
        },
        h: function(aM) {
            return aM.getHours() % 12 || 12;
        },
        hh: function(aM) {
            return ab(aM.getHours() % 12 || 12);
        },
        H: function(aM) {
            return aM.getHours();
        },
        HH: function(aM) {
            return ab(aM.getHours());
        },
        d: function(aM) {
            return aM.getDate();
        },
        dd: function(aM) {
            return ab(aM.getDate());
        },
        ddd: function(aN, aM) {
            return aM.dayNamesShort[aN.getDay()];
        },
        dddd: function(aN, aM) {
            return aM.dayNames[aN.getDay()];
        },
        M: function(aM) {
            return aM.getMonth() + 1;
        },
        MM: function(aM) {
            return ab(aM.getMonth() + 1);
        },
        MMM: function(aN, aM) {
            return aM.monthNamesShort[aN.getMonth()];
        },
        MMMM: function(aN, aM) {
            return aM.monthNames[aN.getMonth()];
        },
        yy: function(aM) {
            return (aM.getFullYear() + "").substring(2);
        },
        yyyy: function(aM) {
            return aM.getFullYear();
        },
        t: function(aM) {
            return aM.getHours() < 12 ? "a": "p";
        },
        tt: function(aM) {
            return aM.getHours() < 12 ? "am": "pm";
        },
        T: function(aM) {
            return aM.getHours() < 12 ? "A": "P";
        },
        TT: function(aM) {
            return aM.getHours() < 12 ? "AM": "PM";
        },
        u: function(aM) {
            return D(aM, "yyyy-MM-dd'T'HH:mm:ss'Z'");
        },
        S: function(aN) {
            var aM = aN.getDate();
            if (aM > 10 && aM < 20) {
                return "th";
            }
            return ["st", "nd", "rd"][aM % 10 - 1] || "th";
        },
        w: function(aN, aM) {
            return aM.weekNumberCalculation(aN);
        },
        W: function(aM) {
            return x(aM);
        }
    };
    aC.dateFormatters = aA;
    function x(aM) {
        var aN;
        var aO = new Date(aM.getTime());
        aO.setDate(aO.getDate() + 4 - (aO.getDay() || 7));
        aN = aO.getTime();
        aO.setMonth(0);
        aO.setDate(1);
        return Math.floor(Math.round((aN - aO) / 86400000) / 7) + 1;
    }
    aC.applyAll = J;
    function ao(aM) {
        if (aM.end) {
            return s(aM.end, aM.allDay);
        } else {
            return aE(O(aM.start), 1);
        }
    }
    function s(aM, aN) {
        aM = O(aM);
        return aN || aM.getHours() || aM.getMinutes() ? aE(aM, 1) : e(aM);
    }
    function v(aN, aM, aO) {
        aN.unbind("mouseover").mouseover(function(aS) {
            var aR = aS.target,
            aT, aQ, aP;
            while (aR != this) {
                aT = aR;
                aR = aR.parentNode;
            }
            if ((aQ = aT._fci) !== w) {
                aT._fci = w;
                aP = aM[aQ];
                aO(aP.event, aP.element, aP);
                au(aS.target).trigger(aS);
            }
            aS.stopPropagation();
        });
    }
    function av(aO, aP, aM) {
        for (var aN = 0,
        aQ; aN < aO.length; aN++) {
            aQ = au(aO[aN]);
            aQ.width(Math.max(0, aP - j(aQ, aM)));
        }
    }
    function Z(aP, aM, aN) {
        for (var aO = 0,
        aQ; aO < aP.length; aO++) {
            aQ = au(aP[aO]);
            aQ.height(Math.max(0, aM - H(aQ, aN)));
        }
    }
    function j(aN, aM) {
        return ah(aN) + r(aN) + (aM ? ag(aN) : 0);
    }
    function ah(aM) {
        return (parseFloat(au.css(aM[0], "paddingLeft", true)) || 0) + (parseFloat(au.css(aM[0], "paddingRight", true)) || 0);
    }
    function ag(aM) {
        return (parseFloat(au.css(aM[0], "marginLeft", true)) || 0) + (parseFloat(au.css(aM[0], "marginRight", true)) || 0);
    }
    function r(aM) {
        return (parseFloat(au.css(aM[0], "borderLeftWidth", true)) || 0) + (parseFloat(au.css(aM[0], "borderRightWidth", true)) || 0);
    }
    function H(aN, aM) {
        return z(aN) + aq(aN) + (aM ? m(aN) : 0);
    }
    function z(aM) {
        return (parseFloat(au.css(aM[0], "paddingTop", true)) || 0) + (parseFloat(au.css(aM[0], "paddingBottom", true)) || 0);
    }
    function m(aM) {
        return (parseFloat(au.css(aM[0], "marginTop", true)) || 0) + (parseFloat(au.css(aM[0], "marginBottom", true)) || 0);
    }
    function aq(aM) {
        return (parseFloat(au.css(aM[0], "borderTopWidth", true)) || 0) + (parseFloat(au.css(aM[0], "borderBottomWidth", true)) || 0);
    }
    function ar() {}
    function a(aN, aM) {
        return aN - aM;
    }
    function at(aM) {
        return Math.max.apply(Math, aM);
    }
    function ab(aM) {
        return (aM < 10 ? "0": "") + aM;
    }
    function G(aQ, aM) {
        if (aQ[aM] !== w) {
            return aQ[aM];
        }
        var aP = aM.split(/(?=[A-Z])/),
        aO = aP.length - 1,
        aN;
        for (; aO >= 0; aO--) {
            aN = aQ[aP[aO].toLowerCase()];
            if (aN !== w) {
                return aN;
            }
        }
        return aQ[""];
    }
    function aH(aM) {
        return aM.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/'/g, "&#039;").replace(/"/g, "&quot;").replace(/\n/g, "<br />");
    }
    function aL(aM) {
        aM.attr("unselectable", "on").css("MozUserSelect", "none").bind("selectstart.ui",
        function() {
            return false;
        });
    }
    function g(aM) {
        aM.children().removeClass("fc-first fc-last").filter(":first-child").addClass("fc-first").end().filter(":last-child").addClass("fc-last");
    }
    function L(aM, aN) {
        aM.each(function(aO, aP) {
            aP.className = aP.className.replace(/^fc-\w*/, "fc-" + Q[aN.getDay()]);
        });
    }
    function R(aN, aO) {
        var aM = aN.source || {};
        var aT = aN.color;
        var aR = aM.color;
        var aQ = aO("eventColor");
        var aU = aN.backgroundColor || aT || aM.backgroundColor || aR || aO("eventBackgroundColor") || aQ;
        var aP = aN.borderColor || aT || aM.borderColor || aR || aO("eventBorderColor") || aQ;
        var aV = aN.textColor || aM.textColor || aO("eventTextColor");
        var aS = [];
        if (aU) {
            aS.push("background-color:" + aU);
        }
        if (aP) {
            aS.push("border-color:" + aP);
        }
        if (aV) {
            aS.push("color:" + aV);
        }
        return aS.join(";");
    }
    function J(aP, aQ, aN) {
        if (au.isFunction(aP)) {
            aP = [aP];
        }
        if (aP) {
            var aO;
            var aM;
            for (aO = 0; aO < aP.length; aO++) {
                aM = aP[aO].apply(aQ, aN) || aM;
            }
            return aM;
        }
    }
    function aJ() {
        for (var aM = 0; aM < arguments.length; aM++) {
            if (arguments[aM] !== w) {
                return arguments[aM];
            }
        }
    }
    af.month = ad;
    function ad(aR, aQ) {
        var aU = this;
        aU.render = aM;
        B.call(aU, aR, aQ, "month");
        var aN = aU.opt;
        var aO = aU.renderBasic;
        var aS = aU.skipHiddenDays;
        var aP = aU.getCellsPerWeek;
        var aT = aQ.formatDate;
        function aM(aX, a2) {
            if (a2) {
                q(aX, a2);
                aX.setDate(1);
            }
            var aV = aN("firstDay");
            var aW = O(aX, true);
            aW.setDate(1);
            var aY = q(O(aW), 1);
            var a3 = O(aW);
            aE(a3, -((a3.getDay() - aV + 7) % 7));
            aS(a3);
            var aZ = O(aY);
            aE(aZ, (7 - aZ.getDay() + aV) % 7);
            aS(aZ, -1, true);
            var a1 = aP();
            var a0 = Math.round(aB(aZ, a3) / 7);
            if (aN("weekMode") == "fixed") {
                aE(aZ, (6 - a0) * 7);
                a0 = 6;
            }
            aU.title = aT(aW, aN("titleFormat"));
            aU.start = aW;
            aU.end = aY;
            aU.visStart = a3;
            aU.visEnd = aZ;
            aO(a0, a1, true);
        }
    }
    af.basicWeek = aD;
    function aD(aR, aQ) {
        var aU = this;
        aU.render = aM;
        B.call(aU, aR, aQ, "basicWeek");
        var aN = aU.opt;
        var aO = aU.renderBasic;
        var aT = aU.skipHiddenDays;
        var aP = aU.getCellsPerWeek;
        var aS = aQ.formatDates;
        function aM(aZ, a1) {
            if (a1) {
                aE(aZ, a1 * 7);
            }
            var a0 = aE(O(aZ), -((aZ.getDay() - aN("firstDay") + 7) % 7));
            var aX = aE(O(a0), 7);
            var aW = O(a0);
            aT(aW);
            var aV = O(aX);
            aT(aV, -1, true);
            var aY = aP();
            aU.start = a0;
            aU.end = aX;
            aU.visStart = aW;
            aU.visEnd = aV;
            aU.title = aS(aW, aE(O(aV), -1), aN("titleFormat"));
            aO(1, aY, false);
        }
    }
    af.basicDay = K;
    function K(aQ, aT) {
        var aP = this;
        aP.render = aR;
        B.call(aP, aQ, aT, "basicDay");
        var aO = aP.opt;
        var aM = aP.renderBasic;
        var aN = aP.skipHiddenDays;
        var aS = aT.formatDate;
        function aR(aV, aX) {
            if (aX) {
                aE(aV, aX);
            }
            aN(aV, aX < 0 ? -1 : 1);
            var aW = O(aV, true);
            var aU = aE(O(aW), 1);
            aP.title = aS(aV, aO("titleFormat"));
            aP.start = aP.visStart = aW;
            aP.end = aP.visEnd = aU;
            aM(1, 1, false);
        }
    }
    aj({
        weekMode: "fixed"
    });
    function B(a7, by, aZ) {
        var bx = this;
        bx.renderBasic = br;
        bx.setHeight = bv;
        bx.setWidth = bG;
        bx.renderDayOverlay = bn;
        bx.defaultSelectionEnd = bm;
        bx.renderSelection = bz;
        bx.clearSelection = aM;
        bx.reportDayClick = a5;
        bx.dragStart = aR;
        bx.dragStop = a0;
        bx.defaultEventEnd = bL;
        bx.getHoverListener = function() {
            return aS;
        };
        bx.colLeft = bN;
        bx.colRight = ba;
        bx.colContentLeft = bu;
        bx.colContentRight = bs;
        bx.getIsCellAllDay = function() {
            return true;
        };
        bx.allDayRow = a4;
        bx.getRowCnt = function() {
            return be;
        };
        bx.getColCnt = function() {
            return aU;
        };
        bx.getColWidth = function() {
            return bj;
        };
        bx.getDaySegmentContainer = function() {
            return a3;
        };
        aF.call(bx, a7, by, aZ);
        c.call(bx);
        an.call(bx);
        am.call(bx);
        var bc = bx.opt;
        var bo = bx.trigger;
        var bJ = bx.renderOverlay;
        var aY = bx.clearOverlays;
        var bt = bx.daySelectionMousedown;
        var aN = bx.cellToDate;
        var bC = bx.dateToCell;
        var bq = bx.rangeToSegments;
        var bw = by.formatDate;
        var bd;
        var a6;
        var a1;
        var bh;
        var aX;
        var a8;
        var bk;
        var bi;
        var aP;
        var a3;
        var bK;
        var bM;
        var bj;
        var a2;
        var be, aU;
        var a9;
        var bA;
        var aS;
        var bD;
        var bE;
        var bO;
        var aV;
        var bb;
        var bB;
        var bl;
        aL(a7.addClass("fc-grid"));
        function br(bP, bQ, bR) {
            be = bP;
            aU = bQ;
            a9 = bR;
            bH();
            if (!bh) {
                aQ();
            }
            bg();
        }
        function bH() {
            bO = bc("theme") ? "ui": "fc";
            aV = bc("columnFormat");
            bb = bc("weekNumbers");
            bB = bc("weekNumberTitle");
            if (bc("weekNumberCalculation") != "iso") {
                bl = "w";
            } else {
                bl = "W";
            }
        }
        function aQ() {
            a3 = au("<div class='fc-event-container' style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(a7);
        }
        function bg() {
            var bP = bp();
            if (bd) {
                bd.remove();
            }
            bd = au(bP).appendTo(a7);
            a6 = bd.find("thead");
            a1 = a6.find(".fc-day-header");
            bh = bd.find("tbody");
            aX = bh.find("tr");
            a8 = bh.find(".fc-day");
            bk = aX.find("td:first-child");
            bi = aX.eq(0).find(".fc-day > div");
            aP = aX.eq(0).find(".fc-day-content > div");
            g(a6.add(a6.find("tr")));
            g(aX);
            aX.eq(0).addClass("fc-first");
            aX.filter(":last").addClass("fc-last");
            a8.each(function(bR, bS) {
                var bQ = aN(Math.floor(bR / aU), bR % aU);
                bo("dayRender", bx, bQ, au(bS));
                if("undefined" != typeof(fc_day_createplan)){
                	var data_date;
                	if(data_date = bS.getAttribute('data-date'))
                		bS.setAttribute("onClick", "zxcreateplan('"+data_date+"')");
                }
            });
            aT(a8);
        }
        function bp() {
            var bP = "<table class='fc-border-separate' style='width:100%' cellspacing='0'>" + aO() + bf() + "</table>";
            return bP;
        }
        function aO() {
            var bS = bO + "-widget-header";
            var bR = "";
            var bQ;
            var bP;
            bR += "<thead><tr>";
            if (bb) {
                bR += "<th class='fc-week-number " + bS + "'>" + aH(bB) + "</th>";
            }
            for (bQ = 0; bQ < aU; bQ++) {
                bP = aN(0, bQ);
                bR += "<th class='fc-day-header fc-" + Q[bP.getDay()] + " " + bS + "'>" + aH(bw(bP, aV)) + "</th>";
            }
            bR += "</tr></thead>";
            return bR;
        }
        function bf() {
            var bR = bO + "-widget-content";
            var bS = "";
            var bT;
            var bQ;
            var bP;
            bS += "<tbody>";
            for (bT = 0; bT < be; bT++) {
                bS += "<tr class='fc-week'>";
                if (bb) {
                    bP = aN(bT, 0);
                    bS += "<td class='fc-week-number " + bR + "'>" + "<div>" + aH(bw(bP, bl)) + "</div>" + "</td>";
                }
                for (bQ = 0; bQ < aU; bQ++) {
                    bP = aN(bT, bQ);
                    bS += aW(bP);
                }
                bS += "</tr>";
            }
            bS += "</tbody>";
            return bS;
        }
        function aW(bR) {
            var bQ = bO + "-widget-content";
            var bT = bx.start.getMonth();
            var bP = e(new Date());
            var bS = "";
            var bU = ["fc-day", "fc-" + Q[bR.getDay()], bQ];
            if (bR.getMonth() != bT) {
                bU.push("fc-other-month");
            }
            if ( + bR == +bP) {
                bU.push("fc-today", bO + "-state-highlight");
            } else {
                if (bR < bP) {
                    bU.push("fc-past");
                } else {
                    bU.push("fc-future");
                }
            }
            bS += "<td" + " class='" + bU.join(" ") + "'" + " data-date='" + bw(bR, "yyyy-MM-dd") + "'" + ">" + "<div>";
            if (a9) {
                bS += "<div class='fc-day-number'>" + bR.getDate() + "</div>";
            }
            bS += "<div class='fc-day-content'>" + "<div style='position:relative'>&nbsp;</div>" + "</div>" + "</div>" + "</td>";
            return bS;
        }
        function bv(bQ) {
            bM = bQ;
            var bT = bM - a6.height();
            var bS;
            var bR;
            var bP;
            if (bc("weekMode") == "variable") {
                bS = bR = Math.floor(bT / (be == 1 ? 2 : 6));
            } else {
                bS = Math.floor(bT / be);
                bR = bT - bS * (be - 1);
            }
            bk.each(function(bU, bV) {
                if (bU < be) {
                    bP = au(bV);
                    bP.find("> div").css("min-height", (bU == be - 1 ? bR: bS) - H(bP));
                }
            });
        }
        function bG(bP) {
            bK = bP;
            bD.clear();
            bE.clear();
            a2 = 0;
            if (bb) {
                a2 = a6.find("th.fc-week-number").outerWidth();
            }
            bj = Math.floor((bK - a2) / aU);
            av(a1.slice(0, -1), bj);
        }
        function aT(bP) {
            bP.click(bI).mousedown(bt);
        }
        function bI(bQ) {
            if (!bc("selectable")) {
                var bP = p(au(this).data("date"));
                bo("dayClick", this, bP, true, bQ);
            }
        }
        function bn(bT, bU, bP) {
            if (bP) {
                bA.build();
            }
            var bQ = bq(bT, bU);
            for (var bR = 0; bR < bQ.length; bR++) {
                var bS = bQ[bR];
                aT(bF(bS.row, bS.leftCol, bS.row, bS.rightCol));
            }
        }
        function bF(bS, bT, bQ, bR) {
            var bP = bA.rect(bS, bT, bQ, bR, a7);
            return bJ(bP, a7);
        }
        function bm(bP, bQ) {
            return O(bP);
        }
        function bz(bP, bR, bQ) {
            bn(bP, aE(O(bR), 1), true);
        }
        function aM() {
            aY();
        }
        function a5(bR, bT, bS) {
            var bP = bC(bR);
            var bQ = a8[bP.row * aU + bP.col];
            bo("dayClick", bQ, bR, bT, bS);
        }
        function aR(bR, bP, bQ) {
            aS.start(function(bS) {
                aY();
                if (bS) {
                    bF(bS.row, bS.col, bS.row, bS.col);
                }
            },
            bP);
        }
        function a0(bT, bQ, bR) {
            var bP = aS.stop();
            aY();
            if (bP) {
                var bS = aN(bP);
                bo("drop", bT, bS, true, bQ, bR);
            }
        }
        function bL(bP) {
            return O(bP.start);
        }
        bA = new P(function(bP, bS) {
            var bR, bT, bQ;
            a1.each(function(bV, bU) {
                bR = au(bU);
                bT = bR.offset().left;
                if (bV) {
                    bQ[1] = bT;
                }
                bQ = [bT];
                bS[bV] = bQ;
            });
            bQ[1] = bT + bR.outerWidth();
            aX.each(function(bV, bU) {
                if (bV < be) {
                    bR = au(bU);
                    bT = bR.offset().top;
                    if (bV) {
                        bQ[1] = bT;
                    }
                    bQ = [bT];
                    bP[bV] = bQ;
                }
            });
            bQ[1] = bT + bR.outerHeight();
        });
        aS = new ap(bA);
        bD = new n(function(bP) {
            return bi.eq(bP);
        });
        bE = new n(function(bP) {
            return aP.eq(bP);
        });
        function bN(bP) {
            return bD.left(bP);
        }
        function ba(bP) {
            return bD.right(bP);
        }
        function bu(bP) {
            return bE.left(bP);
        }
        function bs(bP) {
            return bE.right(bP);
        }
        function a4(bP) {
            return aX.eq(bP);
        }
    }
    function am() {
        var aN = this;
        aN.renderEvents = aM;
        aN.clearEvents = aO;
        Y.call(aN);
        function aM(aQ, aP) {
            aN.renderDayEvents(aQ, aP);
        }
        function aO() {
            aN.getDaySegmentContainer().empty();
        }
    }
    af.agendaWeek = i;
    function i(aR, aQ) {
        var aU = this;
        aU.render = aM;
        I.call(aU, aR, aQ, "agendaWeek");
        var aN = aU.opt;
        var aO = aU.renderAgenda;
        var aT = aU.skipHiddenDays;
        var aP = aU.getCellsPerWeek;
        var aS = aQ.formatDates;
        function aM(aZ, a1) {
            if (a1) {
                aE(aZ, a1 * 7);
            }
            var a0 = aE(O(aZ), -((aZ.getDay() - aN("firstDay") + 7) % 7));
            var aX = aE(O(a0), 7);
            var aW = O(a0);
            aT(aW);
            var aV = O(aX);
            aT(aV, -1, true);
            var aY = aP();
            aU.title = aS(aW, aE(O(aV), -1), aN("titleFormat"));
            aU.start = a0;
            aU.end = aX;
            aU.visStart = aW;
            aU.visEnd = aV;
            aO(aY);
        }
    }
    af.agendaDay = ay;
    function ay(aQ, aT) {
        var aP = this;
        aP.render = aR;
        I.call(aP, aQ, aT, "agendaDay");
        var aO = aP.opt;
        var aM = aP.renderAgenda;
        var aN = aP.skipHiddenDays;
        var aS = aT.formatDate;
        function aR(aV, aX) {
            if (aX) {
                aE(aV, aX);
            }
            aN(aV, aX < 0 ? -1 : 1);
            var aW = O(aV, true);
            var aU = aE(O(aW), 1);
            aP.title = aS(aV, aO("titleFormat"));
            aP.start = aP.visStart = aW;
            aP.end = aP.visEnd = aU;
            aM(1);
        }
    }
    aj({
        allDaySlot: true,
        allDayText: "all-day",
        firstHour: 6,
        slotMinutes: 30,
        defaultEventMinutes: 120,
        axisFormat: "h(:mm)tt",
        timeFormat: {
            agenda: "h:mm{ - h:mm}"
        },
        dragOpacity: {
            agenda: 0.5
        },
        minTime: 0,
        maxTime: 24,
        slotEventOverlap: true
    });
    function I(cb, a2, bf) {
        var bc = this;
        bc.renderAgenda = aY;
        bc.setWidth = bo;
        bc.setHeight = bk;
        bc.afterRender = bU;
        bc.defaultEventEnd = bT;
        bc.timePosition = ce;
        bc.getIsCellAllDay = bv;
        bc.allDayRow = bC;
        bc.getCoordinateGrid = function() {
            return bH;
        };
        bc.getHoverListener = function() {
            return ca;
        };
        bc.colLeft = b5;
        bc.colRight = bM;
        bc.colContentLeft = cf;
        bc.colContentRight = aU;
        bc.getDaySegmentContainer = function() {
            return a7;
        };
        bc.getSlotSegmentContainer = function() {
            return aS;
        };
        bc.getMinMinute = function() {
            return bj;
        };
        bc.getMaxMinute = function() {
            return aO;
        };
        bc.getSlotContainer = function() {
            return a5;
        };
        bc.getRowCnt = function() {
            return 1;
        };
        bc.getColCnt = function() {
            return bF;
        };
        bc.getColWidth = function() {
            return aV;
        };
        bc.getSnapHeight = function() {
            return b7;
        };
        bc.getSnapMinutes = function() {
            return bs;
        };
        bc.defaultSelectionEnd = bn;
        bc.renderDayOverlay = aQ;
        bc.renderSelection = b3;
        bc.clearSelection = bY;
        bc.reportDayClick = a9;
        bc.dragStart = aW;
        bc.dragStop = cc;
        aF.call(bc, cb, a2, bf);
        c.call(bc);
        an.call(bc);
        V.call(bc);
        var bA = bc.opt;
        var bD = bc.trigger;
        var br = bc.renderOverlay;
        var aP = bc.clearOverlays;
        var bt = bc.reportSelection;
        var b1 = bc.unselect;
        var by = bc.daySelectionMousedown;
        var a4 = bc.slotSegHtml;
        var ba = bc.cellToDate;
        var aX = bc.dateToCell;
        var bh = bc.rangeToSegments;
        var a8 = a2.formatDate;
        var bP;
        var b8;
        var bW;
        var bV;
        var be;
        var aR;
        var aM;
        var bS;
        var bL;
        var cd;
        var a7;
        var b9;
        var b0;
        var bp;
        var a5;
        var aS;
        var aZ;
        var aT;
        var bw;
        var bJ;
        var bl;
        var aV;
        var bz;
        var bG;
        var bs;
        var a3;
        var b7;
        var bF;
        var bK;
        var bH;
        var ca;
        var bE;
        var bx;
        var bg = {};
        var bq;
        var aN;
        var bj, aO;
        var a1;
        var bQ;
        var bB;
        var bu;
        aL(cb.addClass("fc-agenda"));
        function aY(cg) {
            bF = cg;
            bI();
            if (!bP) {
                bd();
            } else {
                bO();
            }
        }
        function bI() {
            bq = bA("theme") ? "ui": "fc";
            aN = bA("isRTL");
            bj = aI(bA("minTime"));
            aO = aI(bA("maxTime"));
            a1 = bA("columnFormat");
            bQ = bA("weekNumbers");
            bB = bA("weekNumberTitle");
            if (bA("weekNumberCalculation") != "iso") {
                bu = "w";
            } else {
                bu = "W";
            }
            bs = bA("snapMinutes") || bA("slotMinutes");
        }
        function bd() {
            var cl = bq + "-widget-header";
            var cg = bq + "-widget-content";
            var ck;
            var cn;
            var cj;
            var cm;
            var ci;
            var ch = bA("slotMinutes") % 15 == 0;
            bO();
            cd = au("<div style='position:absolute;z-index:2;left:0;width:100%'/>").appendTo(cb);
            if (bA("allDaySlot")) {
                a7 = au("<div class='fc-event-container' style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(cd);
                ck = "<table style='width:100%' class='fc-agenda-allday' cellspacing='0'>" + "<tr>" + "<th class='" + cl + " fc-agenda-axis'>" + bA("allDayText") + "</th>" + "<td>" + "<div class='fc-day-content'><div style='position:relative'/></div>" + "</td>" + "<th class='" + cl + " fc-agenda-gutter'>&nbsp;</th>" + "</tr>" + "</table>";
                b9 = au(ck).appendTo(cd);
                b0 = b9.find("tr");
                bm(b0.find("td"));
                cd.append("<div class='fc-agenda-divider " + cl + "'>" + "<div class='fc-agenda-divider-inner'/>" + "</div>");
            } else {
                a7 = au([]);
            }
            bp = au("<div style='position:absolute;width:100%;overflow-x:hidden;overflow-y:auto'/>").appendTo(cd);
            a5 = au("<div style='position:relative;width:100%;overflow:hidden'/>").appendTo(bp);
            aS = au("<div class='fc-event-container' style='position:absolute;z-index:8;top:0;left:0'/>").appendTo(a5);
            ck = "<table class='fc-agenda-slots' style='width:100%' cellspacing='0'>" + "<tbody>";
            cn = h();
            cm = k(O(cn), aO);
            k(cn, bj);
            bK = 0;
            for (cj = 0; cn < cm; cj++) {
                ci = cn.getMinutes();
                ck += "<tr class='fc-slot" + cj + " " + (!ci ? "": "fc-minor") + "'>" + "<th class='fc-agenda-axis " + cl + "'>" + ((!ch || !ci) ? a8(cn, bA("axisFormat")) : "&nbsp;") + "</th>" + "<td class='" + cg + "'>" + "<div style='position:relative'>&nbsp;</div>" + "</td>" + "</tr>";
                k(cn, bA("slotMinutes"));
                bK++;
            }
            ck += "</tbody>" + "</table>";
            aZ = au(ck).appendTo(a5);
            bZ(aZ.find("td"));
        }
        function bO() {
            var cg = b6();
            if (bP) {
                bP.remove();
            }
            bP = au(cg).appendTo(cb);
            b8 = bP.find("thead");
            bW = b8.find("th").slice(1, -1);
            bV = bP.find("tbody");
            be = bV.find("td").slice(0, -1);
            aR = be.find("> div");
            aM = be.find(".fc-day-content > div");
            bS = be.eq(0);
            bL = aR.eq(0);
            g(b8.add(b8.find("tr")));
            g(bV.add(bV.find("tr")));
        }
        function b6() {
            var cg = "<table style='width:100%' class='fc-agenda-days fc-border-separate' cellspacing='0'>" + b4() + bX() + "</table>";
            return cg;
        }
        function b4() {
            var cj = bq + "-widget-header";
            var ch;
            var ci = "";
            var ck;
            var cg;
            ci += "<thead>" + "<tr>";
            if (bQ) {
                ch = ba(0, 0);
                ck = a8(ch, bu);
                if (aN) {
                    ck += bB;
                } else {
                    ck = bB + ck;
                }
                ci += "<th class='fc-agenda-axis fc-week-number " + cj + "'>" + aH(ck) + "</th>";
            } else {
                ci += "<th class='fc-agenda-axis " + cj + "'>&nbsp;</th>";
            }
            for (cg = 0; cg < bF; cg++) {
                ch = ba(0, cg);
                ci += "<th class='fc-" + Q[ch.getDay()] + " fc-col" + cg + " " + cj + "'>" + aH(a8(ch, a1)) + "</th>";
            }
            ci += "<th class='fc-agenda-gutter " + cj + "'>&nbsp;</th>" + "</tr>" + "</thead>";
            return ci;
        }
        function bX() {
            var ci = bq + "-widget-header";
            var cj = bq + "-widget-content";
            var cl;
            var cn = e(new Date());
            var ck;
            var co;
            var ch;
            var cg;
            var cm = "";
            cm += "<tbody>" + "<tr>" + "<th class='fc-agenda-axis " + ci + "'>&nbsp;</th>";
            co = "";
            for (ck = 0; ck < bF; ck++) {
                cl = ba(0, ck);
                cg = ["fc-col" + ck, "fc-" + Q[cl.getDay()], cj];
                if ( + cl == +cn) {
                    cg.push(bq + "-state-highlight", "fc-today");
                } else {
                    if (cl < cn) {
                        cg.push("fc-past");
                    } else {
                        cg.push("fc-future");
                    }
                }
                ch = "<td class='" + cg.join(" ") + "'>" + "<div>" + "<div class='fc-day-content'>" + "<div style='position:relative'>&nbsp;</div>" + "</div>" + "</div>" + "</td>";
                co += ch;
            }
            cm += co;
            cm += "<td class='fc-agenda-gutter " + cj + "'>&nbsp;</td>" + "</tr>" + "</tbody>";
            return cm;
        }
        function bk(cg) {
            if (cg === w) {
                cg = bJ;
            }
            bJ = cg;
            bg = {};
            var ci = bV.position().top;
            var ch = bp.position().top;
            var cj = Math.min(cg - ci, aZ.height() + ch + 1);
            bL.height(cj - H(bS));
            cd.css("top", ci);
            bp.height(cj - ch - 1);
            bG = aZ.find("tr:first").height() + 1;
            a3 = bA("slotMinutes") / bs;
            b7 = bG / a3;
        }
        function bo(cj) {
            bw = cj;
            bE.clear();
            bx.clear();
            var cg = b8.find("th:first");
            if (b9) {
                cg = cg.add(b9.find("th:first"));
            }
            cg = cg.add(aZ.find("th:first"));
            bl = 0;
            av(cg.width("").each(function(ck, cl) {
                bl = Math.max(bl, au(cl).outerWidth());
            }), bl);
            var ch = bP.find(".fc-agenda-gutter");
            if (b9) {
                ch = ch.add(b9.find("th.fc-agenda-gutter"));
            }
            var ci = bp[0].clientWidth;
            bz = bp.width() - ci;
            if (bz) {
                av(ch, bz);
                ch.show().prev().removeClass("fc-last");
            } else {
                ch.hide().prev().addClass("fc-last");
            }
            aV = Math.floor((ci - bl) / bF);
            av(bW.slice(0, -1), aV);
        }
        function bi() {
            var cj = h();
            var ch = O(cj);
            ch.setHours(bA("firstHour"));
            var ci = ce(cj, ch) + 1;
            function cg() {
                bp.scrollTop(ci);
            }
            cg();
            setTimeout(cg, 0);
        }
        function bU() {
            bi();
        }
        function bm(cg) {
            cg.click(bN).mousedown(by);
        }
        function bZ(cg) {
            cg.click(bN).mousedown(a0);
        }
        function bN(ck) {
            if (!bA("selectable")) {
                var ci = Math.min(bF - 1, Math.floor((ck.pageX - bP.offset().left - bl) / aV));
                var ch = ba(0, ci);
                var cl = this.parentNode.className.match(/fc-slot(\d+)/);
                if (cl) {
                    var cj = parseInt(cl[1]) * bA("slotMinutes");
                    var cg = Math.floor(cj / 60);
                    ch.setHours(cg);
                    ch.setMinutes(cj % 60 + bj);
                    bD("dayClick", be[ci], ch, false, ck);
                } else {
                    bD("dayClick", be[ci], ch, true, ck);
                }
            }
        }
        function aQ(ck, cl, cg) {
            if (cg) {
                bH.build();
            }
            var ch = bh(ck, cl);
            for (var ci = 0; ci < ch.length; ci++) {
                var cj = ch[ci];
                bm(a6(cj.row, cj.leftCol, cj.row, cj.rightCol));
            }
        }
        function a6(cj, ck, ch, ci) {
            var cg = bH.rect(cj, ck, ch, ci, cd);
            return br(cg, cd);
        }
        function bb(ck, cp) {
            for (var ci = 0; ci < bF; ci++) {
                var cl = ba(0, ci);
                var ch = aE(O(cl), 1);
                var cj = new Date(Math.max(cl, ck));
                var co = new Date(Math.min(ch, cp));
                if (cj < co) {
                    var cn = bH.rect(0, ci, 0, ci, a5);
                    var cm = ce(cl, cj);
                    var cg = ce(cl, co);
                    cn.top = cm;
                    cn.height = cg - cm;
                    bZ(br(cn, a5));
                }
            }
        }
        bH = new P(function(co, cm) {
            var ck, ch, cg;
            bW.each(function(cr, cq) {
                ck = au(cq);
                ch = ck.offset().left;
                if (cr) {
                    cg[1] = ch;
                }
                cg = [ch];
                cm[cr] = cg;
            });
            cg[1] = ch + ck.outerWidth();
            if (bA("allDaySlot")) {
                ck = b0;
                ch = ck.offset().top;
                co[0] = [ch, ch + ck.outerHeight()];
            }
            var cn = a5.offset().top;
            var cp = bp.offset().top;
            var cj = cp + bp.outerHeight();
            function cl(cq) {
                return Math.max(cp, Math.min(cj, cq));
            }
            for (var ci = 0; ci < bK * a3; ci++) {
                co.push([cl(cn + b7 * ci), cl(cn + b7 * (ci + 1))]);
            }
        });
        ca = new ap(bH);
        bE = new n(function(cg) {
            return aR.eq(cg);
        });
        bx = new n(function(cg) {
            return aM.eq(cg);
        });
        function b5(cg) {
            return bE.left(cg);
        }
        function cf(cg) {
            return bx.left(cg);
        }
        function bM(cg) {
            return bE.right(cg);
        }
        function aU(cg) {
            return bx.right(cg);
        }
        function bv(cg) {
            return bA("allDaySlot") && !cg.row;
        }
        function bR(cg) {
            var ci = ba(0, cg.col);
            var ch = cg.row;
            if (bA("allDaySlot")) {
                ch--;
            }
            if (ch >= 0) {
                k(ci, bj + ch * bs);
            }
            return ci;
        }
        function ce(ch, cl) {
            ch = O(ch, true);
            if (cl < k(O(ch), bj)) {
                return 0;
            }
            if (cl >= k(O(ch), aO)) {
                return aZ.height();
            }
            var cg = bA("slotMinutes"),
            ck = cl.getHours() * 60 + cl.getMinutes() - bj,
            cj = Math.floor(ck / cg),
            ci = bg[cj];
            if (ci === w) {
                ci = bg[cj] = aZ.find("tr").eq(cj).find("td div")[0].offsetTop;
            }
            return Math.max(0, Math.round(ci - 1 + bG * ((ck % cg) / cg)));
        }
        function bC(cg) {
            return b0;
        }
        function bT(cg) {
            var ch = O(cg.start);
            if (cg.allDay) {
                return ch;
            }
            return k(ch, bA("defaultEventMinutes"));
        }
        function bn(cg, ch) {
            if (ch) {
                return O(cg);
            }
            return k(O(cg), bA("slotMinutes"));
        }
        function b3(cg, ci, ch) {
            if (ch) {
                if (bA("allDaySlot")) {
                    aQ(cg, aE(O(ci), 1), true);
                }
            } else {
                b2(cg, ci);
            }
        }
        function b2(cg, cm) {
            var ck = bA("selectHelper");
            bH.build();
            if (ck) {
                var ci = aX(cg).col;
                if (ci >= 0 && ci < bF) {
                    var cj = bH.rect(0, ci, 0, ci, a5);
                    var cl = ce(cg, cg);
                    var ch = ce(cg, cm);
                    if (ch > cl) {
                        cj.top = cl;
                        cj.height = ch - cl;
                        cj.left += 2;
                        cj.width -= 5;
                        if (au.isFunction(ck)) {
                            var cn = ck(cg, cm);
                            if (cn) {
                                cj.position = "absolute";
                                aT = au(cn).css(cj).appendTo(a5);
                            }
                        } else {
                            cj.isStart = true;
                            cj.isEnd = true;
                            aT = au(a4({
                                title: "",
                                start: cg,
                                end: cm,
                                className: ["fc-select-helper"],
                                editable: false
                            },
                            cj));
                            aT.css("opacity", bA("dragOpacity"));
                        }
                        if (aT) {
                            bZ(aT);
                            a5.append(aT);
                            av(aT, cj.width, true);
                            Z(aT, cj.height, true);
                        }
                    }
                }
            } else {
                bb(cg, cm);
            }
        }
        function bY() {
            aP();
            if (aT) {
                aT.remove();
                aT = null;
            }
        }
        function a0(cg) {
            if (cg.which == 1 && bA("selectable")) {
                b1(cg);
                var ch;
                ca.start(function(cj, ci) {
                    bY();
                    if (cj && cj.col == ci.col && !bv(cj)) {
                        var cl = bR(ci);
                        var ck = bR(cj);
                        ch = [cl, k(O(cl), bs), ck, k(O(ck), bs)].sort(a);
                        b2(ch[0], ch[3]);
                    } else {
                        ch = null;
                    }
                },
                cg);
                au(document).one("mouseup",
                function(ci) {
                    ca.stop();
                    if (ch) {
                        if ( + ch[0] == +ch[1]) {
                            a9(ch[0], false, ci);
                        }
                        bt(ch[0], ch[3], false, ci);
                    }
                });
            }
        }
        function a9(cg, ci, ch) {
            bD("dayClick", be[aX(cg).col], cg, ci, ch);
        }
        function aW(ci, cg, ch) {
            ca.start(function(cj) {
                aP();
                if (cj) {
                    if (bv(cj)) {
                        a6(cj.row, cj.col, cj.row, cj.col);
                    } else {
                        var cl = bR(cj);
                        var ck = k(O(cl), bA("defaultEventMinutes"));
                        bb(cl, ck);
                    }
                }
            },
            cg);
        }
        function cc(cj, ch, ci) {
            var cg = ca.stop();
            aP();
            if (cg) {
                bD("drop", cj, bR(cg), bv(cg), ch, ci);
            }
        }
    }
    function V() {
        var bl = this;
        bl.renderEvents = aR;
        bl.clearEvents = a6;
        bl.slotSegHtml = a7;
        Y.call(bl);
        var a1 = bl.opt;
        var a8 = bl.trigger;
        var aP = bl.isEventDraggable;
        var bq = bl.isEventResizable;
        var br = bl.eventEnd;
        var bb = bl.eventElementHandlers;
        var bh = bl.setHeight;
        var aQ = bl.getDaySegmentContainer;
        var bt = bl.getSlotSegmentContainer;
        var aO = bl.getHoverListener;
        var aN = bl.getMaxMinute;
        var bc = bl.getMinMinute;
        var aU = bl.timePosition;
        var be = bl.getIsCellAllDay;
        var bg = bl.colContentLeft;
        var bd = bl.colContentRight;
        var aM = bl.cellToDate;
        var bi = bl.getColCnt;
        var a2 = bl.getColWidth;
        var bk = bl.getSnapHeight;
        var aW = bl.getSnapMinutes;
        var aY = bl.getSlotContainer;
        var aZ = bl.reportEventElement;
        var aS = bl.showEvents;
        var a0 = bl.hideEvents;
        var bo = bl.eventDrop;
        var a4 = bl.eventResize;
        var a9 = bl.renderDayOverlay;
        var aT = bl.clearOverlays;
        var bu = bl.renderDayEvents;
        var bm = bl.calendar;
        var bj = bm.formatDate;
        var a3 = bm.formatDates;
        bl.draggableDayEvent = aX;
        function aR(bz, bx) {
            var by, bw = bz.length,
            bA = [],
            bv = [];
            for (by = 0; by < bw; by++) {
                if (bz[by].allDay) {
                    bA.push(bz[by]);
                } else {
                    bv.push(bz[by]);
                }
            }
            if (a1("allDaySlot")) {
                bu(bA, bx);
                bh();
            }
            aV(a5(bv), bx);
        }
        function a6() {
            aQ().empty();
            bt().empty();
        }
        function a5(bF) {
            var bD = bi(),
            bB = bc(),
            bw = aN(),
            bC,
            bz = au.map(bF, bs),
            bA,
            by,
            bx,
            bE,
            bv = [];
            for (bA = 0; bA < bD; bA++) {
                bC = aM(0, bA);
                k(bC, bB);
                bE = bf(bF, bz, bC, k(O(bC), bw - bB));
                bE = ak(bE);
                for (by = 0; by < bE.length; by++) {
                    bx = bE[by];
                    bx.col = bA;
                    bv.push(bx);
                }
            }
            return bv;
        }
        function bf(bH, bB, bx, bA) {
            var by = [],
            bC,
            bE = bH.length,
            bw,
            bF,
            bD,
            bG,
            bI,
            bv,
            bz;
            for (bC = 0; bC < bE; bC++) {
                bw = bH[bC];
                bF = bw.start;
                bD = bB[bC];
                if (bD > bx && bF < bA) {
                    if (bF < bx) {
                        bG = O(bx);
                        bv = false;
                    } else {
                        bG = bF;
                        bv = true;
                    }
                    if (bD > bA) {
                        bI = O(bA);
                        bz = false;
                    } else {
                        bI = bD;
                        bz = true;
                    }
                    by.push({
                        event: bw,
                        start: bG,
                        end: bI,
                        isStart: bv,
                        isEnd: bz
                    });
                }
            }
            return by.sort(y);
        }
        function bs(bv) {
            if (bv.end) {
                return O(bv.end);
            } else {
                return k(O(bv.start), a1("defaultEventMinutes"));
            }
        }
        function aV(bH, bI) {
            var bM, bP = bH.length,
            bO, bK, bE, bA, bB, bD, by, bJ, bw, bQ, bC = "",
            bR, bN, bz, bG, bF, bL = bt(),
            bx = a1("isRTL");
            for (bM = 0; bM < bP; bM++) {
                bO = bH[bM];
                bK = bO.event;
                bE = aU(bO.start, bO.start);
                bA = aU(bO.start, bO.end);
                bB = bg(bO.col);
                bD = bd(bO.col);
                by = bD - bB;
                bD -= by * 0.025;
                by = bD - bB;
                bJ = by * (bO.forwardCoord - bO.backwardCoord);
                if (a1("slotEventOverlap")) {
                    bJ = Math.max((bJ - (20 / 2)) * 2, bJ);
                }
                if (bx) {
                    bQ = bD - bO.backwardCoord * by;
                    bw = bQ - bJ;
                } else {
                    bw = bB + bO.backwardCoord * by;
                    bQ = bw + bJ;
                }
                bw = Math.max(bw, bB);
                bQ = Math.min(bQ, bD);
                bJ = bQ - bw;
                bO.top = bE;
                bO.left = bw;
                bO.outerWidth = bJ;
                bO.outerHeight = bA - bE;
                bC += a7(bK, bO);
            }
            bL[0].innerHTML = bC;
            bR = bL.children();
            for (bM = 0; bM < bP; bM++) {
                bO = bH[bM];
                bK = bO.event;
                bN = au(bR[bM]);
                bz = a8("eventRender", bK, bK, bN);
                if (bz === false) {
                    bN.remove();
                } else {
                    if (bz && bz !== true) {
                        bN.remove();
                        bN = au(bz).css({
                            position: "absolute",
                            top: bO.top,
                            left: bO.left
                        }).appendTo(bL);
                    }
                    bO.element = bN;
                    if (bK._id === bI) {
                        bn(bK, bN, bO);
                    } else {
                        bN[0]._fci = bM;
                    }
                    aZ(bK, bN);
                }
            }
            v(bL, bH, bn);
            for (bM = 0; bM < bP; bM++) {
                bO = bH[bM];
                if (bN = bO.element) {
                    bO.vsides = H(bN, true);
                    bO.hsides = j(bN, true);
                    bG = bN.find(".fc-event-title");
                    if (bG.length) {
                        bO.contentTop = bG[0].offsetTop;
                    }
                }
            }
            for (bM = 0; bM < bP; bM++) {
                bO = bH[bM];
                var bv = "";
                if (bN = bO.element) {
                    bN[0].style.width = Math.max(0, bO.outerWidth - bO.hsides) + "px";
                    bF = Math.max(0, bO.outerHeight - bO.vsides);
                    bF = bF > 15 ? bF: 15;
                    bN[0].style.height = bF + "px";
                    bN[0].style.marginRight = "3px";
                    bK = bO.event;
                    bN[0].title = bK.title;
                    if (bF <= 42) {
                        bv = window.iTeamGlobal.getStringByLen(bK.title, 12, "..");
                    } else {
                        bv = bK.title;
                    }
                    if (bO.contentTop !== w && bF - bO.contentTop < 10) {
                        bN.find("div.fc-event-time").text(bj(bK.start, a1("timeFormat")) + " - " + bv);
                        bN.find("div.fc-event-title").remove();
                    } else {
                        bN.find("div.fc-event-title").text(bv);
                    }
                    a8("eventAfterRender", bK, bK, bN);
                }
            }
        }
        function a7(bA, bv) {
            var bz = "<";
            var bx = bA.url;
            var bw = R(bA, a1);
            var by = ["fc-event", "fc-event-vert"];
            if (aP(bA)) {
                by.push("fc-event-draggable");
            }
            if (bv.isStart) {
                by.push("fc-event-start");
            }
            if (bv.isEnd) {
                by.push("fc-event-end");
            }
            by = by.concat(bA.className);
            if (bA.source) {
                by = by.concat(bA.source.className || []);
            }
            if (bx) {
                bz += "a href='" + aH(bA.url) + "'";
            } else {
                bz += "div";
            }
            bz += " class='" + by.join(" ") + "'" + " style=" + "'" + "position:absolute;" + "top:" + bv.top + "px;" + "left:" + bv.left + "px;" + bw + "'" + ">" + "<div class='fc-event-inner'>" + "<div class='fc-event-time'>" + aH(a3(bA.start, bA.end, a1("timeFormat"))) + "</div>" + "<div class='fc-event-title'>" + aH(bA.title || "") + "</div>" + "</div>" + "<div class='fc-event-bg'></div>";
            if (bv.isEnd && bq(bA)) {
                bz += "<div class='ui-resizable-handle ui-resizable-s'>=</div>";
            }
            bz += "</" + (bx ? "a": "div") + ">";
            return bz;
        }
        function bn(bx, bw, bv) {
            var by = bw.find("div.fc-event-time");
            if (aP(bx)) {
                ba(bx, bw, by);
            }
            if (bv.isEnd && bq(bx)) {
                bp(bx, bw, by);
            }
            bb(bx, bw);
        }
        function aX(bw, bF, bA) {
            var bv = bA.isStart;
            var bG;
            var bE;
            var bI = true;
            var bz;
            var by = aO();
            var bB = a2();
            var bx = bk();
            var bH = aW();
            var bD = bc();
            bF.draggable({
                opacity: a1("dragOpacity", "month"),
                revertDuration: a1("dragRevertDuration"),
                start: function(bJ, bK) {
                    a8("eventDragStart", bF, bw, bJ, bK);
                    a0(bw, bF);
                    bG = bF.width();
                    by.start(function(bM, bL) {
                        aT();
                        if (bM) {
                            bE = false;
                            var bO = aM(0, bL.col);
                            var bN = aM(0, bM.col);
                            bz = aB(bN, bO);
                            if (!bM.row) {
                                a9(aE(O(bw.start), bz), aE(ao(bw), bz));
                                bC();
                            } else {
                                if (bv) {
                                    if (bI) {
                                        bF.width(bB - 10);
                                        Z(bF, bx * Math.round((bw.end ? ((bw.end - bw.start) / U) : a1("defaultEventMinutes")) / bH));
                                        bF.draggable("option", "grid", [bB, 1]);
                                        bI = false;
                                    }
                                } else {
                                    bE = true;
                                }
                            }
                            bE = bE || (bI && !bz);
                        } else {
                            bC();
                            bE = true;
                        }
                        bF.draggable("option", "revert", bE);
                    },
                    bJ, "drag");
                },
                stop: function(bK, bL) {
                    by.stop();
                    aT();
                    a8("eventDragStop", bF, bw, bK, bL);
                    if (bE) {
                        bC();
                        bF.css("filter", "");
                        aS(bw, bF);
                    } else {
                        var bJ = 0;
                        if (!bI) {
                            bJ = Math.round((bF.offset().top - aY().offset().top) / bx) * bH + bD - (bw.start.getHours() * 60 + bw.start.getMinutes());
                        }
                        bo(this, bw, bz, bJ, bI, bK, bL);
                    }
                }
            });
            function bC() {
                if (!bI) {
                    bF.width(bG).height("").draggable("option", "grid", null);
                    bI = true;
                }
            }
        }
        function ba(bI, bK, bx) {
            var bD = bl.getCoordinateGrid();
            var bG = bi();
            var bP = a2();
            var bH = bk();
            var bC = aW();
            var bO;
            var bM;
            var bN, bF;
            var bv, by;
            var bw, bB;
            var bz;
            var bJ, bA;
            bK.draggable({
                scroll: false,
                grid: [bP, bH],
                axis: bG == 1 ? "y": false,
                opacity: a1("dragOpacity"),
                revertDuration: a1("dragRevertDuration"),
                start: function(bQ, bR) {
                    a8("eventDragStart", bK, bI, bQ, bR);
                    a0(bI, bK);
                    bD.build();
                    bO = bK.position();
                    bM = bD.cell(bQ.pageX, bQ.pageY);
                    bN = bF = true;
                    bv = by = be(bM);
                    bw = bB = 0;
                    bz = 0;
                    bJ = bA = 0;
                },
                drag: function(bU, bV) {
                    var bQ = bD.cell(bU.pageX, bU.pageY);
                    bN = !!bQ;
                    if (bN) {
                        bv = be(bQ);
                        bw = Math.round((bV.position.left - bO.left) / bP);
                        if (bw != bB) {
                            var bT = aM(0, bM.col);
                            var bS = bM.col + bw;
                            bS = Math.max(0, bS);
                            bS = Math.min(bG - 1, bS);
                            var bR = aM(0, bS);
                            bz = aB(bR, bT);
                        }
                        if (!bv) {
                            bJ = Math.round((bV.position.top - bO.top) / bH) * bC;
                        }
                    }
                    if (bN != bF || bv != by || bw != bB || bJ != bA) {
                        bL();
                        bF = bN;
                        by = bv;
                        bB = bw;
                        bA = bJ;
                    }
                    bK.draggable("option", "revert", !bN);
                },
                stop: function(bQ, bR) {
                    aT();
                    a8("eventDragStop", bK, bI, bQ, bR);
                    if (bN && (bv || bz || bJ)) {
                        bo(this, bI, bz, bv ? 0 : bJ, bv, bQ, bR);
                    } else {
                        bN = true;
                        bv = false;
                        bw = 0;
                        bz = 0;
                        bJ = 0;
                        bL();
                        bK.css("filter", "");
                        bK.css(bO);
                        aS(bI, bK);
                    }
                }
            });
            function bL() {
                aT();
                if (bN) {
                    if (bv) {
                        bx.hide();
                        bK.draggable("option", "grid", null);
                        a9(aE(O(bI.start), bz), aE(ao(bI), bz));
                    } else {
                        bE(bJ);
                        bx.css("display", "");
                        bK.draggable("option", "grid", [bP, bH]);
                    }
                }
            }
            function bE(bR) {
                var bQ = k(O(bI.start), bR);
                var bS;
                if (bI.end) {
                    bS = k(O(bI.end), bR);
                }
                bx.text(a3(bQ, bS, a1("timeFormat")));
            }
        }
        function bp(bz, bx, bA) {
            var by, bv;
            var bB = bk();
            var bw = aW();
            bx.resizable({
                handles: {
                    s: ".ui-resizable-handle"
                },
                grid: bB,
                start: function(bC, bD) {
                    by = bv = 0;
                    a0(bz, bx);
                    a8("eventResizeStart", this, bz, bC, bD);
                },
                resize: function(bC, bD) {
                    by = Math.round((Math.max(bB, bx.height()) - bD.originalSize.height) / bB);
                    if (by != bv) {
                        bA.text(a3(bz.start, (!by && !bz.end) ? null: k(br(bz), bw * by), a1("timeFormat")));
                        bv = by;
                    }
                },
                stop: function(bC, bD) {
                    a8("eventResizeStop", this, bz, bC, bD);
                    if (by) {
                        a4(this, bz, 0, bw * by, bC, bD);
                    } else {
                        aS(bz, bx);
                    }
                }
            });
        }
    }
    function ak(aM) {
        var aO = F(aM);
        var aP = aO[0];
        var aN;
        X(aO);
        if (aP) {
            for (aN = 0; aN < aP.length; aN++) {
                ac(aP[aN]);
            }
            for (aN = 0; aN < aP.length; aN++) {
                M(aP[aN], 0, 0);
            }
        }
        return C(aO);
    }
    function F(aN) {
        var aQ = [];
        var aP, aM;
        var aO;
        for (aP = 0; aP < aN.length; aP++) {
            aM = aN[aP];
            for (aO = 0; aO < aQ.length; aO++) {
                if (!aK(aM, aQ[aO]).length) {
                    break;
                }
            } (aQ[aO] || (aQ[aO] = [])).push(aM);
        }
        return aQ;
    }
    function X(aQ) {
        var aP, aR;
        var aO, aM;
        var aN;
        for (aP = 0; aP < aQ.length; aP++) {
            aR = aQ[aP];
            for (aO = 0; aO < aR.length; aO++) {
                aM = aR[aO];
                aM.forwardSegs = [];
                for (aN = aP + 1; aN < aQ.length; aN++) {
                    aK(aM, aQ[aN], aM.forwardSegs);
                }
            }
        }
    }
    function ac(aN) {
        var aM = aN.forwardSegs;
        var aQ = 0;
        var aP, aO;
        if (aN.forwardPressure === w) {
            for (aP = 0; aP < aM.length; aP++) {
                aO = aM[aP];
                ac(aO);
                aQ = Math.max(aQ, 1 + aO.forwardPressure);
            }
            aN.forwardPressure = aQ;
        }
    }
    function M(aN, aP, aQ) {
        var aM = aN.forwardSegs;
        var aO;
        if (aN.forwardCoord === w) {
            if (!aM.length) {
                aN.forwardCoord = 1;
            } else {
                aM.sort(az);
                M(aM[0], aP + 1, aQ);
                aN.forwardCoord = aM[0].backwardCoord;
            }
            aN.backwardCoord = aN.forwardCoord - (aN.forwardCoord - aQ) / (aP + 1);
            for (aO = 0; aO < aM.length; aO++) {
                M(aM[aO], 0, aN.forwardCoord);
            }
        }
    }
    function C(aP) {
        var aM = [];
        var aO, aQ;
        var aN;
        for (aO = 0; aO < aP.length; aO++) {
            aQ = aP[aO];
            for (aN = 0; aN < aQ.length; aN++) {
                aM.push(aQ[aN]);
            }
        }
        return aM;
    }
    function aK(aM, aP, aO) {
        aO = aO || [];
        for (var aN = 0; aN < aP.length; aN++) {
            if (d(aM, aP[aN])) {
                aO.push(aP[aN]);
            }
        }
        return aO;
    }
    function d(aN, aM) {
        return aN.end > aM.start && aN.start < aM.end;
    }
    function az(aN, aM) {
        return aM.forwardPressure - aN.forwardPressure || (aN.backwardCoord || 0) - (aM.backwardCoord || 0) || y(aN, aM);
    }
    function y(aN, aM) {
        return aN.start - aM.start || (aM.end - aM.start) - (aN.end - aN.start) || (aN.event.title || "").localeCompare(aM.event.title);
    }
    function aF(a1, bl, aY) {
        var bk = this;
        bk.element = a1;
        bk.calendar = bl;
        bk.name = aY;
        bk.opt = a6;
        bk.trigger = be;
        bk.isEventDraggable = aR;
        bk.isEventResizable = bq;
        bk.setEventData = bn;
        bk.clearEventData = aQ;
        bk.eventEnd = br;
        bk.reportEventElement = a4;
        bk.triggerEventDestroy = bd;
        bk.eventElementHandlers = bg;
        bk.showEvents = aT;
        bk.hideEvents = a5;
        bk.eventDrop = bo;
        bk.eventResize = a9;
        var bt = bk.defaultEventEnd;
        var bf = bl.normalizeEvent;
        var bp = bl.reportEventChange;
        var a3 = {};
        var aX = {};
        var bs = [];
        var bb = bl.options;
        function a6(by, bw) {
            var bx = bb[by];
            if (au.isPlainObject(bx)) {
                return G(bx, bw || aY);
            }
            return bx;
        }
        function be(bw, bx) {
            return bl.trigger.apply(bl, [bw, bx || bk].concat(Array.prototype.slice.call(arguments, 2), [bk]));
        }
        function aR(bw) {
            var bx = bw.source || {};
            return aJ(bw.startEditable, bx.startEditable, a6("eventStartEditable"), bw.editable, bx.editable, a6("editable")) && !a6("disableDragging");
        }
        function bq(bw) {
            var bx = bw.source || {};
            return aJ(bw.durationEditable, bx.durationEditable, a6("eventDurationEditable"), bw.editable, bx.editable, a6("editable")) && !a6("disableResizing");
        }
        function bn(by) {
            a3 = {};
            var bx, bw = by.length,
            bz;
            for (bx = 0; bx < bw; bx++) {
                bz = by[bx];
                if (a3[bz._id]) {
                    a3[bz._id].push(bz);
                } else {
                    a3[bz._id] = [bz];
                }
            }
        }
        function aQ() {
            a3 = {};
            aX = {};
            bs = [];
        }
        function br(bw) {
            return bw.end ? O(bw.end) : bt(bw);
        }
        function a4(bx, bw) {
            bs.push({
                event: bx,
                element: bw
            });
            if (aX[bx._id]) {
                aX[bx._id].push(bw);
            } else {
                aX[bx._id] = [bw];
            }
        }
        function bd() {
            au.each(bs,
            function(bw, bx) {
                bk.trigger("eventDestroy", bx.event, bx.event, bx.element);
            });
        }
        function bg(bx, bw) {
            bw.click(function(by) {
                if (!bw.hasClass("ui-draggable-dragging") && !bw.hasClass("ui-resizable-resizing")) {
                    return be("eventClick", this, bx, by);
                }
            }).hover(function(by) {
                be("eventMouseover", this, bx, by);
            },
            function(by) {
                be("eventMouseout", this, bx, by);
            });
        }
        function aT(bx, bw) {
            a2(bx, bw, "show");
        }
        function a5(bx, bw) {
            a2(bx, bw, "hide");
        }
        function a2(bz, by, bB) {
            var bA = aX[bz._id],
            bx,
            bw = bA.length;
            for (bx = 0; bx < bw; bx++) {
                if (!by || bA[bx][0] != by[0]) {
                    bA[bx][bB]();
                }
            }
        }
        function bo(bz, bx, by, bA, bD, bC, bB) {
            var bE = bx.allDay;
            var bw = bx._id;
            a7(a3[bw], by, bA, bD);
            be("eventDrop", bz, bx, by, bA, bD,
            function() {
                a7(a3[bw], -by, -bA, bE);
                bp(bw);
            },
            bC, bB);
            bp(bw);
        }
        function a9(bC, bA, bx, bw, bz, bB) {
            var by = bA._id;
            aZ(a3[by], bx, bw);
            be("eventResize", bC, bA, bx, bw,
            function() {
                aZ(a3[by], -bx, -bw);
                bp(by);
            },
            bz, bB);
            bp(by);
        }
        function a7(bA, by, bx, bB) {
            bx = bx || 0;
            for (var bC, bw = bA.length,
            bz = 0; bz < bw; bz++) {
                bC = bA[bz];
                if (bB !== w) {
                    bC.allDay = bB;
                }
                k(aE(bC.start, by, true), bx);
                if (bC.end) {
                    bC.end = k(aE(bC.end, by, true), bx);
                }
                bf(bC, bb);
            }
        }
        function aZ(bA, by, bx) {
            bx = bx || 0;
            for (var bB, bw = bA.length,
            bz = 0; bz < bw; bz++) {
                bB = bA[bz];
                bB.end = k(aE(br(bB), by, true), bx);
                bf(bB, bb);
            }
        }
        bk.isHiddenDay = aV;
        bk.skipHiddenDays = aS;
        bk.getCellsPerWeek = bi;
        bk.dateToCell = bm;
        bk.dateToDayOffset = ba;
        bk.dayOffsetToCellOffset = aO;
        bk.cellOffsetToCell = a0;
        bk.cellToDate = aP;
        bk.cellToCellOffset = aM;
        bk.cellOffsetToDayOffset = bc;
        bk.dayOffsetToDate = bv;
        bk.rangeToSegments = bh;
        var a8 = a6("hiddenDays") || [];
        var aN = [];
        var bu;
        var aU = [];
        var aW = [];
        var bj = a6("isRTL"); (function() {
            if (a6("weekends") === false) {
                a8.push(0, 6);
            }
            for (var bx = 0,
            bw = 0; bx < 7; bx++) {
                aU[bx] = bw;
                aN[bx] = au.inArray(bx, a8) != -1;
                if (!aN[bx]) {
                    aW[bw] = bx;
                    bw++;
                }
            }
            bu = bw;
            if (!bu) {
                throw "invalid hiddenDays";
            }
        })();
        function aV(bw) {
            if (typeof bw == "object") {
                bw = bw.getDay();
            }
            return aN[bw];
        }
        function bi() {
            return bu;
        }
        function aS(bx, by, bw) {
            by = by || 1;
            while (aN[(bx.getDay() + (bw ? by: 0) + 7) % 7]) {
                aE(bx, by);
            }
        }
        function aP() {
            var by = aM.apply(null, arguments);
            var bw = bc(by);
            var bx = bv(bw);
            return bx;
        }
        function aM(bB, bz) {
            var by = bk.getColCnt();
            var bx = bj ? -1 : 1;
            var bw = bj ? by - 1 : 0;
            if (typeof bB == "object") {
                bz = bB.col;
                bB = bB.row;
            }
            var bA = bB * by + (bz * bx + bw);
            return bA;
        }
        function bc(bx) {
            var bw = bk.visStart.getDay();
            bx += aU[bw];
            return Math.floor(bx / bu) * 7 + aW[(bx % bu + bu) % bu] - bw;
        }
        function bv(bw) {
            var bx = O(bk.visStart);
            aE(bx, bw);
            return bx;
        }
        function bm(by) {
            var bx = ba(by);
            var bz = aO(bx);
            var bw = a0(bz);
            return bw;
        }
        function ba(bw) {
            return aB(bw, bk.visStart);
        }
        function aO(bx) {
            var bw = bk.visStart.getDay();
            bx += bw;
            return Math.floor(bx / 7) * bu + aU[(bx % 7 + 7) % 7] - aU[bw];
        }
        function a0(bB) {
            var bz = bk.getColCnt();
            var bx = bj ? -1 : 1;
            var bw = bj ? bz - 1 : 0;
            var bA = Math.floor(bB / bz);
            var by = ((bB % bz + bz) % bz) * bx + bw;
            return {
                row: bA,
                col: by
            };
        }
        function bh(bx, bM) {
            var bA = bk.getRowCnt();
            var bK = bk.getColCnt();
            var bL = [];
            var bO = ba(bx);
            var bJ = ba(bM);
            var bN = aO(bO);
            var bB = aO(bJ) - 1;
            for (var bC = 0; bC < bA; bC++) {
                var by = bC * bK;
                var bG = by + bK - 1;
                var bH = Math.max(bN, by);
                var bz = Math.min(bB, bG);
                if (bH <= bz) {
                    var bw = a0(bH);
                    var bI = a0(bz);
                    var bF = [bw.col, bI.col].sort();
                    var bD = bc(bH) == bO;
                    var bE = bc(bz) + 1 == bJ;
                    bL.push({
                        row: bC,
                        leftCol: bF[0],
                        rightCol: bF[1],
                        isStart: bD,
                        isEnd: bE
                    });
                }
            }
            return bL;
        }
    }
    function Y() {
        var bt = this;
        bt.renderDayEvents = bA;
        bt.draggableDayEvent = a2;
        bt.resizableDayEvent = bn;
        var a7 = bt.opt;
        var bk = bt.trigger;
        var aU = bt.isEventDraggable;
        var bw = bt.isEventResizable;
        var bx = bt.eventEnd;
        var a5 = bt.reportEventElement;
        var bm = bt.eventElementHandlers;
        var aX = bt.showEvents;
        var a6 = bt.hideEvents;
        var bv = bt.eventDrop;
        var bb = bt.eventResize;
        var bB = bt.getRowCnt;
        var bs = bt.getColCnt;
        var a9 = bt.getColWidth;
        var a1 = bt.allDayRow;
        var bz = bt.colLeft;
        var a3 = bt.colRight;
        var br = bt.colContentLeft;
        var bp = bt.colContentRight;
        var bu = bt.dateToCell;
        var aV = bt.getDaySegmentContainer;
        var ba = bt.calendar.formatDates;
        var bl = bt.renderDayOverlay;
        var aY = bt.clearOverlays;
        var aO = bt.clearSelection;
        var aT = bt.getHoverListener;
        var bo = bt.rangeToSegments;
        var aR = bt.cellToDate;
        var aN = bt.cellToCellOffset;
        var bi = bt.cellOffsetToDayOffset;
        var bd = bt.dateToDayOffset;
        var aQ = bt.dayOffsetToCellOffset;
        function bA(bE, bD) {
            var bC = a0(bE, false, true);
            ae(bC,
            function(bG, bF) {
                a5(bG.event, bF);
            });
            aZ(bC, bD);
            ae(bC,
            function(bG, bF) {
                bk("eventAfterRender", bG.event, bG.event, bF);
            });
        }
        function be(bD, bG, bF) {
            var bC = a0([bD], true, false);
            var bE = [];
            ae(bC,
            function(bI, bH) {
                if (bI.row === bG) {
                    bH.css("top", bF);
                }
                bE.push(bH[0]);
            });
            return bE;
        }
        function a0(bF, bJ, bH) {
            var bG = aV();
            var bE = bJ ? au("<div/>") : bG;
            var bC = a4(bF);
            var bD;
            var bI;
            by(bC);
            bD = bc(bC);
            bE[0].innerHTML = bD;
            bI = bE.children();
            if (bJ) {
                bG.append(bI);
            }
            aW(bC, bI);
            ae(bC,
            function(bL, bK) {
                bL.hsides = j(bK, true);
            });
            ae(bC,
            function(bL, bK) {
                bK.width(Math.max(0, bL.outerWidth - bL.hsides));
            });
            ae(bC,
            function(bL, bK) {
                bL.outerHeight = bK.outerHeight(true);
            });
            aS(bC, bH);
            return bC;
        }
        function a4(bE) {
            var bC = [];
            for (var bD = 0; bD < bE.length; bD++) {
                var bF = bj(bE[bD]);
                bC.push.apply(bC, bF);
            }
            return bC;
        }
        function bj(bF) {
            var bC = bF.start;
            var bG = ao(bF);
            var bD = bo(bC, bG);
            for (var bE = 0; bE < bD.length; bE++) {
                bD[bE].event = bF;
            }
            return bD;
        }
        function by(bD) {
            var bG = a7("isRTL");
            for (var bF = 0; bF < bD.length; bF++) {
                var bH = bD[bF];
                var bI = (bG ? bH.isEnd: bH.isStart) ? br: bz;
                var bC = (bG ? bH.isStart: bH.isEnd) ? bp: a3;
                var bJ = bI(bH.leftCol);
                var bE = bC(bH.rightCol);
                bH.left = bJ;
                bH.outerWidth = bE - bJ;
            }
        }
        function bc(bC) {
            var bE = "";
            for (var bD = 0; bD < bC.length; bD++) {
                bE += a8(bC[bD]);
            }
            return bE;
        }
        function a8(bH) {
            var bF = "";
            var bE = a7("isRTL");
            var bG = bH.event;
            var bD = bG.url;
            var bI = ["fc-event", "fc-event-hori"];
            if (aU(bG)) {
                bI.push("fc-event-draggable");
            }
            if (bH.isStart) {
                bI.push("fc-event-start");
            }
            if (bH.isEnd) {
                bI.push("fc-event-end");
            }
            bI = bI.concat(bG.className);
            if (bG.source) {
                bI = bI.concat(bG.source.className || []);
            }
            var bC = R(bG, a7);
            if (bD) {
                bF += "<a href='" + aH(bD) + "'";
            } else {
                bF += "<div";
            }
            bF += " class='" + bI.join(" ") + "'" + " style=" + "'" + "position:absolute;" + "left:" + bH.left + "px;" + bC + "'" + ">" + "<div class='fc-event-inner' title='" + f(bG.start) + " - " + f(bG.end) + "'>";
            if (!bG.allDay && bH.isStart) {
                bF += "<span class='fc-event-time'>" + aH(ba(bG.start, bG.end, a7("timeFormat"))) + "</span>";
            }
            bF += "<span class='fc-event-title'>" + aH(bG.title || "") + "&nbsp;" + aH(bG.receive || "") + "</span>" + "</div>";
            if (bH.isEnd && bw(bG)) {
                bF += "<div class='ui-resizable-handle ui-resizable-" + (bE ? "w": "e") + "'>" + "&nbsp;&nbsp;&nbsp;" + "</div>";
            }
            bF += "</" + (bD ? "a": "div") + ">";
            return bF;
        }
        function aW(bD, bI) {
            for (var bF = 0; bF < bD.length; bF++) {
                var bH = bD[bF];
                var bG = bH.event;
                var bE = bI.eq(bF);
                var bC = bk("eventRender", bG, bG, bE);
                if (bC === false) {
                    bE.remove();
                } else {
                    if (bC && bC !== true) {
                        bC = au(bC).css({
                            position: "absolute",
                            left: bH.left
                        });
                        bE.replaceWith(bC);
                        bE = bC;
                    }
                    bH.element = bE;
                }
            }
        }
        function aS(bE, bG) {
            var bH = aM(bE);
            var bC = bq();
            var bD = [];
            if (bG) {
                for (var bF = 0; bF < bC.length; bF++) {
                    bC[bF].height(bH[bF]);
                }
            }
            for (var bF = 0; bF < bC.length; bF++) {
                bD.push(bC[bF].position().top);
            }
            ae(bE,
            function(bJ, bI) {
                bI.css("top", bD[bJ.row] + bJ.top);
            });
        }
        function aM(bE) {
            var bG = bB();
            var bL = bs();
            var bM = [];
            var bC = bh(bE);
            for (var bK = 0; bK < bG; bK++) {
                var bJ = bC[bK];
                var bI = [];
                for (var bH = 0; bH < bL; bH++) {
                    bI.push(0);
                }
                for (var bD = 0; bD < bJ.length; bD++) {
                    var bF = bJ[bD];
                    bF.top = at(bI.slice(bF.leftCol, bF.rightCol + 1));
                    for (var bH = bF.leftCol; bH <= bF.rightCol; bH++) {
                        bI[bH] = bF.top + bF.outerHeight;
                    }
                }
                bM.push(at(bI));
            }
            return bM;
        }
        function bh(bD) {
            var bH = bB();
            var bG = [];
            var bC;
            var bF;
            var bE;
            for (bC = 0; bC < bD.length; bC++) {
                bF = bD[bC];
                bE = bF.row;
                if (bF.element) {
                    if (bG[bE]) {
                        bG[bE].push(bF);
                    } else {
                        bG[bE] = [bF];
                    }
                }
            }
            for (bE = 0; bE < bH; bE++) {
                bG[bE] = aP(bG[bE] || []);
            }
            return bG;
        }
        function aP(bD) {
            var bF = [];
            var bC = bg(bD);
            for (var bE = 0; bE < bC.length; bE++) {
                bF.push.apply(bF, bC[bE]);
            }
            return bF;
        }
        function bg(bE) {
            bE.sort(aw);
            var bD = [];
            for (var bF = 0; bF < bE.length; bF++) {
                var bG = bE[bF];
                for (var bC = 0; bC < bD.length; bC++) {
                    if (!b(bG, bD[bC])) {
                        break;
                    }
                }
                if (bD[bC]) {
                    bD[bC].push(bG);
                } else {
                    bD[bC] = [bG];
                }
            }
            return bD;
        }
        function bq() {
            var bC;
            var bE = bB();
            var bD = [];
            for (bC = 0; bC < bE; bC++) {
                bD[bC] = a1(bC).find("div.fc-day-content > div");
            }
            return bD;
        }
        function aZ(bD, bC) {
            var bE = aV();
            ae(bD,
            function(bI, bG, bF) {
                var bH = bI.event;
                if (bH._id === bC) {
                    bf(bH, bG, bI);
                } else {
                    bG[0]._fci = bF;
                }
            });
            v(bE, bD, bf);
        }
        function bf(bE, bC, bD) {
            if (aU(bE)) {
                bt.draggableDayEvent(bE, bC, bD);
            }
            if (bD.isEnd && bw(bE)) {
                bt.resizableDayEvent(bE, bC, bD);
            }
            bm(bE, bC);
        }
        function a2(bF, bE) {
            var bD = aT();
            var bC;
            bE.draggable({
                delay: 50,
                opacity: a7("dragOpacity"),
                revertDuration: a7("dragRevertDuration"),
                start: function(bG, bH) {
                    bk("eventDragStart", bE, bF, bG, bH);
                    a6(bF, bE);
                    bD.start(function(bJ, bI, bM, bN) {
                        bE.draggable("option", "revert", !bJ || !bM && !bN);
                        aY();
                        if (bJ) {
                            var bL = aR(bI);
                            var bK = aR(bJ);
                            bC = aB(bK, bL);
                            bl(aE(O(bF.start), bC), aE(ao(bF), bC));
                        } else {
                            bC = 0;
                        }
                    },
                    bG, "drag");
                },
                stop: function(bG, bH) {
                    bD.stop();
                    aY();
                    bk("eventDragStop", bE, bF, bG, bH);
                    if (bC) {
                        bv(this, bF, bC, 0, bF.allDay, bG, bH);
                    } else {
                        bE.css("filter", "");
                        aX(bF, bE);
                    }
                }
            });
        }
        function bn(bF, bD, bE) {
            var bC = a7("isRTL");
            var bH = bC ? "w": "e";
            var bG = bD.find(".ui-resizable-" + bH);
            var bI = false;
            aL(bD);
            bD.mousedown(function(bJ) {
                bJ.preventDefault();
            }).click(function(bJ) {
                if (bI) {
                    bJ.preventDefault();
                    bJ.stopImmediatePropagation();
                }
            });
            bG.mousedown(function(bR) {
                if (bR.which != 1) {
                    return;
                }
                bI = true;
                var bL = aT();
                var bP = bB();
                var bS = bs();
                var bK = bD.css("top");
                var bN;
                var bJ;
                var bQ = au.extend({},
                bF);
                var bM = aQ(bd(bF.start));
                aO();
                au("body").css("cursor", bH + "-resize").one("mouseup", bO);
                bk("eventResizeStart", this, bF, bR);
                bL.start(function(bU, bT) {
                    if (bU) {
                        var bX = aN(bT);
                        var bW = aN(bU);
                        bW = Math.max(bW, bM);
                        bN = bi(bW) - bi(bX);
                        if (bN) {
                            bQ.end = aE(bx(bF), bN, true);
                            var bV = bJ;
                            bJ = be(bQ, bE.row, bK);
                            bJ = au(bJ);
                            bJ.find("*").css("cursor", bH + "-resize");
                            if (bV) {
                                bV.remove();
                            }
                            a6(bF);
                        } else {
                            if (bJ) {
                                aX(bF);
                                bJ.remove();
                                bJ = null;
                            }
                        }
                        aY();
                        bl(bF.start, aE(ao(bF), bN));
                    }
                },
                bR);
                function bO(bT) {
                    bk("eventResizeStop", this, bF, bT);
                    au("body").css("cursor", "");
                    bL.stop();
                    aY();
                    if (bN) {
                        bb(this, bF, bN, 0, bT);
                    }
                    setTimeout(function() {
                        bI = false;
                    },
                    0);
                }
            });
        }
    }
    function b(aN, aP) {
        for (var aM = 0; aM < aP.length; aM++) {
            var aO = aP[aM];
            if (aO.leftCol <= aN.rightCol && aO.rightCol >= aN.leftCol) {
                return true;
            }
        }
        return false;
    }
    function ae(aM, aQ) {
        for (var aO = 0; aO < aM.length; aO++) {
            var aP = aM[aO];
            var aN = aP.element;
            if (aN) {
                aQ(aP, aN, aO);
            }
        }
    }
    function aw(aN, aM) {
        return (aM.rightCol - aM.leftCol) - (aN.rightCol - aN.leftCol) || aM.event.allDay - aN.event.allDay || aN.event.start - aM.event.start || (aN.event.title || "").localeCompare(aM.event.title);
    }
    function an() {
        var aW = this;
        aW.select = aU;
        aW.unselect = aQ;
        aW.reportSelection = aM;
        aW.daySelectionMousedown = aT;
        var aO = aW.opt;
        var aP = aW.trigger;
        var aR = aW.defaultSelectionEnd;
        var aN = aW.renderSelection;
        var aV = aW.clearSelection;
        var aS = false;
        if (aO("selectable") && aO("unselectAuto")) {
            au(document).mousedown(function(aX) {
                var aY = aO("unselectCancel");
                if (aY) {
                    if (au(aX.target).parents(aY).length) {
                        return;
                    }
                }
                aQ(aX);
            });
        }
        function aU(aX, aZ, aY) {
            aQ();
            if (!aZ) {
                aZ = aR(aX, aY);
            }
            aN(aX, aZ, aY);
            aM(aX, aZ, aY);
        }
        function aQ(aX) {
            if (aS) {
                aS = false;
                aV();
                aP("unselect", null, aX);
            }
        }
        function aM(aX, a0, aZ, aY) {
            aS = true;
            aP("select", null, aX, a0, aZ, aY);
        }
        function aT(a1) {
            var aZ = aW.cellToDate;
            var aY = aW.getIsCellAllDay;
            var aX = aW.getHoverListener();
            var a2 = aW.reportDayClick;
            if (a1.which == 1 && aO("selectable")) {
                aQ(a1);
                var a0 = this;
                var a3;
                aX.start(function(a5, a4) {
                    aV();
                    if (a5 && aY(a5)) {
                        a3 = [aZ(a4), aZ(a5)].sort(a);
                        aN(a3[0], a3[1], true);
                    } else {
                        a3 = null;
                    }
                },
                a1);
                au(document).one("mouseup",
                function(a4) {
                    aX.stop();
                    if (a3) {
                        if ( + a3[0] == +a3[1]) {
                            a2(a3[0], true, a4);
                        }
                        aM(a3[0], a3[1], true, a4);
                    }
                });
            }
        }
    }
    function c() {
        var aP = this;
        aP.renderOverlay = aN;
        aP.clearOverlays = aM;
        var aO = [];
        var aQ = [];
        function aN(aS, aR) {
            var aT = aQ.shift();
            if (!aT) {
                aT = au("<div class='fc-cell-overlay' style='position:absolute;z-index:3'/>");
            }
            if (aT[0].parentNode != aR[0]) {
                aT.appendTo(aR);
            }
            aO.push(aT.css(aS).show());
            return aT;
        }
        function aM() {
            var aR;
            while (aR = aO.shift()) {
                aQ.push(aR.hide().unbind());
            }
        }
    }
    function P(aM) {
        var aN = this;
        var aO;
        var aP;
        aN.build = function() {
            aO = [];
            aP = [];
            aM(aO, aP);
        };
        aN.cell = function(aQ, aW) {
            var aV = aO.length;
            var aR = aP.length;
            var aS, aT = -1,
            aU = -1;
            for (aS = 0; aS < aV; aS++) {
                if (aW >= aO[aS][0] && aW < aO[aS][1]) {
                    aT = aS;
                    break;
                }
            }
            for (aS = 0; aS < aR; aS++) {
                if (aQ >= aP[aS][0] && aQ < aP[aS][1]) {
                    aU = aS;
                    break;
                }
            }
            return (aT >= 0 && aU >= 0) ? {
                row: aT,
                col: aU
            }: null;
        };
        aN.rect = function(aT, aV, aR, aS, aU) {
            var aQ = aU.offset();
            return {
                top: aO[aT][0] - aQ.top,
                left: aP[aV][0] - aQ.left,
                width: aP[aS][1] - aP[aV][0],
                height: aO[aR][1] - aO[aT][0]
            };
        };
    }
    function ap(aR) {
        var aP = this;
        var aQ;
        var aS;
        var aN;
        var aM;
        aP.start = function(aT, aU, aV) {
            aS = aT;
            aN = aM = null;
            aR.build();
            aO(aU);
            aQ = aV || "mousemove";
            au(document).bind(aQ, aO);
        };
        function aO(aT) {
            E(aT);
            var aU = aR.cell(aT.pageX, aT.pageY);
            if (!aU != !aM || aU && (aU.row != aM.row || aU.col != aM.col)) {
                if (aU) {
                    if (!aN) {
                        aN = aU;
                    }
                    aS(aU, aN, aU.row - aN.row, aU.col - aN.col);
                } else {
                    aS(aU, aN);
                }
                aM = aU;
            }
        }
        aP.stop = function() {
            au(document).unbind(aQ, aO);
            return aM;
        };
    }
    function E(aM) {
        if (aM.pageX === w) {
            aM.pageX = aM.originalEvent.pageX;
            aM.pageY = aM.originalEvent.pageY;
        }
    }
    function n(aN) {
        var aM = this,
        aO = {},
        aR = {},
        aQ = {};
        function aP(aS) {
            return aO[aS] = aO[aS] || aN(aS);
        }
        aM.left = function(aS) {
            return aR[aS] = aR[aS] === w ? aP(aS).position().left: aR[aS];
        };
        aM.right = function(aS) {
            return aQ[aS] = aQ[aS] === w ? aM.left(aS) + aP(aS).width() : aQ[aS];
        };
        aM.clear = function() {
            aO = {};
            aR = {};
            aQ = {};
        };
    }
    function f(aO) {
        var aQ = new Date(aO);
        aQ.setHours(new Date(aO).getHours(), 0, 0, 0);
        var aS = ((aQ.getMonth() + 1) > 9 ? (aQ.getMonth() + 1) : "0" + (aQ.getMonth() + 1));
        var aN = (aQ.getDate() > 9 ? aQ.getDate() : "0" + aQ.getDate());
        var aP = (aQ.getHours() > 9 ? aQ.getHours() : "0" + aQ.getHours());
        var aR = aQ.getMinutes() > 9 ? aQ.getMinutes() : "0" + aQ.getMinutes();
        var aM = aQ.getFullYear() + "-" + aS + "-" + aN + " " + aP + ":" + aR;
        return aM;
    }
})(jQuery);