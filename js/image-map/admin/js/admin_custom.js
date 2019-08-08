var dataTableUtil = {
        create: function(e, t, a, i, n, l, s, r) {
            for (var o = $("thead th", e).length, d = [], c = 0; o > c; c++) s === c && r ? d.push({
                sSearch: r
            }) : d.push(null);
            var h = e.dataTable({
                dom: '<"table-controls"lp>ti',
                pagingType: "bootstrap",
                pageLength: 25,
                destroy: !0,
                autoWidth: !1,
                deferRender: !0,
                stateSave: !0,
                stateSaveParams: function(e, t) {
                    t.columns[1].search.search = ""
                },
                columnDefs: [{
                    orderable: !1,
                    targets: [s]
                }],
                searchCols: d,
                order: [
                    [n, "asc"]
                ],
                serverSide: !0,
                ajax: function(e, t, a) {
                    a.jqXHR = serviceCtrl[i](e, [function(e, a) {
                        t(a)
                    }, function() {
                        h.clearSelection(!0)
                    }])
                },
                rowCallback: l,
                language: {
                    zeroRecords: "No records",
                    lengthMenu: 'Display <select><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option></select> records'
                }
            });
            return h.selectedObj = null, h.selectedRow = null, h.filterByMapId = function(e) {
                h.fnFilter(e, s)
            }, h.clearMapFilter = function() {
                h.fnFilter("", s)
            }, h.clearSelection = function(e) {
                $(h.fnSettings().aoData).each(function() {
                    $(this.nTr).removeClass("row_selected")
                }), e ? h.selectionChange() : (h.selectedObj = null, h.selectedRow = null)
            }, $("tbody", h).on("click.dataTableUtil", function(e) {
                h.clearSelection(), $(e.target.parentNode).closest("tr").addClass("row_selected"), h.selectionChange()
            }), h.getSelectedRow = function() {
                for (var e = h.fnGetNodes(), t = 0; t < e.length; t++)
                    if ($(e[t]).hasClass("row_selected")) return e[t];
                return !1
            }, h.selectionChange = function() {
                h.selectedRow = h.getSelectedRow(), h.selectedObj = h.selectedRow ? h.fnGetData(this.selectedRow) : null, e.trigger("selectionChange")
            }, t.on("submit", function() {
                return val = $(this).find("input[type=text]").val(), 0 == val.length ? (h.fnFilter("", a), h.clearSelection(!0)) : h.fnFilter(val, a), !1
            }), h.destroy = function() {
                $("tbody", h).off("click.dataTableUtil"), t.off(), $("input", t).val(""), h.fnDestroy(), $("tbody", h).empty(), h.selectedObj = h.selectedRow = null
            }, h
        }
    },
    formValidator = {
        errorMessage: "Please fill correct fields highlighted by red color. Hover on it to see details.",
        init: function() {
            $.validator.setDefaults({
                ignore: ".val-ignored",
                focusInvalid: !1,
                onkeyup: !1,
                onfocusout: !1,
                onfocusin: !1,
                onclick: !1,
                errorPlacement: function(e, t) {
                    var a;
                    a = "hidden" == t.attr("type") ? t.siblings("button:eq(0)") : t, a.data("tooltip") && a.tooltip("destroy"), a.attr("rel", "tooltip").attr("title", e.html()).data({
                        className: "error"
                    }), e.data("val-element", a), a.data("val-label", e)
                },
                highlight: function(e) {
                    $(e).closest(".control-group").addClass("error")
                },
                unhighlight: function(e) {
                    $(e).closest(".control-group").removeClass("error")
                },
                success: function(e) {
                    var t = e,
                        a = t.data("val-element");
                    a.removeAttr("rel title data-original-title").tooltip("destroy"), a.removeData("val-label"), t.removeData("val-element")
                },
                invalidHandler: function(e) {
                    $alertElement = $(e.currentTarget).data("alert-element"), $alertElement && $alertElement.html(formValidator.errorMessage).removeClass("hide")
                }
            });
            var e = $.validator.prototype.resetForm;
            $.validator.prototype.resetForm = function() {
                e.call(this, e);
                var t = $(this.currentForm);
                $(".control-group.error", t).removeClass("error");
                var a = this;
                $(':input[rel="tooltip"]', t).each(function() {
                    a.settings.success.call(a, $(this).data("val-label"))
                }), $alertElement = t.data("alert-element"), $alertElement && $alertElement.addClass("hide").empty(), this.cleanDirty()
            }, $.validator.addMethod("required", function(e, t, a) {
                if (!this.depend(a, t)) return "dependency-mismatch";
                if ("select" === t.nodeName.toLowerCase()) {
                    var i = $(t).val();
                    return i && i.length > 0
                }
                return this.checkable(t) ? this.getLength(e, t) > 0 : e.trim().length > 0
            }), $.validator.addMethod("minlen", function(e, t, a) {
                return "" == e || e.length >= a
            }), $.validator.addMethod("maxlen", function(e, t, a) {
                return "" == e || e.length <= a
            }), $.validator.addMethod("cssname", function(e) {
                var t = /^([0-9a-z-]+)$/gi;
                return t.test(e)
            }), $.validator.addMethod("pass", function(e) {
                var t = /^(?=.*[0-9]+.*)(?=.*[a-zA-Z]+.*)[0-9a-zA-Z]{6,}$/gi;
                return t.test(e)
            }), $.validator.addMethod("integer", function(e) {
                var t = /^(-{0,1}[0-9]+)$/gi;
                return t.test(e)
            }), $.validator.prototype.cleanDirty = function() {
                var e = $(this.currentForm);
                this.savedData = e.serialize()
            }, $.validator.prototype.isDirty = function() {
                var e = $(this.currentForm);
                return this.savedData != e.serialize() ? !0 : !1
            }, $.validator.messages.minlen = "Please enter more than {0} characters.", $.validator.messages.maxlen = "Please enter no more than {0} characters.", $.validator.messages.cssname = 'This field can contain letters, numbers<br>or "-" char.', $.validator.messages.integer = "Please enter valid integer.", $.validator.messages.pass = "Password must be at least 6 chars length and contain digits and alphabets."
        },
        validate: function(e, t, a) {
            e.data("alert-element", t);
            var i = e.validate({
                submitHandler: function() {
                    t && t.addClass("hide"), a && a()
                }
            });
            return i
        }
    };
