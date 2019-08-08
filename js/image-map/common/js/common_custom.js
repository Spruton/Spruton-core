L.YourMapIcon = L.Icon.extend({
    options: {
        className: "",
        html: !1,
        dataId: ""
    },
    createIcon: function(e) {
        var t = e && "DIV" === e.tagName ? e : document.createElement("div"),
            a = this.options;
        return t.innerHTML = a.html !== !1 ? a.html : "", t.setAttribute("data-id", a.dataId), t.className = "leaflet-marker-icon " + (this.options.className || ""), t
    },
    createShadow: function() {
        return null
    }
}), L.yourMapIcon = function(e) {
    return new L.YourMapIcon(e)
};
var Templates = {
        serverValidationError: "Wrong parameters passed.",
        noResource: "Resource not found.",
        sessionExpired: "Your login session has expired",
        parseError: "Could not connect or JSON parse error!",
        ajaxError: "Communication error!<br/>Check PHP script!",
        selectOption: function(e, t, a) {
            return '<option value="' + e + '">' + (a ? $.escapifyHTML(t) : t) + "</option>"
        }
    },
    serviceCtrl = {
        //gatePath: "./gate/",
        gatePath: $('#mapContainer').attr('data-path'),
        ajaxMethod: function(e) {
            var t = {
                context: this,
                dataType: "json",
                type: "POST",
                error: this.ajaxError
            };
            $.extend(t, e);
            var a = t.success;
            return t.success = function(e) {
                this.ajaxSuccess(e, a)
            }, $.ajax(t)
        },
        callAjax: function(e, t, a, r) {
            return e = e ? e : "", t = t ? t : "", this.ajaxMethod({
                data: r,
                url: this.gatePath + "&c=" + e + "&action=" + t+'&map_id=' + $('#mapContainer').attr('data-id'),
                success: a
            })
        },
        ajaxSuccess: function(e, t) {
            switch (e.code) {
                case 1:
                case 2:
                case 4:
                    $.isArray(t) ? $.each(t, function(t, a) {
                        a(e.code, e.data)
                    }) : t && t(e.code, e.data), 4 == e.code && viewCtrl.notify(Templates.noResource, !0);
                    break;
                case 3:
                    viewCtrl.notify(Templates.serverValidationError, !0);
                    break;
                case 5:
                    viewCtrl.notify(Templates.sessionExpired, !0), viewCtrl.showTab("logoutTab", null, !0);
                    break;
                default:
                    viewCtrl.notify(Templates.parseError, !0)
            }
        },
        ajaxError: function(e, t) {
            "abort" != t && viewCtrl.notify(Templates.ajaxError, !0)
        }
    },
    mapViewerCtrl = {
        $mapContainer: null,
        $legend: null,
        $breadcrumb: null,
        mapViewer: null,
        currentMapId: null,
        pendingMarkerId: null,
        pendingLabelId: null,
        pendingRegionId: null,
        _regionsData: null,
        mapsPath: "uploads/maps/",
        init: function() {
            var e = this;
            this.$mapContainer = $("#mapContainer"), this.$legend = $(".cfm-legend", this.$mapContainer), this.$breadcrumb = $(".cfm-breadcrumb", this.$mapContainer), this.labelsLayer = null, this.markersLayer = null, $("body").popover({
                content: mapViewerCtrl.markerPopoverContentFunction,
                title: mapViewerCtrl.markerPopoverTitleFunction,
                html: !0,
                trigger: "manual",
                selector: $(".cfm-marker .cfm-inner", this.$mapContainer),
                placement: "bottom-left",
                animation: !0,
                template: '<div id="markerPopover" class="leaflet-clickable popover cfm-marker-popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><a id="markerPopoverCloseBtn"  class="close" >&times;</a><div class="popover-content"></div></div></div>'
            }), $(this.$mapContainer).on("click", ".cfm-marker .cfm-inner", function() {
                e.selectMarker(e.currentMapId, $(this).parent().data("id"), !1, !1)
            }), $(this.$mapContainer).on("click", ".cfm-label .cfm-inner", function() {
                e.selectLabel(e.currentMapId, $(this).parent().data("id"), !1, !1)
            }), $(document).on("click", "#markerPopoverCloseBtn", function() {
                return e.unselectElement(!0), !1
            }), $(document).on("click", "a[data-cfm-long]", function(e) {
                modalCtrl.markerLongTextModal($(this)), e.preventDefault()
            })
        },
        showMap: function(e) {
            return this.currentMapId != e && e ? (serviceCtrl.getMapView(e, $.proxy(this.getMapSuccess, this)), !0) : !1
        },
        getMapSuccess: function(e, t) {
            
        	if (4 != e) 
        	{
                this.currentMapId = t.map.id;
                for (var a = this.mapsPath + t.map.id + "/map_{z}_{x}_{y}.png", r = t.map.mapImage.width, i = t.map.mapImage.height, n = 0, o = r, s = i, l = 129; o > l || s > l;) o = Math.floor(o / 2), s = Math.floor(s / 2), n++;
                n--;
                var m = this.findInitialZoom(t.map.zoom);
                if (this.clear(), this.mapViewer = L.map("mapViewer", {
                        minZoom: 18 - n,
                        maxZoom: 18,
                        crs: L.CRS.Simple
                    }), -1 == m) {
                    var p = this.mapViewer.unproject([0, 0], this.mapViewer.getMaxZoom()),
                        c = this.mapViewer.unproject([r, i], this.mapViewer.getMaxZoom()),
                        d = L.latLngBounds(p, c);
                    this.mapViewer.fitBounds(d)
                } else this.mapViewer.setView(this.mapViewer.unproject([r / 2, i / 2], this.mapViewer.getMaxZoom()), m); {
                    L.tileLayer(a, {
                        attribution: r + "x" + i,
                        bounds: L.latLngBounds(this.mapViewer.unproject([1, 1], this.mapViewer.getMaxZoom()), this.mapViewer.unproject([r, i], this.mapViewer.getMaxZoom()))
                    }).addTo(this.mapViewer)
                }
                if (this.mapViewer.on("zoomstart", this.onViewerBeforeZoom), this.mapViewer.on("zoomend", this.onViewerAfterZoom), this.mapViewer.on("moveend", this.onViewerAfterMove), this.mapViewer.on("movestart", this.onViewerBeforeMove), this.onViewerAfterZoom(), this.$mapContainer.attr("class", function() {
                        return $.trim($(this).attr("class") ? $(this).attr("class").replace(/map-id-\S+/g, "") : null)
                    }), this.$mapContainer.addClass("map-id-" + this.currentMapId), this.fillMapContainer(t.viewHtml.breadcrumb, t.viewHtml.legend), this._regionsData = t.regions, t.labels) {
                    for (var h = 0; h < t.labels.length; h++) {
                        var u = t.labels[h];
                        L.marker(this.mapViewer.unproject([u.x, u.y], this.mapViewer.getMaxZoom()), {
                            icon: L.yourMapIcon({
                                className: "cfm-label cfm-layer-element",
                                html: u.html,
                                dataId: u.id
                            })
                        }).addTo(this.labelsLayer)
                    }
                    this.labelsLayer.addTo(this.mapViewer)
                }
                if (t.markers) {
                    for (var h = 0; h < t.markers.length; h++) {
                        var f = t.markers[h],
                            g = L.marker(this.mapViewer.unproject([f.x, f.y], this.mapViewer.getMaxZoom()), {
                                icon: L.yourMapIcon({
                                    className: "cfm-marker cfm-layer-element cfm-marker-" + f.typeCssName,
                                    html: f.html,
                                    dataId: f.id
                                })
                            });
                        g.addTo(this.markersLayer)
                    }
                    this.markersLayer.addTo(this.mapViewer)
                }
                
                //set default region
                if($('#mapContainer').attr('data-region-id'))
                {
                	this.pendingRegionId = $('#mapContainer').attr('data-region-id');	
                }
                              
                this.pendingMarkerId ? this.selectMarker(this.currentMapId, this.pendingMarkerId, !0, !0) : this.pendingLabelId ? this.selectLabel(this.currentMapId, this.pendingLabelId, !0, !0) : this.pendingRegionId && this.showRegion(this.currentMapId, this.pendingRegionId, !0), this.pendingMarkerId = this.pendingLabelId = this.pendingRegionId = null
            }
        },
        findInitialZoom: function(e) {
            return this.pendingMarkerId ? 17 : this.pendingLabelId ? 16 : "default" == e ? -1 : 18 - e
        },
        clear: function() {
            this.mapViewer && (this.mapViewer.remove(), this.mapViewer = null), this.labelsLayer = L.layerGroup(), this.markersLayer = L.layerGroup(), this.$breadcrumb.empty(), $("ul", this.$legend).empty(), this._regionsData = null, this.$mapContainer.removeClass()
        },
        fillMapContainer: function(e, t) {
            this.$breadcrumb.html(e), t ? (this.$legend.removeClass("hide"), $("ul", this.$legend).html(t)) : this.$legend.addClass("hide")
        },
        selectElement: function(e, t, a) {
        		        		        	
            if (this.unselectElement(), e.addClass("cfm-selected"), a) {
                var r = this.findLeafletMarkerByEl(e);
                this.mapViewer.panTo(r.getLatLng(), {
                    animate: !1
                })
            }
        },
        findLeafletMarkerByEl: function(e) {
            for (var t = e.is(".cfm-label") ? this.labelsLayer.getLayers() : this.markersLayer.getLayers(), a = e.data("id"), r = 0; r < t.length; r++)
                if (t[r].options.icon.options.dataId == a) return t[r];
            return !1
        },
        unselectElement: function() {
            var e = $(".cfm-selected", this.$mapContainer);
            return e.length ? (e.popover("hide").removeClass("cfm-selected"), !0) : !1
        },
        selectMarker: function(e, t, a, r, i) {
            if (this.currentMapId == e) {
                var n = $('.cfm-marker[data-id="' + t + '"]', this.$mapContainer);
                if (!n.length) return !1;
                if (this.selectElement(n, a, r), !i) {
                    var o = n.popover($("body").data("popover")._options).data("popover");
                    o.show()
                }
                if (!r) {
                    var s = this.$mapContainer.offset(),
                        l = this.$mapContainer.height(),
                        m = this.$mapContainer.width(),
                        p = $("#markerPopover");
                    if (p.length) var c = p.offset().top - s.top,
                        d = p.offset().left - s.left,
                        h = p.outerHeight(!0),
                        u = p.outerWidth(!0);
                    var f = L.point(0, 0);
                    c + h > l && (f.y += h - (l - c)), 0 > d && (f.x += d - 10), d + u > m && (f.x -= m - (d + u) - 10), this.mapViewer.panBy(f, {
                        duration: .5
                    })
                }
                return !0
            }
            return this.pendingMarkerId = t, this.showMap(e), !1
        },
        selectLabel: function(e, t, a, r) {
                	
        		if (this.currentMapId == e) {
                var i = $('.cfm-label[data-id="' + t + '"]', this.$mapContainer);
                return i.length ? (this.selectElement(i, a, r), !0) : !1
            }
            return this.pendingLabelId = t, this.showMap(e), !1
        },
        showRegion: function(e, t, a) {
          
        	//custom change
        	this.currentMapId = e;
        	
        	if (this.currentMapId == e) {
                var r;
                if (!this._regionsData) return !1;
                if ($.each(this._regionsData, function(e, a) {
                        return a.id == t ? (r = a, !1) : void 0
                    }), !r) return !1;
                var i = "default" == r.zoom ? this.mapViewer.getZoom() : this.mapViewer.getMaxZoom() - r.zoom;
                      
                
                return this.mapViewer.setView(this.mapViewer.unproject([r.x, parseFloat(r.y)+150], this.mapViewer.getMaxZoom()), i, {
                    animate: !a
                }), !0
            }
            return this.pendingRegionId = t, this.showMap(e), !1
        },
        markerPopoverContentFunction: function() {
            var e = $(".cfm-params", $(this));
            return e.length ? e[0].outerHTML : ""
        },
        markerPopoverTitleFunction: function() {
            var e = $(this);
            return '<div class="table-wrap"><div class="image">' + $(".cfm-icon", e).html() + '</div><div class="text">' + $(".cfm-title", e).html() + "</div></div>"
        },
        onViewerBeforeZoom: function() {
            mapViewerCtrl.$mapContainer.addClass("zooming"), $(".cfm-marker.cfm-selected", this.$mapContainer).popover("hide")
        },
        onViewerAfterZoom: function() {
            mapViewerCtrl.$mapContainer.removeClass("zooming");
            var e = [1, 3, 6, 12, 25, 50, 100],
                t = e[e.length - (18 - mapViewerCtrl.mapViewer.getZoom() + 1)];
            mapViewerCtrl.mapViewer.attributionControl.setPrefix(i18n['TEXT_SCALE']+': '+ t + "%");
            						            
            var a = [];
            a.push("zoom-" + t), $.each(e, function(e, r) {
                r > t && a.push("zoom-lt-" + r), t > r && a.push("zoom-gt-" + r)
            }), mapViewerCtrl.$mapContainer.attr("class", function() {
                return $.trim($(this).attr("class").replace(/zoom-\S+/g, ""))
            }), mapViewerCtrl.$mapContainer.addClass(a.join(" ")), $(".cfm-marker.cfm-selected", mapViewerCtrl.$mapContainer).popover("show")
        },
        onViewerAfterMove: function() {},
        onViewerBeforeMove: function() {}
    },
    modalCtrl = {
        $messageModal: null,
        $longTextModal: null,
        init: function() {
            this.$messageModal = $("#messageModal"), this.$longTextModal = $("#longTextModal")
        },
        messageModal: function(e) {
            viewCtrl.applyViewState(this.$messageModal, e), this.$messageModal.modal("show")
        },
        markerLongTextModal: function(e) {
            $(".modal-body p", this.$longTextModal).html(e.data("cfm-long")), e.parents(".cfm-marker").length ? ($parent = e.parents(".cfm-marker"), $(".modal-header h3", this.$longTextModal).html($(".cfm-title-params .cfm-icon", $parent).html() + $(".cfm-title-params .cfm-title", $parent).text())) : $(".modal-header h3", this.$longTextModal).html($(".cfm-marker-popover .popover-title").html()), this.$longTextModal.modal()
        }
    },
    viewCtrl = {
        init: function() {        	
            head.csstransitions && head.csstransforms || modalCtrl.messageModal("oldBrowser")
        },
        notify: function(e, t) {
            $(".notifications").notify({
                type: t ? "error" : "success",
                message: {
                    html: e
                }
            }).show()
        },
        applyViewState: function(e, t) {
            $("*[data-state]", e).addClass("hide"), $('*[data-state~="' + t + '"]', e).removeClass("hide")
        }
    };