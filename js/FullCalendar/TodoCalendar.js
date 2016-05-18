(function(b) {
    function a() {}
    a.prototype = {
        init: function() {
            b("#UnEndOrEnd").val("");
            b("#UnEndOrEnd_UL input[type='radio']:first").attr("checked", true);
            b("#calendar").fullCalendar({
                theme: true,
                header: {
                    left: "today",
                    center: "prev title next",
                    right: "month,basicWeek,agendaDay,sidebar"
                },
                buttonText: {
                    today: "今天",
                    month: "月",
                    week: "周",
                    day: "日",
                    selbtn: "日期选择",
                    sidebar: ""
                },
                titleFormat: {
                    month: "yyyy年MMM",
                    week: "yyyy年M月d日-{yyyy年M月d日}",
                    day: "yyyy年M月d日"
                },
                slotMinutes: 30,
                slotEventOverlap: false,
                defaultView: "month", //month, basicWeek, basicDay
                monthNames: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
                monthNamesShort: ["1月", "2月", "3月", "4月", "5月", "6月", "7月", "8月", "9月", "10月", "11月", "12月"],
                dayNames: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
                dayNamesShort: ["周日", "周一", "周二", "周三", "周四", "周五", "周六"],
                allDayText: "全天",
                allDayDefault: false,
                axisFormat: "HH:mm",
                timeFormat: "HH:mm",
                firstHour: 8,
                eventClick: function(c) {
                    if (c.open) {
                    	window.open(c.url);
                        return false;
                    }
                },
                viewDisplay: function(c) {
                    b("#calendar").fullCalendar("removeEvents");
                    b("#calendar").fullCalendar("refetchEvents");
                }
            });
            b("#calendar").fullCalendar("addEventSource", window.ATUserCalendar.getTask);
            b("#calendar").fullCalendar("addEventSource", window.ATUserCalendar.getMeeting);
            b("#calendar").fullCalendar("addEventSource", window.ATUserCalendar.getProject);
            b("#DataType_UL .c_type_a").bind("click",
            function() {
                if (b(this).hasClass("chk_sel")) {
                    switch (b(this).attr("value")) {
                    case "1":
                        b(this).removeClass("chk_task_sel");
                        b(this).removeClass("chk_sel");
                        b("#calendar").fullCalendar("removeEventSource", window.ATUserCalendar.getTask);
                        break;
                    case "4":
                        b(this).removeClass("chk_met_sel");
                        b(this).removeClass("chk_sel");
                        b("#calendar").fullCalendar("removeEventSource", window.ATUserCalendar.getMeeting);
                        break;
                    case "0":
                        b(this).removeClass("chk_project_sel");
                        b(this).removeClass("chk_sel");
                        b("#calendar").fullCalendar("removeEventSource", window.ATUserCalendar.getProject);
                        break;
                    default:
                        return false;
                    }
                } else {
                    switch (b(this).attr("value")) {
                    case "1":
                        b(this).addClass("chk_task_sel");
                        b(this).addClass("chk_sel");
                        b("#calendar").fullCalendar("addEventSource", window.ATUserCalendar.getTask);
                        break;
                    case "4":
                        b(this).addClass("chk_met_sel");
                        b(this).addClass("chk_sel");
                        b("#calendar").fullCalendar("addEventSource", window.ATUserCalendar.getMeeting);
                        break;
                    case "0":
                        b(this).addClass("chk_project_sel");
                        b(this).addClass("chk_sel");
                        b("#calendar").fullCalendar("addEventSource", window.ATUserCalendar.getProject);
                        break;
                    default:
                        return false;
                    }
                }
                if (!b("#DataType_UL .c_type_a").hasClass("chk_sel")) {
                    DialogT.show(b(this), {
                        sureHide: !1,
                        cancelHide: !1,
                        delayTime: 2000,
                        text: "至少选择一种数据类型。",
                        autoHide: !0,
                        verifyClass: "warm_verify"
                    });
                }
            });
            /*
            b("#UnEndOrEnd_UL .c_status_a").bind("click",
            function() {
                if (!b(this).hasClass("rad_a_sel")) {
                    if (!b("#DataType_UL .c_type_a").hasClass("chk_sel")) {
                        DialogT.show(b(this), {
                            sureHide: !1,
                            cancelHide: !1,
                            delayTime: 2000,
                            text: "至少选择一种数据类型。",
                            autoHide: !0,
                            verifyClass: "warm_verify"
                        });
                        return false;
                    }
                    b("#UnEndOrEnd_UL .c_status_a").removeClass("rad_a_sel");
                    b("#UnEndOrEnd").val(b(this).attr("value"));
                    b(this).addClass("rad_a_sel");
                    b("#calendar").fullCalendar("removeEvents");
                    b("#calendar").fullCalendar("refetchEvents");
                }
            });
            */
            b("#status").bind("change",
            function() {
                if (!b(this).hasClass("rad_a_sel")) {
                    b("#calendar").fullCalendar("removeEvents");
                    b("#calendar").fullCalendar("refetchEvents");
                }
            });
            b("#refresh").bind("click",
                function() {
                    if (!b(this).hasClass("rad_a_sel")) {
                        b("#calendar").fullCalendar("removeEvents");
                        b("#calendar").fullCalendar("refetchEvents");
                    }
                });
            b("span.fc-button-sidebar").attr("title", "隐藏侧边栏");
            b("span.fc-button-sidebar").toggle(function() {
                b(this).css("background-position", "50% -23px");
                b(this).attr("title", "显示侧边栏");
                b("#calendar_right").hide();
                b("#calendar").css("width", "1150px");
                b("#calendar").fullCalendar("updatesize");
            },
            function() {
                b(this).css("background-position", "50% 0px");
                b(this).attr("title", "隐藏侧边栏");
                b("#calendar_right").show();
                b("#calendar").css("width", "100%");
                b("#calendar").fullCalendar("updatesize");
            });
        },
        /*
        getTask: function(e, c, d) {
            window.ATUserCalendar.getAjaxData(e, c, 1, d);
        },
        getMeeting: function(e, c, d) {
            window.ATUserCalendar.getAjaxData(e, c, 4, d);
        },
        */
        getProject: function(e, c, d) {
            window.ATUserCalendar.getAjaxData(e, c, 0, d);
        },
        getAjaxData: function(j, d, g, i) {
            var h = b.fullCalendar.formatDate(j, "yyyy-MM-dd HH:mm:ss");
            var f = b.fullCalendar.formatDate(d, "yyyy-MM-dd HH:mm:ss");
            var c = b("#calendar").fullCalendar("getView");
            var e = [];
            b.ajax({
                type: "post",
                dataType: "json",
                data: {
                    "action": "gettodolist_calendar",
                    "teamID": b("#tempteamid").val(),
                    "dataType": g,
                    "status": b("#status").val(),
                    "user_id": b("#user_id").val(),
                    "starttime": h,
                    "endtime": f
                },
                url: "/sscrm/index.php?c=schedule&a=getplan",
                success: function(m) {
                    if (m.issuccess == "true" && m.hint.length > 0) {
                        var l = "";
                        for (var k = 0; k < m.hint.length; k++) {
                            l = "";
                            if (g == 1 && m.hint[k].repeattype > 0) {
                                switch (m.hint[k].repeattype) {
                                case 1:
                                    l = "(每天 重复)";
                                    break;
                                case 2:
                                    l = "(每周 重复)";
                                    break;
                                case 3:
                                    l = "(每两周 重复)";
                                    break;
                                case 4:
                                    l = "(每月 重复)";
                                    break;
                                }
                            }
                            e.push({
                                title: m.hint[k].title + l + "  负责人：" + m.hint[k].receiveUser + (g == 1 ? "  完成度：" + m.hint[k].progress + "%": ""),
                                start: new Date(m.hint[k].start.replace(/-/g, "/")),
                                end: new Date(m.hint[k].end.replace(/-/g, "/")),
                                url: m.hint[k].url,
                                className: m.hint[k].className,
                                allDay: (c.name == "agendaDay" ? (m.hint[k].allDay == "True" ? true: false) : false)
                            });
                        }
                    }
                    i(e);
                },
                error: function() {
                    return;
                }
            });
        }
    };
    window.ATUserCalendar = new a;
})(jQuery);