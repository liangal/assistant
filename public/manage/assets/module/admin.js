/** EasyWeb spa v3.1.4 date:2019-08-05 License By http://easyweb.vip */

layui.define(["layer", "config", "layRouter"], function (g) {
    var i = layui.jquery;
    var k = layui.layer;
    var c = layui.config;
    var o = layui.layRouter;
    var a = ".layui-layout-admin>.layui-body";
    var l = a + ">.layui-tab";
    var f = ".layui-layout-admin>.layui-side>.layui-side-scroll";
    var j = ".layui-layout-admin>.layui-header";
    var b = "admin-pagetabs";
    var e = "admin-side-nav";
    var d = "theme-admin";
    var n = {
        flexible: function (p) {
            var q = i(".layui-layout-admin").hasClass("admin-nav-mini");
            (p == undefined) && (p = q);
            if (q == p) {
                if (p) {
                    n.hideTableScrollBar();
                    i(".layui-layout-admin").removeClass("admin-nav-mini")
                } else {
                    i(".layui-layout-admin").addClass("admin-nav-mini")
                }
            }
            n.resizeTable(360)
        }, activeNav: function (q) {
            if (!q) {
                q = location.hash
            }
            if (q && q != "") {
                i(f + ">.layui-nav .layui-nav-item .layui-nav-child dd.layui-this").removeClass("layui-this");
                i(f + ">.layui-nav .layui-nav-item.layui-this").removeClass("layui-this");
                var t = i(f + '>.layui-nav a[href="#' + q + '"]');
                if (t && t.length > 0) {
                    var s = i(".layui-layout-admin").hasClass("admin-nav-mini");
                    if (i(f + ">.layui-nav").attr("lay-accordion") == "true") {
                        var p = t.parent("dd").parents(".layui-nav-child");
                        if (s) {
                            i(f + ">.layui-nav .layui-nav-itemed>.layui-nav-child").not(p).css("display", "none")
                        } else {
                            i(f + ">.layui-nav .layui-nav-itemed>.layui-nav-child").not(p).slideUp("fast")
                        }
                        i(f + ">.layui-nav .layui-nav-itemed").not(p.parent()).removeClass("layui-nav-itemed")
                    }
                    t.parent().addClass("layui-this");
                    var u = t.parent("dd").parents(".layui-nav-child").parent();
                    if (s) {
                        u.not(".layui-nav-itemed").children(".layui-nav-child").css("display", "block")
                    } else {
                        u.not(".layui-nav-itemed").children(".layui-nav-child").slideDown("fast", function () {
                            var v = t.offset().top + t.outerHeight() + 30 - n.getPageHeight();
                            var w = 50 + 65 - t.offset().top;
                            if (v > 0) {
                                i(f).animate({"scrollTop": i(f).scrollTop() + v}, 100)
                            } else {
                                if (w > 0) {
                                    i(f).animate({"scrollTop": i(f).scrollTop() - w}, 100)
                                }
                            }
                        })
                    }
                    u.addClass("layui-nav-itemed");
                    i('ul[lay-filter="' + e + '"]').addClass("layui-hide");
                    var r = t.parents(".layui-nav");
                    r.removeClass("layui-hide");
                    i(j + ">.layui-nav>.layui-nav-item").removeClass("layui-this");
                    i(j + '>.layui-nav>.layui-nav-item>a[nav-bind="' + r.attr("nav-id") + '"]').parent().addClass("layui-this")
                } else {
                }
            } else {
                console.warn("active url is null")
            }
        }, popupRight: function (p) {
            if (p.title == undefined) {
                p.title = false;
                p.closeBtn = false
            }
            if (p.fixed == undefined) {
                p.fixed = true
            }
            p.anim = -1;
            p.offset = "r";
            p.shadeClose = true;
            p.area || (p.area = "336px");
            p.skin || (p.skin = "layui-anim layui-anim-rl layui-layer-adminRight");
            p.move = false;
            return n.open(p)
        }, open: function (r) {
            if (!r.area) {
                r.area = (r.type == 2) ? ["360px", "300px"] : "360px"
            }
            if (!r.skin) {
                r.skin = "layui-layer-admin"
            }
            if (!r.offset) {
                if (n.getPageWidth() < 768) {
                    r.offset = "15px"
                } else {
                    r.offset = "70px"
                }
            }
            if (r.fixed == undefined) {
                r.fixed = false
            }
            r.resize = r.resize != undefined ? r.resize : false;
            r.shade = r.shade != undefined ? r.shade : 0.1;
            var p = r.end;
            r.end = function () {
                k.closeAll("tips");
                p && p()
            };
            if (r.url) {
                (r.type == undefined) && (r.type = 1);
                var q = r.success;
                r.success = function (s, t) {
                    var u = r.url;
                    if (c.version != undefined) {
                        if (u.indexOf("?") == -1) {
                            u += "?v="
                        } else {
                            u += "&v="
                        }
                        if (c.version == true) {
                            u += new Date().getTime()
                        } else {
                            u += c.version
                        }
                    }
                    n.showLoading(s, 1);
                    i(s).children(".layui-layer-content").load(u, function () {
                        q ? q(s, t) : "";
                        n.removeLoading(s, false)
                    })
                }
            }
            return k.open(r)
        }, req: function (p, q, r, s) {
            if (c.reqPutToPost != false) {
                if ("put" == s.toLowerCase()) {
                    s = "POST";
                    q._method = "PUT"
                } else {
                    if ("delete" == s.toLowerCase()) {
                        s = "POST";
                        q._method = "DELETE"
                    }
                }
            }
            n.ajax({url: c.base_server + p, data: q, type: s, dataType: "json", success: r})
        },reqAsync: function (p, q, r, s) {
            if (c.reqPutToPost != false) {
                if ("put" == s.toLowerCase()) {
                    s = "POST";
                    q._method = "PUT"
                } else {
                    if ("delete" == s.toLowerCase()) {
                        s = "POST";
                        q._method = "DELETE"
                    }
                }
            }
            n.ajax({url: c.base_server + p, data: q, type: s,async: false, dataType: "json", success: r})
        }, ajax: function (r) {
            var q = r.header;
            r.dataType || (r.dataType = "json");
            var p = r.success;
            r.success = function (s, t, v) {
                var u;
                if ("json" == r.dataType.toLowerCase()) {
                    u = s
                } else {
                    u = n.parseJSON(s)
                }
                u && (u = s);
                if (c.ajaxSuccessBefore(u, r.url) == false) {
                    return
                }
                p(s, t, v)
            };
            r.error = function (s) {
                r.success({code: s.status, msg: s.statusText})
            };
            r.beforeSend = function (v) {
                var u = c.getAjaxHeaders(r.url);
                for (var s = 0; s < u.length; s++) {
                    v.setRequestHeader(u[s].name, u[s].value)
                }
                for (var t in q) {
                    v.setRequestHeader(t, q[t])
                }
            };
            i.ajax(r)
        }, parseJSON: function (r) {
            if (typeof r == "string") {
                try {
                    var q = JSON.parse(r);
                    if (typeof q == "object" && q) {
                        return q
                    }
                } catch (p) {
                }
            }
        }, showLoading: function (t, s, q) {
            var r;
            if (t != undefined && (typeof t != "string") && !(t instanceof i)) {
                s = t.type;
                q = t.opacity;
                r = t.size;
                t = t.elem
            }
            (!t) && (t = "body");
            (s == undefined) && (s = 1);
            (r == undefined) && (r = "sm");
            r = " " + r;
            var p = ['<div class="ball-loader' + r + '"><span></span><span></span><span></span><span></span></div>', '<div class="rubik-loader' + r + '"></div>', '<div class="signal-loader' + r + '"><span></span><span></span><span></span><span></span></div>'];
            i(t).addClass("page-no-scroll");
            var u = i(t).children(".page-loading");
            if (u.length <= 0) {
                i(t).append('<div class="page-loading">' + p[s - 1] + "</div>");
                u = i(t).children(".page-loading")
            }
            q && u.css("background-color", "rgba(255,255,255," + q + ")");
            u.show()
        }, removeLoading: function (q, s, p) {
            if (!q) {
                q = "body"
            }
            if (s == undefined) {
                s = true
            }
            var r = i(q).children(".page-loading");
            if (p) {
                r.remove()
            } else {
                s ? r.fadeOut() : r.hide()
            }
            i(q).removeClass("page-no-scroll")
        }, putTempData: function (q, r) {
            var p = c.tableName + "_tempData";
            if (r != undefined && r != null) {
                layui.sessionData(p, {key: q, value: r})
            } else {
                layui.sessionData(p, {key: q, remove: true})
            }
        }, getTempData: function (q) {
            var p = c.tableName + "_tempData";
            var r = layui.sessionData(p);
            if (r) {
                return r[q]
            } else {
                return false
            }
        }, rollPage: function (s) {
            var q = i(l + ">.layui-tab-title");
            var r = q.scrollLeft();
            if ("left" === s) {
                q.animate({"scrollLeft": r - 120}, 100)
            } else {
                if ("auto" === s) {
                    var p = 0;
                    q.children("li").each(function () {
                        if (i(this).hasClass("layui-this")) {
                            return false
                        } else {
                            p += i(this).outerWidth()
                        }
                    });
                    q.animate({"scrollLeft": p - 120}, 100)
                } else {
                    q.animate({"scrollLeft": r + 120}, 100)
                }
            }
        }, refresh: function (p) {
            o.refresh(p)
        }, closeThisTabs: function (p) {
            n.closeTabOperNav();
            var q = i(l + ">.layui-tab-title");
            if (!p) {
                if (q.find("li").first().hasClass("layui-this")) {
                    k.msg("主页不能关闭", {icon: 2});
                    return
                }
                q.find("li.layui-this").find(".layui-tab-close").trigger("click")
            } else {
                if (p == q.find("li").first().attr("lay-id")) {
                    k.msg("主页不能关闭", {icon: 2});
                    return
                }
                q.find('li[lay-id="' + p + '"]').find(".layui-tab-close").trigger("click")
            }
        }, closeOtherTabs: function (p) {
            if (!p) {
                i(l + ">.layui-tab-title li:gt(0):not(.layui-this)").find(".layui-tab-close").trigger("click")
            } else {
                i(l + ">.layui-tab-title li:gt(0)").each(function () {
                    if (p != i(this).attr("lay-id")) {
                        i(this).find(".layui-tab-close").trigger("click")
                    }
                })
            }
            n.closeTabOperNav()
        }, closeAllTabs: function () {
            i(l + ">.layui-tab-title li:gt(0)").find(".layui-tab-close").trigger("click");
            i(l + ">.layui-tab-title li:eq(0)").trigger("click");
            n.closeTabOperNav()
        }, closeTabOperNav: function () {
            i(".layui-icon-down .layui-nav .layui-nav-child").removeClass("layui-show")
        }, changeTheme: function (v) {
            if (v) {
                layui.data(c.tableName, {key: "theme", value: v});
                if (d == v) {
                    v = undefined
                }
            } else {
                layui.data(c.tableName, {key: "theme", remove: true})
            }
            try {
                n.removeTheme(top);
                (v && top.layui) && top.layui.link(n.getThemeDir() + v + n.getCssSuffix(), v);
                var w = top.window.frames;
                for (var r = 0; r < w.length; r++) {
                    try {
                        var t = w[r];
                        n.removeTheme(t);
                        if (v && t.layui) {
                            t.layui.link(n.getThemeDir() + v + n.getCssSuffix(), v)
                        }
                        var s = t.frames;
                        for (var q = 0; q < s.length; q++) {
                            try {
                                var p = s[q];
                                n.removeTheme(p);
                                if (v && p.layui) {
                                    p.layui.link(n.getThemeDir() + v + n.getCssSuffix(), v)
                                }
                            } catch (u) {
                            }
                        }
                    } catch (u) {
                    }
                }
            } catch (u) {
            }
        }, removeTheme: function (p) {
            if (!p) {
                p = window
            }
            if (p.layui) {
                var q = "layuicss-theme";
                p.layui.jquery('link[id^="' + q + '"]').remove()
            }
        }, getThemeDir: function () {
            return layui.cache.base + "theme/"
        }, closeThisDialog: function () {
            parent.layer.close(parent.layer.getFrameIndex(window.name))
        }, closeDialog: function (p) {
            var q = i(p).parents(".layui-layer").attr("id").substring(11);
            k.close(q)
        }, iframeAuto: function () {
            parent.layer.iframeAuto(parent.layer.getFrameIndex(window.name))
        }, getPageHeight: function () {
            return document.documentElement.clientHeight || document.body.clientHeight
        }, getPageWidth: function () {
            return document.documentElement.clientWidth || document.body.clientWidth
        }, getCssSuffix: function () {
            var p = ".css";
            if (c.version != undefined) {
                p += "?v=";
                if (c.version == true) {
                    p += new Date().getTime()
                } else {
                    p += c.version
                }
            }
            return p
        }, hideTableScrollBar: function (q) {
            if (n.getPageWidth() > 768) {
                if (window.hsbTimer) {
                    clearTimeout(hsbTimer)
                }
                var p = c.pageTabs ? i(l + ">.layui-tab-content>.layui-tab-item.layui-show") : i(a);
                p.find(".layui-table-body.layui-table-main").addClass("no-scrollbar");
                window.hsbTimer = setTimeout(function () {
                    p.find(".layui-table-body.layui-table-main").removeClass("no-scrollbar")
                }, q == undefined ? 500 : q)
            }
        }, modelForm: function (q, t, p) {
            var s = i(q);
            s.addClass("layui-form");
            if (p) {
                s.attr("lay-filter", p)
            }
            var r = s.find(".layui-layer-btn .layui-layer-btn0");
            r.attr("lay-submit", "");
            r.attr("lay-filter", t)
        }, btnLoading: function (q, r, s) {
            if (r != undefined && (typeof r == "boolean")) {
                s = r;
                r = undefined
            }
            (s == undefined) && (s = true);
            var p = i(q);
            if (s) {
                r && p.html(r);
                p.find(".layui-icon").addClass("layui-hide");
                p.addClass("icon-btn");
                p.prepend('<i class="layui-icon layui-icon-loading layui-anim layui-anim-rotate layui-anim-loop ew-btn-loading"></i>');
                p.prop("disabled", "disabled")
            } else {
                p.find(".ew-btn-loading").remove();
                p.removeProp("disabled", "disabled");
                if (p.find(".layui-icon.layui-hide").length <= 0) {
                    p.removeClass("icon-btn")
                }
                p.find(".layui-icon").removeClass("layui-hide");
                r && p.html(r)
            }
        }, openSideAutoExpand: function () {
            i(".layui-layout-admin>.layui-side").off("mouseenter.openSideAutoExpand").on("mouseenter.openSideAutoExpand", function () {
                if (i(this).parent().hasClass("admin-nav-mini")) {
                    n.flexible(true);
                    i(this).addClass("side-mini-hover")
                }
            });
            i(".layui-layout-admin>.layui-side").off("mouseleave.openSideAutoExpand").on("mouseleave.openSideAutoExpand", function () {
                if (i(this).hasClass("side-mini-hover")) {
                    n.flexible(false);
                    i(this).removeClass("side-mini-hover")
                }
            })
        }, openCellAutoExpand: function () {
            i("body").off("mouseenter.openCellAutoExpand").on("mouseenter.openCellAutoExpand", ".layui-table-view td", function () {
                i(this).find(".layui-table-grid-down").trigger("click")
            });
            i("body").off("mouseleave.openCellAutoExpand").on("mouseleave.openCellAutoExpand", ".layui-table-tips>.layui-layer-content", function () {
                i(".layui-table-tips-c").trigger("click")
            })
        }, isTop: function () {
            return i(a).length > 0
        }, strToWin: function (s) {
            var r = window;
            if (s) {
                var p = s.split(".");
                for (var q = 0; q < p.length; q++) {
                    r = r[p[q]]
                }
            }
            return r
        }, hasPerm: function (s) {
            var q = c.getUserAuths();
            if (q) {
                for (var p = 0;
                     p < q.length; p++) {
                    if (s == q[p]) {
                        return true
                    }
                }
            }
            return false
        }, renderPerm: function () {
            i("[perm-show]").each(function (p, r) {
                var q = i(this).attr("perm-show");
                if (!n.hasPerm(q)) {
                    i(this).remove()
                }
            })
        }, resizeTable: function (p) {
            setTimeout(function () {
                var q = c.pageTabs ? i(l + ">.layui-tab-content>.layui-tab-item.layui-show") : i(a);
                q.find(".layui-table-view").each(function () {
                    var r = i(this).attr("lay-id");
                    layui.table && layui.table.resize(r)
                })
            }, p == undefined ? 0 : p)
        }, parseLayerOption: function (r) {
            for (var s in r) {
                if (r[s] && r[s].toString().indexOf(",") != -1) {
                    r[s] = r[s].toString().split(",")
                }
            }
            var p = ["success", "cancel", "end", "full", "min", "restore"];
            for (var q = 0; q < p.length; q++) {
                for (var s in r) {
                    if (s == p[q]) {
                        r[s] = window[r[s]]
                    }
                }
            }
            if (r.content && (typeof r.content === "string") && r.content.indexOf("#") == 0) {
                r.content = i(r.content).html()
            }
            (r.type == undefined) && (r.type = 2);
            return r
        }
    };
    n.events = {
        flexible: function (p) {
            n.flexible()
        }, refresh: function () {
            n.refresh()
        }, back: function () {
            history.back()
        }, theme: function () {
            var p = i(this).data("url");
            n.popupRight({id: "layer-theme", url: p ? p : "components/tpl/theme.html"})
        }, note: function () {
            var p = i(this).data("url");
            n.popupRight({id: "layer-note", url: p ? p : "components/tpl/note.html"})
        }, message: function () {
            var p = i(this).data("url");
            n.popupRight({id: "layer-notice", url: p ? p : "components/tpl/message.html"})
        }, userinfo: function () {
            var p = i(this).data("url");
            n.open({id: "userForm",title: "个人中心", area: ['600px', '380px'], shade: 0, url: p})
        }, psw: function () {
            var p = i(this).data("url");
            n.open({id: "pswForm", title: "修改密码", shade: 0, url: p ? p : "components/tpl/password.html"})
        }, logout: function () {
            var p = i(this).data("url");
            k.confirm("确定要退出登录吗？", {title: "温馨提示", skin: "layui-layer-admin"}, function () {
                c.removeToken();
                p ? location.replace(p) : location.reload()
            })
        }, open: function () {
            var p = i(this).data();
            n.open(n.parseLayerOption(n.util.deepClone(p)))
        }, popupRight: function () {
            var p = i(this).data();
            n.popupRight(n.parseLayerOption(n.util.deepClone(p)))
        }, fullScreen: function () {
            var w = "layui-icon-screen-full", p = "layui-icon-screen-restore";
            var t = i(this).find("i");
            var s = document.fullscreenElement || document.msFullscreenElement || document.mozFullScreenElement || document.webkitFullscreenElement || false;
            if (s) {
                var r = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
                if (r) {
                    r.call(document)
                } else {
                    if (window.ActiveXObject) {
                        var q = new ActiveXObject("WScript.Shell");
                        q && q.SendKeys("{F11}")
                    }
                }
                t.addClass(w).removeClass(p)
            } else {
                var u = document.documentElement;
                var v = u.requestFullscreen || u.webkitRequestFullscreen || u.mozRequestFullScreen || u.msRequestFullscreen;
                if (v) {
                    v.call(u)
                } else {
                    if (window.ActiveXObject) {
                        var q = new ActiveXObject("WScript.Shell");
                        q && q.SendKeys("{F11}")
                    }
                }
                t.addClass(p).removeClass(w)
            }
        }, leftPage: function () {
            n.rollPage("left")
        }, rightPage: function () {
            n.rollPage()
        }, closeThisTabs: function () {
            var p = i(this).data("url");
            n.closeThisTabs(p)
        }, closeOtherTabs: function () {
            n.closeOtherTabs()
        }, closeAllTabs: function () {
            n.closeAllTabs()
        }, closeDialog: function () {
            n.closeDialog(this)
        }, closeIframeDialog: function () {
            n.closeThisDialog()
        }
    };
    n.chooseLocation = function (u) {
        var q = u.title;
        var y = u.onSelect;
        var s = u.needCity;
        var z = u.center;
        var C = u.defaultZoom;
        var v = u.pointZoom;
        var x = u.keywords;
        var B = u.pageSize;
        var t = u.mapJsUrl;
        (q == undefined) && (q = "选择位置");
        (C == undefined) && (C = 11);
        (v == undefined) && (v = 17);
        (x == undefined) && (x = "");
        (B == undefined) && (B = 30);
        (t == undefined) && (t = "https://webapi.amap.com/maps?v=1.4.14&key=006d995d433058322319fa797f2876f5");
        var D = false, A;
        var w = function (F, E) {
            AMap.service(["AMap.PlaceSearch"], function () {
                var H = new AMap.PlaceSearch({type: "", pageSize: B, pageIndex: 1});
                var G = [E, F];
                H.searchNearBy(x, G, 1000, function (J, I) {
                    if (J == "complete") {
                        var M = I.poiList.pois;
                        var N = "";
                        for (var L = 0; L < M.length; L++) {
                            var K = M[L];
                            if (K.location != undefined) {
                                N += '<div data-lng="' + K.location.lng + '" data-lat="' + K.location.lat + '" class="ew-map-select-search-list-item">';
                                N += '     <div class="ew-map-select-search-list-item-title">' + K.name + "</div>";
                                N += '     <div class="ew-map-select-search-list-item-address">' + K.address + "</div>";
                                N += '     <div class="ew-map-select-search-list-item-icon-ok layui-hide"><i class="layui-icon layui-icon-ok-circle"></i></div>';
                                N += "</div>"
                            }
                        }
                        i("#ew-map-select-pois").html(N)
                    }
                })
            })
        };
        var p = function () {
            var E = {resizeEnable: true, zoom: C};
            z && (E.center = z);
            var F = new AMap.Map("ew-map-select-map", E);
            F.on("complete", function () {
                var G = F.getCenter();
                w(G.lat, G.lng)
            });
            F.on("moveend", function () {
                if (D) {
                    D = false
                } else {
                    i("#ew-map-select-tips").addClass("layui-hide");
                    i("#ew-map-select-center-img").removeClass("bounceInDown");
                    setTimeout(function () {
                        i("#ew-map-select-center-img").addClass("bounceInDown")
                    });
                    var G = F.getCenter();
                    w(G.lat, G.lng)
                }
            });
            i("#ew-map-select-pois").off("click").on("click", ".ew-map-select-search-list-item", function () {
                i("#ew-map-select-tips").addClass("layui-hide");
                i("#ew-map-select-pois .ew-map-select-search-list-item-icon-ok").addClass("layui-hide");
                i(this).find(".ew-map-select-search-list-item-icon-ok").removeClass("layui-hide");
                i("#ew-map-select-center-img").removeClass("bounceInDown");
                setTimeout(function () {
                    i("#ew-map-select-center-img").addClass("bounceInDown")
                });
                var I = i(this).data("lng");
                var J = i(this).data("lat");
                var H = i(this).find(".ew-map-select-search-list-item-title").text();
                var G = i(this).find(".ew-map-select-search-list-item-address").text();
                A = {name: H, address: G, lat: J, lng: I};
                D = true;
                F.setZoomAndCenter(v, [I, J])
            });
            i("#ew-map-select-btn-ok").click(function () {
                if (A == undefined) {
                    k.msg("请点击位置列表选择", {icon: 2, anim: 6})
                } else {
                    if (y) {
                        if (s) {
                            var G = k.load(2);
                            F.setCenter([A.lng, A.lat]);
                            F.getCity(function (H) {
                                k.close(G);
                                A.city = H;
                                n.closeDialog("#ew-map-select-btn-ok");
                                y(A)
                            })
                        } else {
                            n.closeDialog("#ew-map-select-btn-ok");
                            y(A)
                        }
                    } else {
                        n.closeDialog("#ew-map-select-btn-ok")
                    }
                }
            });
            i("#ew-map-select-input-search").off("input").on("input", function () {
                var G = i(this).val();
                if (!G) {
                    i("#ew-map-select-tips").html("");
                    i("#ew-map-select-tips").addClass("layui-hide")
                }
                AMap.plugin("AMap.Autocomplete", function () {
                    var H = new AMap.Autocomplete({city: "全国"});
                    H.search(G, function (K, J) {
                        if (J.tips) {
                            var I = J.tips;
                            var M = "";
                            for (var L = 0; L < I.length; L++) {
                                var N = I[L];
                                if (N.location != undefined) {
                                    M += '<div data-lng="' + N.location.lng + '" data-lat="' + N.location.lat + '" class="ew-map-select-search-list-item">';
                                    M += '     <div class="ew-map-select-search-list-item-icon-search"><i class="layui-icon layui-icon-search"></i></div>';
                                    M += '     <div class="ew-map-select-search-list-item-title">' + N.name + "</div>";
                                    M += '     <div class="ew-map-select-search-list-item-address">' + N.address + "</div>";
                                    M += "</div>"
                                }
                            }
                            i("#ew-map-select-tips").html(M);
                            if (I.length == 0) {
                                i("#ew-map-select-tips").addClass("layui-hide")
                            } else {
                                i("#ew-map-select-tips").removeClass("layui-hide")
                            }
                        } else {
                            i("#ew-map-select-tips").html("");
                            i("#ew-map-select-tips").addClass("layui-hide")
                        }
                    })
                })
            });
            i("#ew-map-select-input-search").off("blur").on("blur", function () {
                var G = i(this).val();
                if (!G) {
                    i("#ew-map-select-tips").html("");
                    i("#ew-map-select-tips").addClass("layui-hide")
                }
            });
            i("#ew-map-select-input-search").off("focus").on("focus", function () {
                var G = i(this).val();
                if (G) {
                    i("#ew-map-select-tips").removeClass("layui-hide")
                }
            });
            i("#ew-map-select-tips").off("click").on("click", ".ew-map-select-search-list-item", function () {
                i("#ew-map-select-tips").addClass("layui-hide");
                var G = i(this).data("lng");
                var H = i(this).data("lat");
                A = undefined;
                F.setZoomAndCenter(v, [G, H])
            })
        };
        var r = '<div class="ew-map-select-tool" style="position: relative;">';
        r += '        搜索：<input id="ew-map-select-input-search" class="layui-input icon-search inline-block" style="width: 190px;" placeholder="输入关键字搜索" autocomplete="off" />';
        r += '        <button id="ew-map-select-btn-ok" class="layui-btn icon-btn pull-right" type="button"><i class="layui-icon">&#xe605;</i>确定</button>';
        r += '        <div id="ew-map-select-tips" class="ew-map-select-search-list layui-hide">';
        r += "        </div>";
        r += "   </div>";
        r += '   <div class="layui-row ew-map-select">';
        r += '        <div class="layui-col-sm7 ew-map-select-map-group" style="position: relative;">';
        r += '             <div id="ew-map-select-map"></div>';
        r += '             <i id="ew-map-select-center-img2" class="layui-icon layui-icon-add-1"></i>';
        r += '             <img id="ew-map-select-center-img" src="https://3gimg.qq.com/lightmap/components/locationPicker2/image/marker.png"/>';
        r += "        </div>";
        r += '        <div id="ew-map-select-pois" class="layui-col-sm5 ew-map-select-search-list">';
        r += "        </div>";
        r += "   </div>";
        n.open({
            id: "ew-map-select", type: 1, title: q, area: "750px", content: r, success: function (E, G) {
                var F = i(E).children(".layui-layer-content");
                F.css("overflow", "visible");
                n.showLoading(F);
                if (undefined == window.AMap) {
                    i.getScript(t, function () {
                        p();
                        n.removeLoading(F)
                    })
                } else {
                    p();
                    n.removeLoading(F)
                }
            }
        })
    };
    n.cropImg = function (s) {
        var q = "image/jpeg";
        var x = s.aspectRatio;
        var y = s.imgSrc;
        var v = s.imgType;
        var t = s.onCrop;
        var u = s.limitSize;
        var w = s.acceptMime;
        var r = s.exts;
        var p = s.title;
        (x == undefined) && (x = 1 / 1);
        (p == undefined) && (p = "裁剪图片");
        v && (q = v);
        layui.use(["Cropper", "upload"], function () {
            var A = layui.Cropper;
            var z = layui.upload;

            function B() {
                var E, F = i("#ew-crop-img");
                var G = {
                    elem: "#ew-crop-img-upload", auto: false, drag: false, choose: function (H) {
                        H.preview(function (J, K, I) {
                            q = K.type;
                            F.attr("src", I);
                            if (!y || !E) {
                                y = I;
                                B()
                            } else {
                                E.destroy();
                                E = new A(F[0], D)
                            }
                        })
                    }
                };
                (u != undefined) && (G.size = u);
                (w != undefined) && (G.acceptMime = w);
                (r != undefined) && (G.exts = r);
                z.render(G);
                if (!y) {
                    i("#ew-crop-img-upload").trigger("click");
                    return
                }
                var D = {aspectRatio: x, preview: "#ew-crop-img-preview"};
                E = new A(F[0], D);
                i(".ew-crop-tool").on("click", "[data-method]", function () {
                    var I = i(this).data(), J, H;
                    if (!E || !I.method) {
                        return
                    }
                    I = i.extend({}, I);
                    J = E.cropped;
                    switch (I.method) {
                        case"rotate":
                            if (J && D.viewMode > 0) {
                                E.clear()
                            }
                            break;
                        case"getCroppedCanvas":
                            if (q === "image/jpeg") {
                                if (!I.option) {
                                    I.option = {}
                                }
                                I.option.fillColor = "#fff"
                            }
                            break
                    }
                    H = E[I.method](I.option, I.secondOption);
                    switch (I.method) {
                        case"rotate":
                            if (J && D.viewMode > 0) {
                                E.crop()
                            }
                            break;
                        case"scaleX":
                        case"scaleY":
                            i(this).data("option", -I.option);
                            break;
                        case"getCroppedCanvas":
                            if (H) {
                                t && t(H.toDataURL(q));
                                n.closeDialog("#ew-crop-img")
                            } else {
                                k.msg("裁剪失败", {icon: 2, anim: 6})
                            }
                            break
                    }
                })
            }

            var C = '<div class="layui-row">';
            C += '        <div class="layui-col-sm8" style="min-height: 9rem;">';
            C += '             <img id="ew-crop-img" src="' + (y ? y : "") + '" style="max-width:100%;" />';
            C += "        </div>";
            C += '        <div class="layui-col-sm4 layui-hide-xs" style="padding: 0 20px;text-align: center;">';
            C += '             <div id="ew-crop-img-preview" style="width: 100%;height: 9rem;overflow: hidden;display: inline-block;border: 1px solid #dddddd;"></div>';
            C += "        </div>";
            C += "   </div>";
            C += '   <div class="text-center ew-crop-tool" style="padding: 15px 10px 5px 0;">';
            C += '        <div class="layui-btn-group" style="margin-bottom: 10px;margin-left: 10px;">';
            C += '             <button title="放大" data-method="zoom" data-option="0.1" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-add-1"></i></button>';
            C += '             <button title="缩小" data-method="zoom" data-option="-0.1" class="layui-btn icon-btn" type="button"><span style="display: inline-block;width: 12px;height: 2.5px;background: rgba(255, 255, 255, 0.9);vertical-align: middle;margin: 0 4px;"></span></button>';
            C += "        </div>";
            C += '        <div class="layui-btn-group layui-hide-xs" style="margin-bottom: 10px;">';
            C += '             <button title="向左旋转" data-method="rotate" data-option="-45" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-refresh-1" style="transform: rotateY(180deg) rotate(40deg);display: inline-block;"></i></button>';
            C += '             <button title="向右旋转" data-method="rotate" data-option="45" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-refresh-1" style="transform: rotate(30deg);display: inline-block;"></i></button>';
            C += "        </div>";
            C += '        <div class="layui-btn-group" style="margin-bottom: 10px;">';
            C += '             <button title="左移" data-method="move" data-option="-10" data-second-option="0" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-left"></i></button>';
            C += '             <button title="右移" data-method="move" data-option="10" data-second-option="0" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-right"></i></button>';
            C += '             <button title="上移" data-method="move" data-option="0" data-second-option="-10" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-up"></i></button>';
            C += '             <button title="下移" data-method="move" data-option="0" data-second-option="10" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-down"></i></button>';
            C += "        </div>";
            C += '        <div class="layui-btn-group" style="margin-bottom: 10px;">';
            C += '             <button title="左右翻转" data-method="scaleX" data-option="-1" class="layui-btn icon-btn" type="button" style="position: relative;width: 41px;"><i class="layui-icon layui-icon-triangle-r" style="position: absolute;left: 9px;top: 0;transform: rotateY(180deg);font-size: 16px;"></i><i class="layui-icon layui-icon-triangle-r" style="position: absolute; right: 3px; top: 0;font-size: 16px;"></i></button>';
            C += '             <button title="上下翻转" data-method="scaleY" data-option="-1" class="layui-btn icon-btn" type="button" style="position: relative;width: 41px;"><i class="layui-icon layui-icon-triangle-d" style="position: absolute;left: 11px;top: 6px;transform: rotateX(180deg);line-height: normal;font-size: 16px;"></i><i class="layui-icon layui-icon-triangle-d" style="position: absolute; left: 11px; top: 14px;line-height: normal;font-size: 16px;"></i></button>';
            C += "        </div>";
            C += '        <div class="layui-btn-group" style="margin-bottom: 10px;">';
            C += '             <button title="重新开始" data-method="reset" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-refresh"></i></button>';
            C += '             <button title="选择图片" id="ew-crop-img-upload" class="layui-btn icon-btn" type="button"><i class="layui-icon layui-icon-upload-drag"></i></button>';
            C += "        </div>";
            C += '        <button data-method="getCroppedCanvas" data-option="{ &quot;maxWidth&quot;: 4096, &quot;maxHeight&quot;: 4096 }" class="layui-btn icon-btn" type="button" style="margin-left: 10px;margin-bottom: 10px;"><i class="layui-icon">&#xe605;</i>完成</button>';
            C += "   </div>";
            n.open({
                title: p, area: "665px", type: 1, content: C, success: function (D, E) {
                    i(D).children(".layui-layer-content").css("overflow", "visible");
                    B()
                }
            })
        })
    };
    n.util = {
        Convert_BD09_To_GCJ02: function (q) {
            var s = (3.141592653589793 * 3000) / 180;
            var p = q.lng - 0.0065, u = q.lat - 0.006;
            var t = Math.sqrt(p * p + u * u) - 0.00002 * Math.sin(u * s);
            var r = Math.atan2(u, p) - 0.000003 * Math.cos(p * s);
            q.lng = t * Math.cos(r);
            q.lat = t * Math.sin(r);
            return q
        }, Convert_GCJ02_To_BD09: function (q) {
            var s = (3.141592653589793 * 3000) / 180;
            var p = q.lng, u = q.lat;
            var t = Math.sqrt(p * p + u * u) + 0.00002 * Math.sin(u * s);
            var r = Math.atan2(u, p) + 0.000003 * Math.cos(p * s);
            q.lng = t * Math.cos(r) + 0.0065;
            q.lat = t * Math.sin(r) + 0.006;
            return q
        }, animateNum: function (F, z, H, x) {
            var t = i(F);
            var u = t.text().replace(/,/g, "");
            z = z === null || z === undefined || z === true || z === "true";
            H = isNaN(H) ? 500 : H;
            x = isNaN(x) ? 100 : x;
            var B = "INPUT,TEXTAREA".indexOf(t.get(0).tagName) >= 0;
            var v = function (L) {
                var J = "";
                for (var K = 0; K < L.length; K++) {
                    if (!isNaN(L.charAt(K))) {
                        return J
                    } else {
                        J += L.charAt(K)
                    }
                }
            }, C = function (L) {
                var J = "";
                for (var K = L.length - 1; K >= 0; K--) {
                    if (!isNaN(L.charAt(K))) {
                        return J
                    } else {
                        J = L.charAt(K) + J
                    }
                }
            }, E = function (K, J) {
                if (!J) {
                    return K
                }
                if (!/^[0-9]+.?[0-9]*$/.test(K)) {
                    return K
                }
                K = K.toString();
                return K.replace(K.indexOf(".") > 0 ? /(\d)(?=(\d{3})+(?:\.))/g : /(\d)(?=(\d{3})+(?:$))/g, "$1,")
            };
            var I = v(u.toString());
            var r = C(u.toString());
            var s = u.toString().replace(I, "").replace(r, "");
            if (isNaN(s) || s === 0) {
                B ? t.val(u) : t.html(u);
                console.error("非法数值！");
                return
            }
            var w = s.split(".");
            var q = w[1] ? w[1].length : 0;
            var p = 0, A = s;
            if (Math.abs(A) > 10) {
                p = parseFloat(w[0].substring(0, w[0].length - 1) + (w[1] ? ".0" + w[1] : ""))
            }
            var y = (A - p) / x, G = 0;
            var D = setInterval(function () {
                var J = I + E(p.toFixed(q), z) + r;
                B ? t.val(J) : t.html(J);
                p += y;
                G++;
                if (Math.abs(p) >= Math.abs(A) || G > 5000) {
                    J = I + E(A, z) + r;
                    B ? t.val(J) : t.html(J);
                    clearInterval(D)
                }
            }, H / x)
        }, deepClone: function (s) {
            var p;
            var q = n.util.isClass(s);
            if (q === "Object") {
                p = {}
            } else {
                if (q === "Array") {
                    p = []
                } else {
                    return s
                }
            }
            for (var r in s) {
                var t = s[r];
                if (n.util.isClass(t) == "Object") {
                    p[r] = arguments.callee(t)
                } else {
                    if (n.util.isClass(t) == "Array") {
                        p[r] = arguments.callee(t)
                    } else {
                        p[r] = s[r]
                    }
                }
            }
            return p
        }, isClass: function (p) {
            if (p === null) {
                return "Null"
            }
            if (p === undefined) {
                return "Undefined"
            }
            return Object.prototype.toString.call(p).slice(8, -1)
        }
    };
    var m = ".layui-layout-admin.admin-nav-mini>.layui-side .layui-nav .layui-nav-item";
    i(document).on("mouseenter", m + "," + m + " .layui-nav-child>dd", function () {
        if (n.getPageWidth() > 768) {
            var q = i(this), s = q.find(">.layui-nav-child");
            if (s.length > 0) {
                q.addClass("admin-nav-hover");
                s.css("left", q.offset().left + q.outerWidth());
                var r = q.offset().top;
                if (r + s.outerHeight() > n.getPageHeight()) {
                    r = r - s.outerHeight() + q.outerHeight();
                    (r < 60) && (r = 60);
                    s.addClass("show-top")
                }
                s.css("top", r);
                s.addClass("ew-anim-drop-in")
            } else {
                if (q.hasClass("layui-nav-item")) {
                    var p = q.find("cite").text();
                    k.tips(p, q, {
                        tips: [2, "#303133"], time: -1, success: function (t, u) {
                            i(t).css("margin-top", "12px")
                        }
                    })
                }
            }
        }
    }).on("mouseleave", m + "," + m + " .layui-nav-child>dd", function () {
        k.closeAll("tips");
        var q = i(this);
        q.removeClass("admin-nav-hover");
        var p = q.find(">.layui-nav-child");
        p.removeClass("show-top ew-anim-drop-in");
        p.css({"left": "unset", "top": "unset"})
    });
    i(document).on("click", "*[ew-event]", function () {
        var p = i(this).attr("ew-event");
        var q = n.events[p];
        q && q.call(this, i(this))
    });
    i(document).on("mouseenter", "*[lay-tips]", function () {
        var p = i(this).attr("lay-tips");
        var q = i(this).attr("lay-direction");
        var r = i(this).attr("lay-bg");
        var s = i(this).attr("lay-offset");
        k.tips(p, this, {
            tips: [q || 1, r || "#303133"], time: -1, success: function (t, u) {
                if (s) {
                    s = s.split(",");
                    var w = s[0], v = s.length > 1 ? s[1] : undefined;
                    w && (i(t).css("margin-top", w));
                    v && (i(t).css("margin-left", v))
                }
            }
        })
    }).on("mouseleave", "*[lay-tips]", function () {
        k.closeAll("tips")
    });
    var h = layui.data(c.tableName);
    if (h && h.theme) {
        (h.theme == d) || layui.link(n.getThemeDir() + h.theme + n.getCssSuffix(), h.theme)
    } else {
        if (d != c.defaultTheme) {
            layui.link(n.getThemeDir() + c.defaultTheme + n.getCssSuffix(), c.defaultTheme)
        }
    }
    g("admin", n)
});