$.extend(serviceCtrl, {
    uploadMapPath: serviceCtrl.gatePath + "?c=admin&a=uploadMap",
    uploadIconsPath: serviceCtrl.gatePath + "?c=admin&a=uploadIcons",
    uploadImagesPath: serviceCtrl.gatePath + "?c=admin&a=uploadImages",
    _cache: {},
    isCached: function(e, t) {
        var a = !1;
        return $.each(this._cache, function(i, n) {
            return i == e && n.callData == (t ? $.param(t) : null) ? (a = !0, !1) : void 0
        }), a
    },
    clearCache: function() {
        if (arguments.length)
            for (var e = 0, t = arguments.length; t > e; e++) delete this._cache[arguments[e]];
        else this._cache = {}
    },
    saveCache: function(e, t, a) {
        this._cache[e] = {
            callData: t ? $.param(t) : null,
            value: a
        }
    },
    getCache: function(e, t) {
        var a;
        return $.each(this._cache, function(i, n) {
            return n.callData == (t ? $.param(t) : null) && i == e ? (a = n.value, !1) : void 0
        }), a
    },
    callWithCache: function(e, t, a, i) {
        if (this.isCached(t, a)) return i(1, this.getCache(t, a)), void 0;
        var n = this;
        this.callAjax(e, t, function(e, l) {
            i(e, l), 1 == e && n.saveCache(t, a, l)
        }, a)
    },
    clearServerCache: function() {
        this.clearCache(), this.callAjax("admin", "clearCache")
    },
    getMapView: function(e, t) {
        this.callAjax("admin", "getMapView", t, {
            id: e
        })
    },
    saveElementsPositions: function(e, t) {
        this.callAjax("admin", "saveElementsPositions", t, e)
    },
    login: function(e, t) {
        this.callAjax("admin", "login", t, e)
    },
    changePasssword: function(e, t) {
        this.callAjax("admin", "changePasssword", t, e)
    },
    logout: function() {
        this.callAjax("admin", "logout")
    },
    getTree: function(e) {
        this.callWithCache("admin", "index", null, e)
    },
    saveTreeOrder: function(e, t) {
        this.callAjax("admin", "saveTreeOrder", t, e), this.clearCache("index")
    },
    getMarkers: function(e, t) {
        return this.callAjax("admin", "getMarkers", t, e)
    },
    getMarkerTypes: function(e) {
        this.callWithCache("admin", "getMarkerTypes", null, e)
    },
    getMarkerType: function(e, t, a) {
        var i = {
            id: e,
            onlyEnabledParams: t ? 1 : 0
        };
        this.callWithCache("admin", "getMarkerType", i, a)
    },
    deleteMarkerType: function(e, t) {
        this.callAjax("admin", "deleteMarkerType", t, {
            id: e
        }), this.clearCache("getMarkerTypes", "getMarkerType")
    },
    saveMarkerType: function(e, t) {
        this.callAjax("admin", "saveMarkerType", t, e), this.clearCache("getMarkerTypes", "getMarkerType")
    },
    getDictionaries: function(e) {
        this.callWithCache("admin", "getDictionaries", null, e)
    },
    getDictionary: function(e, t) {
        this.callAjax("admin", "getDictionary", t, {
            id: e
        })
    },
    getDictionariesEntries: function(e, t) {
        if (!$.isArray(e)) throw "ids parameter must be an array";
        this.callAjax("admin", "getDictionariesEntries", t, {
            ids: e
        })
    },
    deleteDictionary: function(e, t) {
        this.callAjax("admin", "deleteDictionary", t, {
            id: e
        }), this.clearCache("getDictionaries")
    },
    saveDictionary: function(e, t) {
        this.callAjax("admin", "saveDictionary", t, e), this.clearCache("getDictionaries")
    },
    getLabels: function(e, t) {
        return this.callAjax("admin", "getLabels", t, e)
    },
    getLabel: function(e, t) {
        this.callAjax("admin", "getLabel", t, {
            id: e
        })
    },
    deleteLabel: function(e, t) {
        this.callAjax("admin", "deleteLabel", t, {
            id: e
        })
    },
    saveLabel: function(e, t) {
        this.callAjax("admin", "saveLabel", t, e)
    },
    getMarker: function(e, t) {
        this.callAjax("admin", "getMarker", t, {
            id: e
        })
    },
    deleteMarker: function(e, t) {
        this.callAjax("admin", "deleteMarker", t, {
            id: e
        })
    },
    saveMarker: function(e, t) {
        this.callAjax("admin", "saveMarker", t, e)
    },
    getMap: function(e, t) {
        this.callAjax("admin", "getMap", t, {
            id: e
        })
    },
    deleteMap: function(e, t) {
        this.callAjax("admin", "deleteMap", t, {
            id: e
        }), this.clearCache("index")
    },
    saveMap: function(e, t) {
        this.callAjax("admin", "saveMap", t, e), this.clearCache("index")
    },
    getRegions: function(e, t) {
        this.callAjax("admin", "getRegions", t, {
            id: e
        })
    },
    getRegion: function(e, t) {
        this.callAjax("admin", "getRegion", t, {
            id: e
        })
    },
    deleteRegion: function(e, t) {
        this.callAjax("admin", "deleteRegion", t, {
            id: e
        })
    },
    saveRegion: function(e, t) {
        this.callAjax("admin", "saveRegion", t, e)
    },
    saveRegionPosition: function(e, t) {
        this.callAjax("admin", "saveRegionPosition", t, e)
    },
    getSettings: function(e) {
        this.callAjax("admin", "getSettings", e)
    },
    saveSettings: function(e, t) {
        this.callAjax("admin", "saveSettings", t, e)
    },
    changePassword: function(e, t) {
        this.callAjax("admin", "changePassword", t, e)
    },
    getImages: function(e) {
        this.callAjax("admin", "getImages", e)
    },
    getIcons: function(e) {
        this.callAjax("admin", "getIcons", e)
    },
    deleteIcon: function(e, t) {
        this.callAjax("admin", "deleteIcon", t, {
            id: e
        })
    },
    deleteImage: function(e, t) {
        this.callAjax("admin", "deleteImage", t, {
            id: e
        })
    }
}), $.extend(Templates, {
    orderSaved: "New order saved.",
    mapSaved: "Map saved.",
    regionSaved: "Region saved.",
    dictionarySaved: "Dictionary saved.",
    deleted: "Item deleted.",
    labelSaved: "Label saved.",
    markerSaved: "Marker saved.",
    regionSaved: "Region saved.",
    markerTypeSaved: "Marker type saved.",
    positionsSaved: "Positions saved.",
    settingsSaved: "Settings saved.",
    passwordChanged: "Password changed.",
    cssInvalid: "Css name is not unique.",
    oldPasswordInvalid: "Old password is invalid.",
    passwordInvalid: "Wrong password.",
    noChanges: "Nothing to save for now.",
    notEnabled: "Element not enabled<br/>can't be positioned on the map.",
    breadcrumb: function(e) {
        for (var t = '<ul class="breadcrumb">', a = '<span class="divider"><i class="icon-chevron-right"></i></span>', i = e.parents ? e.parents.concat([e]) : [e], n = 0; n < i.length; n++) t += "<li>", n > 0 && (t += a), t += $.escapifyHTML(i[n].name), t += "</li>";
        return t + "</ul>"
    },
    thumbnail: function(e) {
        return '<li><a href="#" class="thumbnail" ><img src="' + e.url + '"  ><div class="caption" >' + e.id + "</div></a></li>"
    },
    regionName: function(e) {
        return $.escapifyHTML(e.name)
    },
    elementsMoved: function(e, t) {
        return '<ul class="unstyled"><li>markers: <strong>' + e + "</strong></li><li>labels: <strong>" + t + "</strong></li></ul>"
    },
    regionRow: function(e) {
        return "<tr><td>" + e.id + "</td><td>" + e.name + "</td></tr>"
    },
    dictionaryEntryRow: function(e) {
        return "<tr><td>" + e.id + "</td><td>" + $.escapifyHTML(e.value) + "</td></tr>"
    },
    customParamRow: function(e) {
        return "<tr " + (parseInt(e.enabled) ? "" : 'class="disabled"') + "><td>" + this.paramTypeText(e.type) + "</td><td>" + $.escapifyHTML(e.label) + '</td><td class="h-centered">' + this.onOffIcon(e.enabled) + '</td><td class="h-centered">' + this.onOffIcon(e.searchable) + '</td><td class="h-centered">' + this.onOffIcon(e.showLabel) + '</td><td class="h-centered">' + this.onOffIcon(e.alwaysVisible) + "</td></tr>"
    },
    paramTypeText: function(e) {
        switch (e) {
            case "text":
                return "Text";
            case "longText":
                return "Long text";
            case "dictionary":
                return "Dictionary";
            case "link":
                return "Link"
        }
    },
    onOffIcon: function(e) {
        return parseInt(e) ? '<i class="icon-ok"></i>' : '<i class="icon-remove"></i>'
    },
    paramControlGroup: function(e, t) {
        return '<div class="control-group"><label class="control-label" for="metParam' + e.number + '">' + $.escapifyHTML(e.label) + '</label><div class="controls">' + this.paramInput(e.type, "param" + e.number + "Value", t) + "</div></div>"
    },
    paramInput: function(e, t, a) {
        var i = this;
        switch (e) {
            case "text":
                return '<input id="metParam' + t + '" name="' + t + '" type="text" class="input-xxlarge" minlen="2" maxlen="80" placeholder="type..." >';
            case "longText":
                return '<textarea id="metParam' + t + '" name="' + t + '" class="input-xxlarge" rows="5" minlen="5" placeholder="type..."></textarea>&nbsp;<i class="info-icon icon-question-sign" rel="tooltip" title="You can put any html data here. Data is not escaped so be careful."></i>';
            case "dictionary":
                var n = this.selectOption("", "-- empty --");
                return a && $.each(a, function(e, t) {
                    n += i.selectOption(t.id, t.value, !0)
                }), '<select id="metParam' + t + '" name="' + t + '" >' + n + "</select>";
            case "link":
                var n = '<div class="control-group"><input id="metParam' + t + '" name="' + t + '-multi-0" type="text" class="input-xxlarge" minlen="2" maxlen="80" placeholder="Text to show..." ></div>';
                return n += '<div class="control-group"><input name="' + t + '-multi-1" type="text" class="input-xxlarge" minlen="2" maxlen="80" placeholder="URL to open..." ></div>', n += '<div class="control-group"><label class="checkbox"><input name="' + t + '-multi-2" type="checkbox"  />Open in new tab</label></div>'
        }
    },
    regionsHeader: function(e) {
        return "Regions for map: " + e
    }
});
var treeCtrl = {
    $treeElement: null,
    treeInstance: null,
    init: function() {
    	/*
        this.$treeElement = $("<div></div>"), this.$treeElement.jstree({
            json_data: {
                data: []
            },
            ui: {
                select_limit: 1
            },
            crrm: {
                move: {
                    check_move: function() {
                        return !0
                    }
                }
            },
            dnd: {
                drop_target: !1,
                drag_target: !1
            },
            themes: {
                theme: "apple",
                url: "../common/css/apple/style.css"
            },
            types: {
                types: {
                    "default": {
                        icon: {
                            image: "../common/img/map_icon.png"
                        },
                        select_node: function(e) {
                            this.is_closed(e) && this.toggle_node(e)
                        },
                        deselect_node: function() {
                            return !1
                        }
                    },
                    noMap: {
                        icon: {
                            image: "img/noMap_icon.png"
                        }
                    }
                }
            },
            plugins: ["themes", "ui", "dnd", "crrm", "types", "json_data"]
        }).on("loaded.jstree", function() {
            $("#jstree-marker-line").css("z-index", -1)
        }), this.treeInstance = $.jstree._reference(this.$treeElement.jstree("get_index"))
       */ 
    },
    refresh: function() {
        serviceCtrl.getTree($.proxy(this.dataCallback, this))
    },
    dataCallback: function(e, t) {
        t.length ? viewCtrl.applyViewState(mapsTabCtrl.$tabElement, "") : viewCtrl.applyViewState(mapsTabCtrl.$tabElement, "noMaps"), this.treeInstance._get_settings().json_data.data != t && (this.treeInstance._get_settings().json_data = {
            data: t
        }, this.treeInstance.refresh(-1), this.$treeElement.jstree("open_all")), this.$treeElement.triggerHandler("select_node.jstree")
    },
    disableDrag: function() {
        this.treeInstance._get_settings().crrm.move.check_move = function() {
            return !1
        }
    },
    enableDrag: function() {
        this.treeInstance._get_settings().crrm.move.check_move = function() {
            return !0
        }
    },
    getSelectedObj: function() {
        var e = this.$treeElement.jstree("get_selected"),
            t = [];
        if (e.length) {
            return e.parents("li").each(function() {
                var e = $(this);
                t.unshift($.extend({
                    name: e.children("a").text()
                }, e.data()))
            }), $.extend({
                name: e.children("a").text(),
                parents: t
            }, e.data())
        }
        return null
    }
};
$.extend(mapViewerCtrl, {
    _super: {
        selectElement: $.proxy(mapViewerCtrl.selectElement, mapViewerCtrl),
        unselectElement: $.proxy(mapViewerCtrl.unselectElement, mapViewerCtrl),
        selectMarker: $.proxy(mapViewerCtrl.selectMarker, mapViewerCtrl),
        showRegion: $.proxy(mapViewerCtrl.showRegion, mapViewerCtrl),
        onViewerAfterMove: $.proxy(mapViewerCtrl.onViewerAfterMove, mapViewerCtrl),
        clear: $.proxy(mapViewerCtrl.clear, mapViewerCtrl)
    }
}, {
    draggableMode: !1,
    viewerMoveDirty: !1,
    disablePopovers: !1,
    selectElement: function(e, t, a) {
 			  
    	//set off draggin marker if no access
    	if($('#mapContainer').attr('data-edit-access')!=1)
    	{    	
	    	this._super.selectElement(e, t, a)
	    	this.$mapContainer.trigger("selectionChange")
	    			    	 
	    	return false;
    	}
    	
        if (this._super.selectElement(e, t, a), this.$mapContainer.trigger("selectionChange"), this.draggableMode) {
            var i, n, l, s = this,
                r = $(".cfm-selected", this.$mapContainer),
                o = this.findLeafletMarkerByEl(r);
            o.dragging.enable(), o.on("dragstart", function(e) {
                i = $("#markerPopover"), n = i.position(), l = s.mapViewer.project(e.target.getLatLng())
            }), o.on("drag", function(e) {
                var t = s.mapViewer.project(e.target.getLatLng());
                t.x -= l.x, t.y -= l.y, i.length && i.css({
                    left: n.left + t.x,
                    top: n.top + t.y
                })
            }), o.on("dragend", function(e) {
                var t = s.mapViewer.project(e.target.getLatLng(), s.mapViewer.getMaxZoom()),
                    a = {
                        x: t.x,
                        y: t.y,
                        id: r.data("id")
                    };
                s.$mapContainer.trigger("elementMoved", [r, a])
            })
        }
    },
    onEvent: function(e, t) {
        this.$mapContainer.on(e, t)
    },
    offEvent: function(e) {
        this.$mapContainer.off(e)
    },
    unselectElement: function() {
        if ($selected = $(".cfm-selected", this.$mapContainer), $selected.length) {
            var e = this.findLeafletMarkerByEl($selected);
            e.dragging.disable(), e.clearAllEventListeners()
        }
        this._super.unselectElement() && this.$mapContainer.trigger("selectionChange")
    },
    selectMarker: function(e, t, a, i) {
        this._super.selectMarker(e, t, a, i, this.disablePopovers)
    },
    showRegion: function(e, t, a) {
        this._super.showRegion(e, t, a), this.viewerMoveDirty = !1
    },
    onViewerAfterMove: function(e) {
        mapViewerCtrl._super.onViewerAfterMove(e), mapViewerCtrl.viewerMoveDirty = !0
    },
    clear: function() {
        mapViewerCtrl._super.clear(), mapViewerCtrl.viewerMoveDirty = !1, mapViewerCtrl.currentMapId = null
    }
});
var loginTabCtrl = {
        tabName: "loginTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        init: function() {
            //this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this._validator = formValidator.validate(this.$formElement, null, $.proxy(this.saveForm, this))
        },
        enter: function() {},
        saveForm: function() {
            //serviceCtrl.login(this.$formElement.serializeObject(), $.proxy(this.saveSuccess, this))
        },
        saveSuccess: function(e) {
            //2 == e ? viewCtrl.notify(Templates.passwordInvalid, !0) : (viewCtrl.showTab("mapsTab"), viewCtrl.$mainNavigation.removeClass("hide"))
        },
        exit: function() {
            //return this._validator.resetForm(), !0
            return !0;                        
        }
    },
    mapsTabCtrl = {
        tabName: "mapsTab",
        $navElement: null,
        $tabElement: null,
        $controlButtons: null,
        $actionButton: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$actionButton = $(".btn-group:eq(1)", this.$tabElement), this.$controlButtons.controls(this, ["add"]), $(".dropdown-menu", this.$actionButton).controls(this)
        },
        enter: function() {
            treeCtrl.enableDrag(), treeCtrl.$treeElement.detach().appendTo($(".tree-holder", this.$tabElement));
            var e = this;
            treeCtrl.$treeElement.on("select_node.jstree", function() {
                e.selectionChange()
            }).on("move_node.jstree", function(t, a) {
                serviceCtrl.saveTreeOrder({
                    id: a.rslt.o.data().id,
                    parentId: a.rslt.np.data().id ? a.rslt.np.data().id : "",
                    position: a.rslt.o.index()
                }, $.proxy(e.saveOrderSuccess, e))
            }), treeCtrl.refresh()
        },
        exit: function() {
            return treeCtrl.$treeElement.off("select_node.jstree"), treeCtrl.$treeElement.off("move_node.jstree"), !0
        },
        saveOrderSuccess: function() {
            viewCtrl.notify(Templates.orderSaved), treeCtrl.refresh()
        },
        selectionChange: function() {
            var e = treeCtrl.getSelectedObj();
            e ? (this.$controlButtons.controls("enable"), $("a:eq(0)", this.$actionButton).removeClass("disabled"), e.noMap && $("a:eq(0)", this.$actionButton).addClass("disabled"), (!parseInt(e.enabled) || e.noMap) && this.$controlButtons.controls("disable", ["preview"])) : (this.$controlButtons.controls("disable"), $("a:eq(0)", this.$actionButton).addClass("disabled"))
        },
        add: function() {
            viewCtrl.showTab("mapEditTab")
        },
        edit: function() {
            viewCtrl.showTab("mapEditTab", [treeCtrl.getSelectedObj().id])
        },
        upload: function() {
            var e = treeCtrl.getSelectedObj();
            modalCtrl.uploadModal("map", e.id, function() {
                treeCtrl.refresh()
            })
        },
        preview: function() {
            window.open("../#/?map=" + treeCtrl.getSelectedObj().id, "_blank")
        },
        moveElements: function() {
            viewCtrl.showTab("mapViewTab", [treeCtrl.getSelectedObj().id, "marker"])
        },
        addMarker: function() {
            viewCtrl.showTab("markerEditTab", [null, treeCtrl.getSelectedObj()])
        },
        addLabel: function() {
            viewCtrl.showTab("labelEditTab", [null, treeCtrl.getSelectedObj()])
        },
        browseMarkers: function() {
            viewCtrl.showTab("markersTab", [treeCtrl.getSelectedObj()])
        },
        browseLabels: function() {
            viewCtrl.showTab("labelsTab", [treeCtrl.getSelectedObj()])
        },
        editRegions: function() {
            viewCtrl.showTab("regionsTab", [treeCtrl.getSelectedObj()])
        },
        remove: function() {
            var e = this;
            modalCtrl.deleteModal("Are you sure you want to delete the map?<br>This will delete all markers difinied for this map.", function() {
                serviceCtrl.deleteMap(treeCtrl.getSelectedObj().id, $.proxy(e.deleteSuccess, e))
            })
        },
        deleteSuccess: function() {
            viewCtrl.notify(Templates.deleted), treeCtrl.refresh()
        }
    },
    mapEditTabCtrl = {
        tabName: "mapEditTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        _validator: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this.$mapHiddenInput = $('input[name="mapImage"]', this.$formElement), this._validator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$tabElement), $.proxy(this.saveForm, this));
            var e = this;
            $('button[data-action="cancel"]', this.$formElement).click(function() {
                e.exit(!0), viewCtrl.showPrevTab()
            })
        },
        enter: function(e) {
            e ? serviceCtrl.getMap(e, $.proxy(this.getSuccess, this)) : this.getSuccess(), viewCtrl.applyViewState(this.$tabElement, e ? "edit" : "new")
        },
        getSuccess: function(e, t) {
            this.$formElement.objectDeserialize(t), this._validator.cleanDirty()
        },
        exit: function(e) {
            return !this._validator.isDirty() || e ? (this._validator.resetForm(), !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        saveForm: function() {
            return this._validator.isDirty() ? (serviceCtrl.saveMap(this.$formElement.serializeObject(), $.proxy(this.saveSuccess, this)), void 0) : viewCtrl.notify(Templates.noChanges)
        },
        saveSuccess: function() {
            this.exit(!0), viewCtrl.notify(Templates.mapSaved), viewCtrl.showPrevTab()
        }
    };
mapViewTabCtrl = {
    tabName: "mapViewTab",
    $navElement: null,
    $tabElement: null,
    $statusElement: null,
    $regionZoomCbx: null,
    $controlButtons: null,
    $selectedElement: null,
    _mode: null,
    _editedId: null,
    _movedMarkersCount: 0,
    _movedLabelsCount: 0,
    _movedElements: null,
    init: function() {
        this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$statusElement = $('div[data-action="status"]', this.$tabElement), this.$regionZoomCbx = $("#mvtCbx", this.$tabElement), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$controlButtons.controls(this);
        var e = this;
        
        //handle save marker after marker click.                 	
      	$('body').mouseup(function(){
      		setTimeout(function() {
      			return e.saveElements(), void 0;
      		}, 100);
        })          
        
                        
        $('button[data-action="save"]', this.$tabElement).click(function() {
            switch (e._mode) {
                case "label":
                case "marker":
                    return e.saveElements(), void 0;
                case "region":
                    return e.saveRegion(), void 0
            }
        }), $('button[data-action="cancel"]', this.$tabElement).click(function() {
            "region" == e._mode && e.saveRegionSuccess(null, null, !0)
        }), $("#mvtMpCbx").change(function() {
            mapViewerCtrl.disablePopovers = $(this).prop("checked")
        }), this.resetmovedElements()
    },
    enter: function(e, t, a) {
        switch (this._mode = t, this._editedId = a, viewCtrl.windowResize(), this.$controlButtons.controls("disable"), this._mode) {
            case "region":
                mapViewerCtrl.showRegion(e, this._editedId), mapViewerCtrl.draggableMode = !1;
                break;
            case "marker":
            case "label":
                this._editedId ? mapViewerCtrl["marker" == this._mode ? "selectMarker" : "selectLabel"](e, this._editedId) : mapViewerCtrl.showMap(e), mapViewerCtrl.draggableMode = !0, mapViewerCtrl.onEvent("selectionChange", $.proxy(this.selectionChange, this)), mapViewerCtrl.onEvent("elementMoved", $.proxy(this.elementMoved, this));
                break;
            default:
                throw "mapViewTab unknown mode"
        }
        viewCtrl.applyViewState(this.$tabElement, this._mode), this.updateStatus(), this.loadTypesCss()
    },
    exit: function(e) {
        return e || !this.isDirty() ? (this.$regionZoomCbx.prop("checked", !1), mapViewerCtrl.clear(), this._mode = null, this._editedId = null, this.resetmovedElements(), mapViewerCtrl.offEvent("elementMoved"), mapViewerCtrl.offEvent("selectionChange"), this.unloadTypesCss(), !0) : (modalCtrl.unsavedModal($.proxy(function() {
            this.exit(!0), viewCtrl.tabChangeProceed()
        }, this)), !1)
    },
    selectionChange: function() {
        this.$selectedElement = $(".cfm-selected", mapViewerCtrl.$selectedLayer), this.$selectedElement ? this.$controlButtons.controls("enable") : this.$controlButtons.controls("disable")
    },
    elementMoved: function(e, t, a) {
        t.hasClass("cfm-marker") ? (this._movedElements.markers[a.id] || this._movedMarkersCount++, this._movedElements.markers[a.id] = a) : t.hasClass("cfm-label") && (this._movedElements.labels[a.id] || this._movedLabelsCount++, this._movedElements.labels[a.id] = a), this.updateStatus()
    },
    getTypesCssPath: function() {
    	  return '';
        //return serviceCtrl.gatePath + "css.php?rand=" + Math.floor(1e3 * Math.random() + 1)
    },
    loadTypesCss: function() {
        $("head").append("<link>");
        var e = $("head").children(":last");
        e.attr({
            rel: "stylesheet",
            type: "text/css",
            href: this.getTypesCssPath(),
            id: "typesCss"
        })
    },
    unloadTypesCss: function() {
        $("#typesCss").remove()
    },
    removeSelected: function() {
        var e = this;
        modalCtrl.deleteModal("Are you sure you wanna selected element?", function() {
            var t = e.$selectedElement.hasClass("cfm-marker") ? "markers" : "labels",
                a = !1;
            e._movedElements[t][e.$selectedElement.data("id")] && (delete e._movedElements[t][e.$selectedElement.data("id")], a = !0), "markers" == t ? (serviceCtrl.deleteMarker(e.$selectedElement.data("id"), $.proxy(e.deleteSuccess, e)), a && e._movedMarkersCount--) : (serviceCtrl.deleteLabel(e.$selectedElement.data("id"), $.proxy(e.deleteSuccess, e)), a && e._movedLabelsCount--)
        })
    },
    resetmovedElements: function() {
        this._movedMarkersCount = 0, this._movedLabelsCount = 0, this._movedElements = {
            labels: {},
            markers: {}
        }
    },
    deleteSuccess: function() {
        var e = this.$selectedElement;
        mapViewerCtrl.unselectElement(), e.remove(), this.updateStatus(), viewCtrl.notify(Templates.deleted)
    },
    editSelected: function() {
        viewCtrl.showTab(this.$selectedElement.hasClass("cfm-marker") ? "markerEditTab" : "labelEditTab", [this.$selectedElement.data("id")])
    },
    updateStatus: function() {
        switch (this._mode) {
            case "region":
                return this.$statusElement.empty(), void 0;
            case "label":
            case "marker":
                return this.$statusElement.html(Templates.elementsMoved(this._movedMarkersCount, this._movedLabelsCount)), void 0
        }
    },
    isDirty: function() {
        switch (this._mode) {
            case "label":
            case "marker":
                return this._movedMarkersCount || this._movedLabelsCount;
            case "region":
                return mapViewerCtrl.viewerMoveDirty
        }
        return !1
    },
    saveElements: function() {
        return this.isDirty() ? (serviceCtrl.saveElementsPositions(this._movedElements, $.proxy(this.saveElementsSuccess, this)), void 0) : viewCtrl.notify(Templates.noChanges)
    },
    saveElementsSuccess: function(e, t, a) {
        a || viewCtrl.notify(Templates.positionsSaved), this.resetmovedElements(), this.updateStatus()
    },
    saveRegion: function() {
        var e = this.$regionZoomCbx.prop("checked");
        if (!this.isDirty() && !e) return viewCtrl.notify(Templates.noChanges);
        var t = mapViewerCtrl.mapViewer.project(mapViewerCtrl.mapViewer.getCenter(), mapViewerCtrl.mapViewer.getMaxZoom());
        savingData = {
            id: this._editedId
        }, savingData.x = t.x, savingData.y = t.y, savingData.zoom = e ? mapViewerCtrl.mapViewer.getMaxZoom() - mapViewerCtrl.mapViewer.getZoom() : "default", serviceCtrl.saveRegionPosition(savingData, $.proxy(this.saveRegionSuccess, this))
    },
    saveRegionSuccess: function(e, t, a) {
        this.exit(!0), a || viewCtrl.notify(Templates.regionSaved), viewCtrl.showTab("regionsTab", [regionsTabCtrl._lastMapObj])
    }
};
var markerTypesTabCtrl = {
        tabName: "markerTypesTab",
        $navElement: null,
        $tabElement: null,
        $controlButtons: null,
        $table: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$table = $("table", this.$tabElement), this.$controlButtons.controls(this, ["add"]), this._extraTable = this.$table.extraTable(), this.$table.on("selectionChange.extraTable", $.proxy(this.tableSelectionChange, this))
        },
        enter: function() {
            this.tableSelectionChange(), serviceCtrl.getMarkerTypes($.proxy(this.getSuccess, this))
        },
        getSuccess: function(e, t) {
            this._extraTable.setData(t)
        },
        exit: function() {
            return this._extraTable.deleteRows(), !0
        },
        add: function() {
            viewCtrl.showTab("markerTypeEditTab")
        },
        edit: function() {
            viewCtrl.showTab("markerTypeEditTab", [this._extraTable.getSelectedData().id])
        },
        remove: function() {
            var e = this;
            modalCtrl.deleteModal("Are you sure you want to delete selected marker type?<br>This will delete all markers defined for this type.", function() {
                serviceCtrl.deleteMarkerType(e._extraTable.getSelectedData().id, $.proxy(e.deleteSuccess, e))
            })
        },
        deleteSuccess: function() {
            this._extraTable.deleteRow(this._extraTable.getSelectedElement().index()), viewCtrl.notify(Templates.deleted), this.tableSelectionChange()
        },
        tableSelectionChange: function() {
            this._extraTable.getSelectedElement() ? this.$controlButtons.controls("enable") : this.$controlButtons.controls("disable")
        }
    },
    markerTypeEditTabCtrl = {
        tabName: "markerTypeEditTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        $controlButtons: null,
        $paramsTable: null,
        $defaultIconHiddenInput: null,
        paramsNum: 5,
        _validator: null,
        _extraTable: null,
        _paramsDirty: !1,
        _changesData: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$paramsTable = $("table", this.$tabElement), this.$defaultIconHiddenInput = $('input:hidden[name="defaultIcon"]', this.$formElement), this.$defaultIconHiddenInput.hiddenInput(), this._validator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$tabElement), $.proxy(this.saveForm, this)), this.$controlButtons.controls(this, ["add"]);
            var e = this;
            $('button[data-action="cancel"]', this.$formElement).click(function() {
                e.exit(!0), viewCtrl.showPrevTab()
            }), $("#mteBrowseIconBtn", this.$formElement).click(function() {
                modalCtrl.imageExplorerModal(!1, function(t) {
                    e.$defaultIconHiddenInput.hiddenInput("setObj", t)
                })
            }), $("#mteClearIconBtn", this.$formElement).click(function() {
                e.$defaultIconHiddenInput.hiddenInput("reset")
            }), this._extraTable = this.$paramsTable.extraTable({
                rowGeneratorFn: $.proxy(Templates.customParamRow, Templates)
            }), this.$paramsTable.on("selectionChange.extraTable", function() {
                e.tableSelectionChange()
            })
        },
        enter: function(e) {
            if (this.resetChangesData(), e) serviceCtrl.getMarkerType(e, !1, $.proxy(this.getSuccess, this));
            else {
                var t = {};
                t.params = [];
                for (var a = 1; a <= this.paramsNum; a++) t.params.push({
                    number: a,
                    type: "text",
                    label: ""
                });
                this.getSuccess(1, t)
            }
            viewCtrl.applyViewState(this.$tabElement, e ? "edit" : "new")
        },
        getSuccess: function(e, t) {
            this.$formElement.objectDeserialize(t), this._validator.cleanDirty(), $('select[rel="colorpicker"]', this.$tabElement).simplecolorpicker({
                picker: !0
            }), t && this._extraTable.setData(t.params), this.tableSelectionChange()
        },
        exit: function(e) {
            return !this._validator.isDirty() && !this._paramsDirty || e ? (this._validator.resetForm(), $('select[rel="colorpicker"]', this.$tabElement).simplecolorpicker("destroy"), this._extraTable.deleteRows(), this._paramsDirty = !1, this.resetChangesData(), !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        resetChangesData: function() {
            this._changesData = {
                edit: {},
                reorder: []
            }
        },
        edit: function() {
            var e = this._extraTable.getSelectedElement(),
                t = e.index(),
                a = this._extraTable.getSelectedData(),
                i = this;
            modalCtrl.paramEditModal(a, function(e) {
                i._extraTable.updateDataRow(e, t), i._changesData.edit[e.number] = e, i._paramsDirty = !0
            })
        },
        moveUp: function() {
            this._extraTable.moveUp() && this.reordered()
        },
        moveDown: function() {
            this._extraTable.moveDown() && this.reordered()
        },
        reordered: function() {
            var e = this._extraTable.getSelectedElement(),
                t = this._extraTable.getSelectedData(),
                a = this;
            $.each(this._changesData.reorder, function(e, i) {
                return i.number == t.number ? (a._changesData.reorder.splice(e, 1), !1) : void 0
            }), this._changesData.reorder.push({
                number: t.number,
                position: e.index()
            }), this._paramsDirty = !0
        },
        saveForm: function() {
            if (!this._validator.isDirty() && !this._paramsDirty) return viewCtrl.notify(Templates.noChanges);
            var e = this.$formElement.serializeObject();
            e.changesData = this._changesData, serviceCtrl.saveMarkerType(e, $.proxy(this.saveSuccess, this))
        },
        saveSuccess: function(e) {
            2 == e ? ($('input[name="cssName"]', this.$formElement).val("").focus(), viewCtrl.notify(Templates.cssInvalid, !0)) : (this.exit(!0), viewCtrl.notify(Templates.markerTypeSaved), viewCtrl.showPrevTab())
        },
        tableSelectionChange: function() {
            this._extraTable.getSelectedElement() ? this.$controlButtons.controls("enable") : this.$controlButtons.controls("disable")
        }
    },
    dictionariesTabCtrl = {
        tabName: "dictionariesTab",
        $navElement: null,
        $tabElement: null,
        $controlButtons: null,
        $table: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$table = $("table", this.$tabElement), this.$controlButtons.controls(this, ["add"]), this._extraTable = this.$table.extraTable(), this.$table.on("selectionChange.extraTable", $.proxy(this.tableSelectionChange, this))
        },
        enter: function() {
            this.tableSelectionChange(), serviceCtrl.getDictionaries($.proxy(this.getSuccess, this))
        },
        getSuccess: function(e, t) {
            this._extraTable.setData(t)
        },
        exit: function() {
            return this._extraTable.deleteRows(), !0
        },
        add: function() {
            viewCtrl.showTab("dictionaryEditTab")
        },
        edit: function() {
            viewCtrl.showTab("dictionaryEditTab", [this._extraTable.getSelectedData().id])
        },
        remove: function() {
            var e = this;
            modalCtrl.deleteModal("Are you sure you want to delete the selected dictionary?<br>This will turn off the marker-type parameters that it uses.", function() {
                serviceCtrl.deleteDictionary(e._extraTable.getSelectedData().id, $.proxy(e.deleteSuccess, e))
            })
        },
        deleteSuccess: function() {
            this._extraTable.deleteRow(this._extraTable.getSelectedElement().index()), viewCtrl.notify(Templates.deleted), this.tableSelectionChange()
        },
        tableSelectionChange: function() {
            this._extraTable.getSelectedElement() ? this.$controlButtons.controls("enable") : this.$controlButtons.controls("disable")
        }
    },
    dictionaryEditTabCtrl = {
        tabName: "dictionaryEditTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        $entryFormElement: null,
        $entryNameInput: null,
        $entrySaveBtn: null,
        $entryAddBtn: null,
        $controlButtons: null,
        $paramsTable: null,
        _formValidator: null,
        _entryFormValidator: null,
        _extraTable: null,
        _entriesDirty: !1,
        _entriesData: null,
        _addedCount: 0,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form:eq(0)", this.$tabElement), this.$entryFormElement = $("#detEntryForm", this.$tabElement), this.$entryNameInput = $("#detEntryNameInput", this.$entryFormElement), this.$entrySaveBtn = $('button[data-action="save"]', this.$entryFormElement), this.$entryAddBtn = $('button[data-action="add"]', this.$entryFormElement), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$paramsTable = $("table", this.$tabElement), this._formValidator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$tabElement), $.proxy(this.saveForm, this)), this._entryFormValidator = formValidator.validate(this.$entryFormElement), this.resetEntriesData(), this.$controlButtons.controls(this);
            var e = this;
            $('button[data-action="cancel"]', $(".panel-footer", this.$tabElement)).click(function() {
                e.exit(!0), viewCtrl.showPrevTab()
            }), $('button[data-action="save"]', $(".panel-footer", this.$tabElement)).click(function() {
                e.$formElement.submit()
            }), this.$entryAddBtn.add(this.$entrySaveBtn).click(function() {
                if ($butt = $(this), !$butt.hasClass("disabled")) switch ($butt.data("action")) {
                    case "save":
                        return e.saveValue(), void 0;
                    case "add":
                        return e.addValue(), void 0
                }
            }), this.$entryNameInput.keypress(function(t) {
                var a = t.keyCode ? t.keyCode : t.which;
                "13" == a && (t.preventDefault(), e.$entrySaveBtn.hasClass("disabled") ? e.$entryAddBtn.triggerHandler("click") : e.$entrySaveBtn.triggerHandler("click"))
            }), this._extraTable = this.$paramsTable.extraTable({
                rowGeneratorFn: $.proxy(Templates.dictionaryEntryRow, Templates)
            }), this.$paramsTable.on("selectionChange.extraTable", function() {
                e.tableSelectionChange()
            })
        },
        enter: function(e) {
            e ? serviceCtrl.getDictionary(e, $.proxy(this.getSuccess, this)) : this.getSuccess(), viewCtrl.applyViewState(this.$tabElement, e ? "edit" : "new")
        },
        getSuccess: function(e, t) {
            this.$formElement.objectDeserialize(t), this._formValidator.cleanDirty(), t && this._extraTable.setData(t.entries), this.tableSelectionChange()
        },
        exit: function(e) {
            return !this._formValidator.isDirty() && !this._entriesDirty || e ? (this._formValidator.resetForm(), this._entryFormValidator.resetForm(), this._extraTable.deleteRows(), this._entriesDirty = !1, this.resetEntriesData(), this._addedCount = 0, !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        resetEntriesData: function() {
            this._entriesData = {
                add: {},
                remove: {},
                edit: {}
            }
        },
        tableSelectionChange: function() {
            var e = this._extraTable.getSelectedData();
            e ? (this._entryFormValidator.resetForm(), this.$controlButtons.controls("enable"), this.$entryNameInput.val(e.value), this.$entrySaveBtn.removeClass("disabled")) : (this.$controlButtons.controls("disable"), this.$entrySaveBtn.addClass("disabled"))
        },
        addValue: function() {
            if (this.$entryFormElement.valid()) {
                var e = {
                    id: "new " + ++this._addedCount,
                    value: this.$entryNameInput.val()
                };
                this._extraTable.addDataRow(e), this.$entryNameInput.val("").focus(), this._entriesData.add[e.id] = e, this._entriesDirty = !0
            }
        },
        saveValue: function() {
            if (this.$entryFormElement.valid()) {
                var e = this._extraTable.getSelectedElement().index(),
                    t = this._extraTable.getSelectedData(),
                    a = {
                        id: t.id,
                        value: this.$entryNameInput.val()
                    };
                this._extraTable.updateDataRow(a, e), this.$entryNameInput.val(""), this._entriesData.add[t.id] ? this._entriesData.add[t.id] = a : this._entriesData.edit[t.id] = a, this._entriesDirty = !0, this.tableSelectionChange()
            }
        },
        remove: function() {
            var e = this._extraTable.getSelectedData();
            this._entriesData.add[e.id] ? delete this._entriesData.add[e.id] : this._entriesData.remove[e.id] = e, delete this._entriesData.edit[e.id], this._extraTable.deleteRow(this._extraTable.getSelectedElement().index()), this.tableSelectionChange(), this._entriesDirty = !0
        },
        saveForm: function() {
            if (!this._formValidator.isDirty() && !this._entriesDirty) return viewCtrl.notify(Templates.noChanges);
            var e = this.$formElement.serializeObject();
            console.log(this._entriesData), e.entriesData = this._entriesData, serviceCtrl.saveDictionary(e, $.proxy(this.saveSuccess, this))
        },
        saveSuccess: function() {
            this.exit(!0), viewCtrl.notify(Templates.dictionarySaved), viewCtrl.showPrevTab()
        }
    },
    markersTabCtrl = {
        tabName: "markersTab",
        $navElement: null,
        $tabElement: null,
        $controlButtons: null,
        $mapHiddenInput: null,
        $dataTableInstance: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$mapHiddenInput = $('input:hidden[name="map"]', this.$tabElement), this.$mapHiddenInput.hiddenInput({
                labelTemplate: Templates.breadcrumb
            }), this.$controlButtons.controls(this, ["add"]);
            var e = this;
            $('button[data-action="select-map"]', this.$tabElement).click(function() {
                modalCtrl.treeModal(function(t) {
                    e.$mapHiddenInput.hiddenInput("setObj", t), e.$dataTableInstance.filterByMapId(t.id)
                })
            }), $('button[data-action="clear-map"]', this.$tabElement).click(function() {
                e.$mapHiddenInput.hiddenInput("setObj", null), e.$dataTableInstance.clearMapFilter()
            })
        },
        enter: function(e) {
            var t = function(e, t) {
                $("td", e).each(function(e) {
                    $td = $(this), 4 == e && $td.html(Templates.onOffIcon(t[e])).css("text-align", "center")
                })
            };
            this.$dataTableInstance = dataTableUtil.create($("table", this.$tabElement), $(".form-search", this.$tabElement), 1, "getMarkers", 1, t, 3, e && e.id), this.$controlButtons.controls("disable"), this.$mapHiddenInput.hiddenInput("setObj", e);
            var a = this;
            this.$dataTableInstance.on("selectionChange", function() {
                a.$dataTableInstance.selectedObj ? (a.$controlButtons.controls("enable"), parseInt(a.$dataTableInstance.selectedObj[4]) || a.$controlButtons.controls("disable", ["move"])) : a.$controlButtons.controls("disable")
            })
        },
        exit: function() {
            return this.$mapHiddenInput.hiddenInput("setObj", null), this.$dataTableInstance.off("selectionChange"), this.$dataTableInstance.destroy(), !0
        },
        add: function() {
            viewCtrl.showTab("markerEditTab")
        },
        edit: function() {
            viewCtrl.showTab("markerEditTab", [this.$dataTableInstance.selectedObj[0]])
        },
        move: function() {
            var e = $(this.$dataTableInstance.selectedObj[3]).data("id");
            viewCtrl.showTab("mapViewTab", [e, "marker", this.$dataTableInstance.selectedObj[0]])
        },
        remove: function() {
            var e = this;
            modalCtrl.deleteModal("Are you sure you wanna delete selected marker?", function() {
                serviceCtrl.deleteMarker(e.$dataTableInstance.selectedObj[0], $.proxy(e.deleteSuccess, e))
            })
        },
        deleteSuccess: function() {
            this.$dataTableInstance.fnDeleteRow(1, null, !0), viewCtrl.notify(Templates.deleted)
        }
    },
    markerEditTabCtrl = {
        tabName: "markerEditTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        $typeSelect: null,
        $paramInputsContainer: null,
        $imageHiddenInput: null,
        $iconHiddenInput: null,
        $mapHiddenInput: null,
        $regionHiddenInput: null,
        _validator: null,
        _markerObj: null,
        _markerTypeObj: null,
        _dictEntriesData: null,
        _deserialized: !1,
        _positionAfterSave: null,
        _savingData: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this.$typeSelect = $("#metTypeSelect", this.$formElement), this.$paramInputsContainer = $("#metParamInputs", this.$formElement), this.$imageHiddenInput = $('input:hidden[name="image"]', this.$formElement), this.$iconHiddenInput = $('input:hidden[name="icon"]', this.$formElement), this.$mapHiddenInput = $('input:hidden[name="map"]', this.$formElement), this.$regionHiddenInput = $('input:hidden[name="region"]', this.$formElement), this._validator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$tabElement), $.proxy(this.saveForm, this)), this.$imageHiddenInput.hiddenInput(), this.$mapHiddenInput.hiddenInput({
                labelTemplate: Templates.breadcrumb
            }), this.$regionHiddenInput.hiddenInput({
                labelTemplate: Templates.regionName
            }), this.$typeSelect.change($.proxy(this.typeChange, this));
            var e = this;
            $('button[data-action="cancel"]', this.$formElement).click(function() {
                e.exit(!0), viewCtrl.showPrevTab()
            }), $('button[data-action="save-and-position"]', this.$formElement).click(function() {
                e.$formElement.valid() && (e._positionAfterSave = !0, e.saveForm() || e.saveSuccess(null, null, !0))
            }), $('button[data-action="select-map"]', this.$formElement).click(function() {
                modalCtrl.treeModal(function(t) {
                    e.$mapHiddenInput.hiddenInput("setObj", t), e.$regionHiddenInput.hiddenInput("setObj", null)
                })
            }), $('button[data-action="select-region"]', this.$formElement).click(function() {
                var t = e.$mapHiddenInput.hiddenInput("getObj");
                return t ? (modalCtrl.regionsModal(t, function(t) {
                    t.name = $.unescapifyHTML(t.name), e.$regionHiddenInput.hiddenInput("setObj", t)
                }), void 0) : modalCtrl.messageModal("select-map")
            }), $('button[data-action="clear-region"]', this.$formElement).click(function() {
                e.$regionHiddenInput.hiddenInput("setObj", null)
            }), $("#metBrowseImageBtn", this.$formElement).click(function() {
                modalCtrl.imageExplorerModal(!0, function(t) {
                    e.$imageHiddenInput.hiddenInput("setObj", t)
                })
            }), $("#metClearImageBtn", this.$formElement).click(function() {
                e.$imageHiddenInput.hiddenInput("reset")
            }), $("#metBrowseIconBtn", this.$formElement).click(function() {
                modalCtrl.imageExplorerModal(!1, function(t) {
                    e.$iconHiddenInput.hiddenInput("setObj", t)
                })
            }), $("#metDefaultIconBtn", this.$formElement).click(function() {
                e.$iconHiddenInput.hiddenInput("reset")
            })
        },
        enter: function(e, t) {
            var a = this;
            serviceCtrl.getMarkerTypes(function(i, n) {
                a.getTypesSuccess(i, n), n && (e ? serviceCtrl.getMarker(e, $.proxy(a.getMarkerSuccess, a)) : (a.$mapHiddenInput.hiddenInput("setObj", t), a.typeChange()))
            }), viewCtrl.applyViewState(this.$tabElement, e ? "edit" : "new")
        },
        getTypesSuccess: function(e, t) {
            var a = this;
            $.each(t, function(e, t) {
                a.$typeSelect.append(Templates.selectOption(t.id, t.name))
            })
        },
        getMarkerSuccess: function(e, t) {
            this._markerObj = t.marker, this.$typeSelect.val(this._markerObj.markerTypeId), this.typeChange()
        },
        getTypeSuccess: function(e, t) {
            this._markerTypeObj = t;
            var a;
            this.$iconHiddenInput.data("hiddenInput") && (a = this.$iconHiddenInput.hiddenInput("getObj"), this.$iconHiddenInput.hiddenInput("destroy")), this.$iconHiddenInput.data("labelFalseyVal", this._markerTypeObj.defaultIcon.url), this.$iconHiddenInput.hiddenInput(), this.$iconHiddenInput.hiddenInput("setObj", a);
            var i = [];
            this._markerTypeObj.params && $.each(this._markerTypeObj.params, function(e, t) {
                "dictionary" == t.type && i.push(t.typeValue)
            }), i.length ? serviceCtrl.getDictionariesEntries(i, $.proxy(this.getDictionariesEntriesSuccess, this)) : this.createParamInputs()
        },
        getDictionariesEntriesSuccess: function(e, t) {
            this._dictEntriesData = t, this.createParamInputs()
        },
        exit: function(e) {
            return !this._validator.isDirty() || e ? (this.$iconHiddenInput.hiddenInput("destroy"), this.$typeSelect.empty(), this.$paramInputsContainer.empty(), this._markerObj = null, this._markerTypeObj = null, this._dictEntriesData = null, this._validator.resetForm(), this._deserialized = !1, this._savingData = null, !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        typeChange: function() {
            serviceCtrl.getMarkerType(this.$typeSelect.val(), !0, $.proxy(this.getTypeSuccess, this))
        },
        createParamInputs: function() {
            var e = this;
            this.$paramInputsContainer.empty(), this._markerTypeObj.params && $.each(this._markerTypeObj.params, function(t, a) {
                if ("dictionary" == a.type) {
                    var i = e._dictEntriesData ? e._dictEntriesData[a.typeValue] : null;
                    return e.$paramInputsContainer.append(Templates.paramControlGroup(a, i)), void 0
                }
                if (e.$paramInputsContainer.append(Templates.paramControlGroup(a)), "link" == a.type) {
                    var n = e.$formElement.find('[name="param' + a.number + 'Value-multi-0"]'),
                        l = e.$formElement.find('[name="param' + a.number + 'Value-multi-1"]');
                    n.rules("add", {
                        required: {
                            depends: function() {
                                return "" != l.val().trim()
                            }
                        }
                    }), l.rules("add", {
                        required: {
                            depends: function() {
                                return "" != n.val().trim()
                            }
                        }
                    })
                }
            }), !this._deserialized && this._markerObj && (this.$formElement.objectDeserialize(this._markerObj), this._validator.cleanDirty(), this._deserialized = !0)
        },
        saveForm: function() {
            return this._savingData = this.$formElement.serializeObject(), this._validator.isDirty() ? (serviceCtrl.saveMarker(this._savingData, $.proxy(this.saveSuccess, this)), !0) : viewCtrl.notify(Templates.noChanges)
        },
        saveSuccess: function(e, t, a) {
            var i = this._savingData.map,
                n = this._savingData.enabled,
                l = t ? t : this._savingData.id;
            a || viewCtrl.notify(Templates.markerSaved), this.exit(!0), this._positionAfterSave && n ? viewCtrl.showTab("mapViewTab", [i, "marker", l]) : viewCtrl.showPrevTab(), this._positionAfterSave && !n && viewCtrl.notify(Templates.notEnabled), this._positionAfterSave = null
        }
    },
    labelsTabCtrl = {
        tabName: "labelsTab",
        $navElement: null,
        $tabElement: null,
        $mapHiddenInput: null,
        $dataTableInstance: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$mapHiddenInput = $('input:hidden[name="map"]', this.$tabElement), this.$mapHiddenInput.hiddenInput({
                labelTemplate: Templates.breadcrumb
            }), this.$controlButtons.controls(this, ["add"]);
            var e = this;
            $('button[data-action="select-map"]', this.$tabElement).click(function() {
                modalCtrl.treeModal(function(t) {
                    e.$mapHiddenInput.hiddenInput("setObj", t), e.$dataTableInstance.filterByMapId(t.id)
                })
            }), $('button[data-action="clear-map"]', this.$tabElement).click(function() {
                e.$mapHiddenInput.hiddenInput("setObj", null), e.$dataTableInstance.clearMapFilter()
            })
        },
        enter: function(e) {
            var t = function(e, t) {
                $("td", e).each(function(e) {
                    $td = $(this), 4 == e && $td.html(Templates.onOffIcon(t[e])).css("text-align", "center")
                })
            };
            this.$dataTableInstance = dataTableUtil.create($("table", this.$tabElement), $(".form-search", this.$tabElement), 1, "getLabels", 1, t, 3, e && e.id), this.$controlButtons.controls("disable"), this.$mapHiddenInput.hiddenInput("setObj", e);
            var a = this;
            this.$dataTableInstance.on("selectionChange", function() {
                a.$dataTableInstance.selectedObj ? (a.$controlButtons.controls("enable"), parseInt(a.$dataTableInstance.selectedObj[4]) || a.$controlButtons.controls("disable", ["move"])) : a.$controlButtons.controls("disable")
            })
        },
        exit: function() {
            return this.$mapHiddenInput.hiddenInput("setObj", null), this.$dataTableInstance.off("selectionChange"), this.$dataTableInstance.destroy(), !0
        },
        add: function() {
            viewCtrl.showTab("labelEditTab")
        },
        edit: function() {
            viewCtrl.showTab("labelEditTab", [this.$dataTableInstance.selectedObj[0]])
        },
        move: function() {
            var e = $(this.$dataTableInstance.selectedObj[3]).data("id");
            viewCtrl.showTab("mapViewTab", [e, "label", this.$dataTableInstance.selectedObj[0]])
        },
        remove: function() {
            var e = this;
            modalCtrl.deleteModal("Are you sure you want delete the selected label?", function() {
                serviceCtrl.deleteLabel(e.$dataTableInstance.selectedObj[0], $.proxy(e.deleteSuccess, e))
            })
        },
        deleteSuccess: function() {
            this.$dataTableInstance.fnDeleteRow(1, null, !0), viewCtrl.notify(Templates.deleted)
        }
    },
    labelEditTabCtrl = {
        tabName: "labelEditTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        $iconHiddenInput: null,
        $mapHiddenInput: null,
        $linkMapHiddenInput: null,
        $regionHiddenInput: null,
        _validator: null,
        _positionAfterSave: null,
        _savingData: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this.$mapHiddenInput = $('input:hidden[name="map"]', this.$formElement), this.$linkMapHiddenInput = $('input:hidden[name="linkMap"]', this.$formElement), this.$regionHiddenInput = $('input:hidden[name="linkRegion"]', this.$formElement), this.$iconHiddenInput = $('input:hidden[name="icon"]', this.$formElement), this._validator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$tabElement), $.proxy(this.saveForm, this)), this.$iconHiddenInput.hiddenInput(), this.$mapHiddenInput.hiddenInput({
                labelTemplate: function(e) {
                    return Templates.breadcrumb(e)
                }
            }), this.$linkMapHiddenInput.hiddenInput({
                labelTemplate: function(e) {
                    return Templates.breadcrumb(e)
                }
            }), this.$regionHiddenInput.hiddenInput({
                labelTemplate: Templates.regionName
            });
            var e = this;
            $("#letBrowseIconBtn", this.$formElement).click(function() {
                modalCtrl.imageExplorerModal(!1, function(t) {
                    e.$iconHiddenInput.hiddenInput("setObj", t)
                })
            }), $('button[data-action="cancel"]', this.$formElement).click(function() {
                e.exit(!0), viewCtrl.showPrevTab()
            }), $('button[data-action="save-and-position"]', this.$formElement).click(function() {
                e.$formElement.valid() && (e._positionAfterSave = !0, e.saveForm() || e.saveSuccess(null, null, !0))
            }), $('button[data-action="select-map"]', this.$formElement).click(function() {
                modalCtrl.treeModal(function(t) {
                    e.$mapHiddenInput.hiddenInput("setObj", t)
                })
            }), $('button[data-action="select-link-map"]', this.$formElement).click(function() {
                modalCtrl.treeModal(function(t) {
                    e.$linkMapHiddenInput.hiddenInput("setObj", t), e.$regionHiddenInput.hiddenInput("setObj", null)
                })
            }), $('button[data-action="clear-link-map"]', this.$formElement).click(function() {
                e.$linkMapHiddenInput.hiddenInput("setObj", null), e.$regionHiddenInput.hiddenInput("setObj", null)
            }), $('button[data-action="select-region"]', this.$formElement).click(function() {
                var t = e.$linkMapHiddenInput.hiddenInput("getObj");
                t || modalCtrl.messageModal("select-map"), modalCtrl.regionsModal(t, function(t) {
                    t.name = $.unescapifyHTML(t.name), e.$regionHiddenInput.hiddenInput("setObj", t)
                })
            }), $('button[data-action="clear-region"]', this.$formElement).click(function() {
                e.$regionHiddenInput.hiddenInput("setObj", null)
            }), $('input[name="type"]', this.$formElement).change(e.labelTypeChange)
        },
        enter: function(e, t) {
            e ? serviceCtrl.getLabel(e, $.proxy(this.getSuccess, this)) : (this.$mapHiddenInput.hiddenInput("setObj", t), this.getSuccess()), viewCtrl.applyViewState(this.$tabElement, e ? "edit" : "new")
        },
        getSuccess: function(e, t) {
            this.$formElement.objectDeserialize(t), this._validator.cleanDirty(), this.labelTypeChange()
        },
        exit: function(e) {
            return !this._validator.isDirty() || e ? (this._validator.resetForm(), this._savingData = null, !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        labelTypeChange: function() {
            var e = $('input:radio[name="type"]:checked', this.$formElement).val(),
                t = $("#letIconTypeInputControl"),
                a = $("#letTextTypeInputControl");
            switch (t.addClass("hide"), a.addClass("hide"), $(":input", this.$formElement).removeClass("val-ignored"), e) {
                case "icon":
                    t.removeClass("hide"), $("textarea", a).addClass("val-ignored");
                    break;
                case "text":
                    a.removeClass("hide"), $("input", t).addClass("val-ignored");
                    break;
                default:
                    throw "radio value unknown"
            }
        },
        saveForm: function() {
            return this._savingData = this.$formElement.serializeObject(), this._validator.isDirty() ? ("text" == this._savingData.type ? delete this._savingData.icon : delete this._savingData.text, serviceCtrl.saveLabel(this._savingData, $.proxy(this.saveSuccess, this)), !0) : viewCtrl.notify(Templates.noChanges)
        },
        saveSuccess: function(e, t, a) {
            var i = this._savingData.map,
                n = this._savingData.enabled,
                l = t ? t : this._savingData.id;
            a || viewCtrl.notify(Templates.labelSaved), this.exit(!0), this._positionAfterSave && n ? viewCtrl.showTab("mapViewTab", [i, "label", l]) : viewCtrl.showPrevTab(), this._positionAfterSave && !n && viewCtrl.notify(Templates.notEnabled), this._positionAfterSave = null
        }
    },
    regionsTabCtrl = {
        tabName: "regionsTab",
        $navElement: null,
        $tabElement: null,
        $controlButtons: null,
        $table: null,
        _extraTable: null,
        _lastMapObj: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$controlButtons = $(".btn-group:eq(0)", this.$tabElement), this.$table = $("table", this.$tabElement), this.$controlButtons.controls(this, ["add"]), this._extraTable = this.$table.extraTable({
                rowGeneratorFn: $.proxy(Templates.regionRow, Templates)
            }), this.$table.on("selectionChange.extraTable", $.proxy(this.tableSelectionChange, this));
            var e = this;
            $('button[data-action="cancel"]', this.$tabElement).click(function() {
                e.exit(!0), viewCtrl.showPrevTab()
            })
        },
        enter: function(e) {
            this._lastMapObj = e, $(".panel-title", this.$tabElement).text(Templates.regionsHeader(this._lastMapObj.name)), this.tableSelectionChange(), serviceCtrl.getRegions(this._lastMapObj.id, $.proxy(this.getSuccess, this))
        },
        getSuccess: function(e, t) {
            this._extraTable.setData(t)
        },
        exit: function() {
            return this._extraTable.deleteRows(), !0
        },
        add: function() {
            viewCtrl.showTab("regionEditTab")
        },
        edit: function() {
            viewCtrl.showTab("regionEditTab", [this._extraTable.getSelectedData().id])
        },
        move: function() {
            viewCtrl.showTab("mapViewTab", [this._lastMapObj.id, "region", this._extraTable.getSelectedData().id])
        },
        remove: function() {
            if (this._orderDirty) return modalCtrl.messageModal("reordered"), void 0;
            var e = this;
            modalCtrl.deleteModal("Are you sure you wanna delete selected region.", function() {
                var t = e._extraTable.getSelectedData().id;
                serviceCtrl.deleteRegion(t, $.proxy(e.deleteSuccess, e))
            })
        },
        deleteSuccess: function() {
            this._extraTable.deleteRow(this._extraTable.getSelectedElement().index()), viewCtrl.notify(Templates.deleted), this.tableSelectionChange()
        },
        tableSelectionChange: function() {
            this._extraTable.getSelectedElement() ? this.$controlButtons.controls("enable") : this.$controlButtons.controls("disable")
        }
    },
    regionEditTabCtrl = {
        tabName: "regionEditTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        _validator: null,
        _savingData: null,
        _positionAfterSave: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this._validator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$tabElement), $.proxy(this.saveForm, this));
            var e = this;
            $('button[data-action="cancel"]', this.$formElement).click(function() {
                e.exit(!0), e._positionAfterSave = null, viewCtrl.showPrevTab()
            }), $('button[data-action="save-and-position"]', this.$formElement).click(function() {
                e.$formElement.valid() && (e._positionAfterSave = !0, e.saveForm() || e.saveSuccess(null, null, !0))
            })
        },
        enter: function(e) {
            e ? serviceCtrl.getRegion(e, $.proxy(this.getSuccess, this)) : this.getSuccess(), viewCtrl.applyViewState(this.$tabElement, e ? "edit" : "new")
        },
        getSuccess: function(e, t) {
            this.$formElement.objectDeserialize(t), this._validator.cleanDirty()
        },
        exit: function(e) {
            return !this._validator.isDirty() || e ? (this._validator.resetForm(), this._savingData = null, !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        saveForm: function() {
            return this._savingData = this.$formElement.serializeObject(), this._savingData.mapId = regionsTabCtrl._lastMapObj.id, this._validator.isDirty() ? (serviceCtrl.saveRegion(this._savingData, $.proxy(this.saveSuccess, this)), !0) : viewCtrl.notify(Templates.noChanges)
        },
        saveSuccess: function(e, t, a) {
            a || viewCtrl.notify(Templates.regionSaved), this.exit(!0), this._positionAfterSave ? viewCtrl.showTab("mapViewTab", [regionsTabCtrl._lastMapObj.id, "region", t]) : viewCtrl.showPrevTab(), this._positionAfterSave = null
        }
    },
    settingsTabCtrl = {
        tabName: "settingsTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        $typeSelect: null,
        _validator: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this.$typeSelect = $('select[name="defaultMarkerType"]', this.$formElement), this._validator = formValidator.validate(this.$formElement, null, $.proxy(this.saveForm, this))
        },
        enter: function() {
            serviceCtrl.getMarkerTypes($.proxy(this.getTypesSuccess, this))
        },
        getTypesSuccess: function(e, t) {
            var a = this;
            this.$typeSelect.append(Templates.selectOption(0, "all")), t && $.each(t, function(e, t) {
                a.$typeSelect.append(Templates.selectOption(t.id, t.name))
            }), this._validator.cleanDirty(), serviceCtrl.getSettings($.proxy(this.getSettingsSuccess, this))
        },
        getSettingsSuccess: function(e, t) {
            this.$formElement.objectDeserialize(t), this._validator.cleanDirty()
        },
        exit: function(e) {
            return !this._validator.isDirty() || e ? (this._validator.cleanDirty(), this.$typeSelect.empty(), !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        saveForm: function() {
            return this._validator.isDirty() ? (serviceCtrl.saveSettings(this.$formElement.serializeObject(), $.proxy(this.saveSuccess, this)), void 0) : viewCtrl.notify(Templates.noChanges)
        },
        saveSuccess: function() {
            this._validator.cleanDirty(), viewCtrl.notify(Templates.settingsSaved)
        }
    },
    passwordTabCtrl = {
        tabName: "passwordTab",
        $navElement: null,
        $tabElement: null,
        $formElement: null,
        _validator: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), this.$formElement = $("form", this.$tabElement), this._validator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$tabElement), $.proxy(this.saveForm, this))
        },
        enter: function() {
            this._validator.cleanDirty()
        },
        exit: function(e) {
            return !this._validator.isDirty() || e ? (this._validator.resetForm(), !0) : (modalCtrl.unsavedModal($.proxy(function() {
                this.exit(!0), viewCtrl.tabChangeProceed()
            }, this)), !1)
        },
        saveForm: function() {
            return this._validator.isDirty() ? (serviceCtrl.changePassword(this.$formElement.serializeObject(), $.proxy(this.saveSuccess, this)), void 0) : viewCtrl.notify(Templates.noChanges)
        },
        saveSuccess: function(e) {
            2 == e ? ($('input[name="oldPassword"]', this.$formElement).val("").focus(), viewCtrl.notify(Templates.oldPasswordInvalid, !0)) : (this.exit(!0), viewCtrl.notify(Templates.passwordChanged))
        }
    },
    logoutTabCtrl = {
        tabName: "logoutTab",
        $navElement: null,
        $tabElement: null,
        init: function() {
            this.$navElement = $('li a[href="#' + this.tabName + '"]', viewCtrl.$mainNavigation), this.$tabElement = $("#" + this.tabName), $("#ltLoginAgain").click(function() {
                location.reload()
            })
        },
        enter: function() {
            $(".navbar.navbar-fixed-top").addClass("hide"), window.location.hash = "", serviceCtrl.logout()
        },
        exit: function() {
            return !0
        }
    },
    utils = {
        isFunction: function(e) {
            var t = {};
            return e && "[object Function]" === t.toString.call(e)
        }
    },
    paramEditModalCtrl = {
        $modalElement: null,
        $formElement: null,
        $typeValueSelect: null,
        lastData: null,
        lastCallback: null,
        _validator: null,
        init: function() {
            var e = this;
            return this.$modalElement = $("#paramEditModal"), this.$formElement = $("form", this.$modalElement), this.$typeValueSelect = $('select[name="typeValue"]', this.$formElement), this._validator = formValidator.validate(this.$formElement, $(".alert.alert-error", this.$modalElement), $.proxy(this.saveForm, this)), $("#pemTypeSelect", this.$formElement).change(function() {
                e.updateViewToType()
            }), this.$modalElement.on("hidden", function(t) {
                e.$modalElement[0] == t.target && e.exit()
            }), this.$formElement.on("click.paramEditModalCtrl", ':checkbox[readonly="readonly"]', function(e) {
                return e.preventDefault(), !1
            }), this
        },
        showModal: function(e, t) {
            this.lastData = e, this.$formElement.objectDeserialize(this.lastData), this.lastCallback = t, this.$modalElement.modal("show"), serviceCtrl.getDictionaries($.proxy(this.getDictsSuccess, this))
        },
        getDictsSuccess: function(e, t) {
            var a = this;
            $.each(t, function(e, t) {
                a.$typeValueSelect.append(Templates.selectOption(t.id, t.name))
            }), this.updateViewToType(), this._validator.cleanDirty()
        },
        saveForm: function() {
            if (this._validator.isDirty() && this.lastCallback) {
                var e = this.$formElement.serializeObject();
                "dictionary" != e.type && delete e.typeValue, this.lastCallback(e)
            }
            this.$modalElement.modal("hide")
        },
        updateViewToType: function() {
            var e = $("#pemTypeSelect", this.$formElement),
                t = $("#pemShowLabelCbx"),
                a = $("#pemDictionaryControlGroup", this.$formElement);
            switch (t.prop("readonly", ""), a.addClass("hide"), this.$typeValueSelect.addClass("val-ignored"), e.val()) {
                case "longText":
                    t.prop("readonly", "readonly"), t.prop("checked", !0);
                    break;
                case "dictionary":
                    this.$typeValueSelect.val(this.lastData.typeValue), a.removeClass("hide"), this.$typeValueSelect.removeClass("val-ignored")
            }
        },
        exit: function() {
            this.lastData = null, this.$typeValueSelect.empty(), this._validator.resetForm(), this.$formElement.off(".paramEditModalCtrl"), this.lastCallback = null
        }
    },
    uploadModalCtrl = {
        $modalElement: null,
        $uploadElement: null,
        $uploadButton: null,
        init: function() {
            this.$modalElement = $("#uploadModal"), this.$uploadElement = $('div[data-action="upload-container"]', this.$modalElement), this.$uploadButton = $('a[data-action="browse"]', this.$modalElement);
            var e = this;
            return $('button[data-action="close"]', this.$modalElement).click(function() {
                e.$uploadElement.fineUploader("getInProgress") > 0 ? modalCtrl.messageModal("uploading") : e.$modalElement.modal("hide")
            }), this.$modalElement.on("hidden", function() {
                e.exit()
            }), this
        },
        showModal: function(e, t, a) {
            this.createUploader();
            var i = this.$uploadElement.data("fineuploader").uploader._options;
            if ("icon" == e || "image" == e) i.multiple = !0, i.request.endpoint = "icon" == e ? serviceCtrl.uploadIconsPath : serviceCtrl.uploadImagesPath, i.request.params = null, this.multiple(!0), this.$uploadElement.off("submit complete");
            else {
                if ("map" != e) throw "Unkonown upload modal mode.";
                i.multiple = !1, i.request.endpoint = serviceCtrl.uploadMapPath, i.request.params = {
                    id: t
                }, this.multiple(!1);
                var n = this;
                this.$uploadElement.on("submit", function() {
                    n.$uploadButton.css("visibility", "hidden")
                }).on("complete", function(e, t, i, l) {
                    serviceCtrl.clearCache("index"), n.$uploadButton.css("visibility", "visible"), l.success ? (n.$modalElement.modal("hide"), a && a(l)) : n.multiple(!1)
                })
            }
            viewCtrl.applyViewState(this.$modalElement, e), this.$modalElement.modal("show")
        },
        multiple: function(e) {
            this.$uploadButton.find('input[type="file"]').prop("multiple", e)
        },
        createUploader: function() {
            this.$uploadElement.data("fineuploader") || this.$uploadElement.fineUploader({
                request: {
                    paramsInBody: !0
                },
                validation: {
                    allowedExtensions: ["jpeg", "jpg", "png", "gif"]
                },
                maxConnections: 2,
                failedUploadTextDisplay: {
                    mode: "custom",
                    maxChars: 999,
                    responseProperty: "error"
                },
                button: this.$uploadButton,
                template: '<div class="qq-uploader"><pre class="qq-upload-drop-area "></pre><span class="qq-drop-processing"></span><ul class="qq-upload-list" style="margin-top: 10px; text-align: center;"></ul></div>',
                fileTemplate: '<li><div class="qq-progress-bar"></div><span class="qq-upload-spinner"></span><span class="qq-upload-finished"></span><span class="qq-upload-file"></span><span class="qq-upload-size"></span><div class="qq-upload-status-text">{statusText}</div><a class="qq-upload-cancel btn btn-mini" href="#">{cancelButtonText}</a></li>',
                classes: {
                    success: "alert alert-success",
                    fail: "alert alert-error"
                },
                debug: !1
            })
        },
        exit: function() {
            this.$uploadElement.off("complete"), this.$uploadElement.fineUploader("reset")
        }
    },
    treeModalCtrl = {
        $modalElement: null,
        lastCallback: null,
        init: function() {
            this.$modalElement = $("#treeModal");
            var e = this;
            return $('button[data-action="select"]', this.$modalElement).click(function() {
                var t = treeCtrl.getSelectedObj();
                e.validateSelection(t) && (e.applySelection(t), e.$modalElement.modal("hide"))
            }), this.$modalElement.on("hidden", function() {
                e.exit()
            }), this
        },
        showModal: function(e) {
            this.lastCallback = e, treeCtrl.disableDrag(), treeCtrl.$treeElement.detach().appendTo($(".tree-holder", this.$modalElement)), treeCtrl.$treeElement.jstree("deselect_all"), this.$modalElement.modal("show"), treeCtrl.refresh()
        },
        validateSelection: function(e) {
            return e && e.noMap ? (modalCtrl.messageModal("map-noMap"), !1) : e && !e.enabled ? (modalCtrl.messageModal("map-disabled"), !1) : !0
        },
        applySelection: function(e) {
            this.lastCallback && e && (this.lastCallback(e), this.lastCallback = null)
        },
        exit: function() {
            treeCtrl.$treeElement.jstree("deselect_all"), this.lastCallback = null
        }
    },
    regionsModalCtrl = {
        $modalElement: null,
        $table: null,
        lastCallback: null,
        _extraTable: null,
        init: function() {
            this.$modalElement = $("#regionsModal"), this.$table = $("table", this.$modalElement), this._extraTable = this.$table.extraTable({
                rowGeneratorFn: function(e) {
                    return "<tr><td>" + e.id + "</td><td>" + e.name + "</td></tr>"
                }
            }), this.$table.on("selectionChange.extraTable", $.proxy(this.tableSelectionChange, this));
            var e = this;
            return $('button[data-action="select"]', this.$modalElement).click(function() {
                e.applySelection(), e.$modalElement.modal("hide")
            }), this.$modalElement.on("hidden", function() {
                e.exit()
            }), this
        },
        showModal: function(e, t) {
            this.lastCallback = t, $(".modal-header h3", this.$modalElement).text(Templates.regionsHeader(e.name)), this.$modalElement.modal("show"), serviceCtrl.getRegions(e.id, $.proxy(this.getSuccess, this))
        },
        getSuccess: function(e, t) {
            this._extraTable.setData(t)
        },
        applySelection: function() {
            var e = this._extraTable.getSelectedData();
            this.lastCallback && e && (this.lastCallback(e), this.lastCallback = null)
        },
        exit: function() {
            this._extraTable.deleteRows(), this.lastCallback = null
        }
    },
    imageExplorerModalCtrl = {
        $modalElement: null,
        $controlButtons: null,
        lastCallback: null,
        mode: null,
        init: function() {
            var e = this;
            return this.$modalElement = $("#imageExplorerModal"), this.$controlButtons = $(".btn-group", this.$modalElement), this.$controlButtons.controls(this), this.$modalElement.on("click", ".thumbnails a", function() {
                $("ul.thumbnails li", this.$modalElement).removeClass("thumb-selected"), $(this).closest("li").addClass("thumb-selected"), e.selectionChange()
            }), $('button[data-action="select"]', this.$modalElement).click(function() {
                e.applySelection()
            }), this.$modalElement.on("hidden", function() {
                e.exit()
            }), this
        },
        showModal: function(e, t, a) {
            this.lastCallback = t, this.mode = e ? "images" : "icons", a ? $('button[data-action="cancel"]', this.$modalElement).addClass("hide") : $('button[data-action="cancel"]', this.$modalElement).removeClass("hide"), viewCtrl.applyViewState(this.$modalElement, this.mode), this.$modalElement.modal("show"), "images" == this.mode ? serviceCtrl.getImages($.proxy(this.getDataSuccess, this)) : serviceCtrl.getIcons($.proxy(this.getDataSuccess, this))
        },
        getDataSuccess: function(e, t) {
            t && $.each(t, function(e, t) {
                var a = $(Templates.thumbnail(t));
                a.data("model", t).appendTo($("ul.thumbnails", this.$modalElement))
            }), this.selectionChange()
        },
        exit: function() {
            $("ul.thumbnails", this.$modalElement).empty(), this.lastCallback = null, this.mode = null
        },
        selectionChange: function() {
            this.getSelectedObj() ? this.$controlButtons.controls("enable") : this.$controlButtons.controls("disable")
        },
        getSelectedElement: function() {
            return $("ul.thumbnails li.thumb-selected", this.$modalElement)
        },
        getSelectedObj: function() {
            return this.getSelectedElement().data("model")
        },
        applySelection: function() {
            var e = this.getSelectedObj();
            this.lastCallback && e && (this.lastCallback(this.getSelectedObj()), this.lastCallback = null)
        },
        remove: function() {
            var e = this;
            modalCtrl.deleteModal("Are you sure you want delete the selected image. If it is being used it will be not visible.", function() {
                var t = e.getSelectedObj().id;
                "images" == e.mode ? serviceCtrl.deleteImage(t, $.proxy(e.deleteSuccess, e)) : serviceCtrl.deleteIcon(t, $.proxy(e.deleteSuccess, e))
            })
        },
        deleteSuccess: function() {
            this.getSelectedElement().remove(), viewCtrl.notify(Templates.deleted), this.selectionChange()
        }
    };
$.extend(modalCtrl, {
    _super: {
        init: $.proxy(modalCtrl.init, modalCtrl)
    }
}, {
    $deleteModal: null,
    $unsavedModal: null,
    $loadingModal: null,
    imageExplorerModalCtrl: null,
    uploadModalCtrl: null,
    treeModalCtrl: null,
    regionsModalCtrl: null,
    paramEditModalCtrl: null,
    lastCallback: null,
    init: function() {
        this._super.init();
        var e = this;
        this.imageExplorerModalCtrl = imageExplorerModalCtrl.init(), this.uploadModalCtrl = uploadModalCtrl.init(), this.treeModalCtrl = treeModalCtrl.init(), this.regionsModalCtrl = regionsModalCtrl.init(), this.paramEditModalCtrl = paramEditModalCtrl.init(), this.$deleteModal = $("#deleteModal"), this.$unsavedModal = $("#unsavedModal"), this.$loadingModal = $("#loadingModal"), $("#unsavedContinue").click(function(t) {
            e.unsavedContinue(), t.preventDefault()
        }), $('button[data-action="yes"]', this.$deleteModal).click(function(t) {
            e.deleteDecision(), t.preventDefault()
        })
    },
    unsavedModal: function(e) {
        this.lastCallback = e, this.$unsavedModal.modal("show")
    },
    unsavedContinue: function() {
        this.lastCallback && (this.lastCallback(), this.lastCallback = null)
    },
    deleteModal: function(e, t) {
        $(".modal-body p", this.$deleteModal).html(e), this.lastCallback = t, this.$deleteModal.modal("show")
    },
    deleteDecision: function() {
        this.lastCallback && (this.lastCallback(), this.lastCallback = null)
    },
    loadingModal: function(e) {
        this.$loadingModal.modal(e ? "show" : "hide")
    },
    treeModal: function(e) {
        this.treeModalCtrl.showModal(e)
    },
    regionsModal: function(e, t) {
        this.regionsModalCtrl.showModal(e, t)
    },
    imageExplorerModal: function(e, t, a) {
        this.imageExplorerModalCtrl.showModal(e, t, a)
    },
    uploadModal: function(e, t, a) {
        this.uploadModalCtrl.showModal(e, t, a)
    },
    messageModal: function(e) {
        viewCtrl.applyViewState(this.$messageModal, e), this.$messageModal.modal("show")
    },
    paramEditModal: function(e, t) {
        this.paramEditModalCtrl.showModal(e, t)
    }
}), $.extend(viewCtrl, {
    _super: {
        init: $.proxy(viewCtrl.init, viewCtrl)
    }
}, {
    lastTabName: null,
    lastTabParameter: null,
    currentTabName: "loginTab",
    currentTabParameter: null,
    waitingTabName: null,
    waitingTabParameter: null,
    tabControllers: [loginTabCtrl, mapsTabCtrl, mapEditTabCtrl, mapViewTabCtrl, markerTypesTabCtrl, markerTypeEditTabCtrl, dictionariesTabCtrl, dictionaryEditTabCtrl, markersTabCtrl, markerEditTabCtrl, labelsTabCtrl, labelEditTabCtrl, regionsTabCtrl, regionEditTabCtrl, settingsTabCtrl, passwordTabCtrl, logoutTabCtrl],
    $mainNavigation: null,
    $tabsContainer: null,
    init: function() {
        this._super.init(), this.$mainNavigation = $("#mainNavigation"), this.$tabsContainer = $("#tabsContainer");
        var e = $(document);
        $("body").tooltip({
            selector: "[rel=tooltip]",
            html: !0,
            placement: "bottom"
        }), $(window).resize(function() {
            viewCtrl.windowResize()
        }), $('li a[data-action="tab"]', this.$mainNavigation).click(function(e) {
            viewCtrl.showTab($(this).attr("href").slice(1)), e.preventDefault()
        }), $("#mediaDropdown ul li a").click(function(e) {
            var t = $(this).attr("href").slice(1);
            switch (t) {
                case "images":
                    modalCtrl.imageExplorerModal(!0, null, !0);
                    break;
                case "icons":
                    modalCtrl.imageExplorerModal(!1, null, !0);
                    break;
                case "uploadImages":
                    modalCtrl.uploadModal("image");
                    break;
                case "uploadIcons":
                    modalCtrl.uploadModal("icon")
            }
            e.preventDefault()
        }), $("#toolsDropdown ul li a").click(function(e) {
            var t = $(this).attr("href").slice(1);
            "clearCache" == t && (serviceCtrl.clearServerCache(), e.preventDefault())
        }), $("a", this.$mainNavigation).on("shown", function(e) {
            viewCtrl.tabShownHandler(e)
        }), e.ajaxStart(function() {
            modalCtrl.loadingModal(!0)
        }), e.ajaxStop(function() {
            modalCtrl.loadingModal(!1)
        }), $.each(this.tabControllers, function(e, t) {
            t.init()
        });
        
        /*var t = navigator.cookieEnabled ? !0 : !1;
        if ("undefined" != typeof navigator.cookieEnabled || t || (document.cookie = "testcookie", t = -1 != document.cookie.indexOf("testcookie") ? !0 : !1), !t) return $(".no-cookies").show(), void 0;
        for (var a = document.cookie.split(";"), i = 0; i < a.length; i++) {
            var n = a[i].indexOf("="),
                l = n > -1 ? a[i].substr(0, n) : a[i];
            document.cookie = l + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT"
        }
        */
        $("#preloader").remove(), this.$tabsContainer.removeClass("hide")
    },
    windowResize: function() {
        var e = $(window);
        if ("mapViewTab" == this.currentTabName) {
            var t = Math.max(e.height(), parseFloat($("body").css("min-height")));
            t -= parseFloat(this.$tabsContainer.css("padding-top")), mapViewerCtrl.$mapContainer.css("height", t)
        }
    },
    findTabController: function(e) {
        var t = this.tabControllers.length;
        for (i = 0; t > i; i++)
            if (this.tabControllers[i].tabName == e) return this.tabControllers[i];
        return !1
    },
    showTab: function(e, t, a) {
    		
    		if(this.currentTabName=='mapViewTab')
    		{
    			this.currentTabName = 'loginTab'    			    			
    		}
    		
    	      		
        if (e != this.currentTabName) 
        {
            var i = this.findTabController(e);
            if (!i) throw "tab not found";
            var n = i.$navElement;
            if (this.waitingTabName = e, this.waitingTabParameter = t ? t : [], !a) {
                var l = this.findTabController(this.currentTabName),
                    s = l.exit;
                if (!utils.isFunction(s)) throw "tab controller exitFn function not found";
                if (!s.call(l)) return !1
            }
            
            return this.lastTabName = this.currentTabName, this.lastTabParameter = this.currentTabParameter, n.tab("show"), !0
        }
    },
    showPrevTab: function() {
        this.lastTabName && this.showTab(this.lastTabName, this.lastTabParameter)
    },
    tabChangeProceed: function() {
        this.showTab(this.waitingTabName, this.waitingTabParameter, !0)
    },
    tabShownHandler: function() {
        this.currentTabName = this.waitingTabName;
        var e = this.findTabController(this.currentTabName),
            t = e.enter;
        if (!utils.isFunction(t)) throw "tab controller enter function or tab element not found";
        t.apply(e, this.waitingTabParameter), this.currentTabParameter = this.waitingTabParameter, this.waitingTabParameter = null, this.waitingTabName = null
    }
}), $(document).ready(function() {
    formValidator.init(), modalCtrl.init(), mapViewerCtrl.init(), treeCtrl.init(), viewCtrl.init()
});

function image_map_show_region(map_id,region_id)
{	
	mapViewerCtrl.showRegion(map_id, region_id)
		
	return false;
}