Array.prototype.indexOf || (Array.prototype.indexOf = function(t, e) {
        for (var a = e || 0, i = this.length; i > a; a++)
            if (this[a] === t) return a;
        return -1
    }), $.fn.resetForm = function() {
        return this.each(function() {
            var t = $(this);
            $('input[type="hidden"]', t).each(function() {
                var t = $(this);
                t.data("hiddenInput") ? t.hiddenInput("reset") : $(this).val("")
            }), ("function" == typeof this.reset || "object" == typeof this.reset && !this.reset.nodeType) && this.reset()
        })
    },
    function(t) {
        function e(e, n) {
            this.$element = t(e).first(), this.options = t.extend({}, i, n), this._defaults = i, this._name = a, this.init()
        }
        var a = "extraTable",
            i = {
                rowGeneratorFn: function(e) {
                    var a = "<tr>";
                    return t.each(e, function(t, e) {
                        a += "<td>" + e + "</td>"
                    }), a += "</tr>"
                },
                selectionClass: "row_selected",
                emptyText: "No rows."
            };
        e.prototype = {
            init: function() {
                var e = this;
                t("tbody", this.$element).on("click.extraTable", "tr", function(a) {
                    e.emptyLabelAdded || ($target = t(a.target), $target.is("tr") || ($target = $target.closest("tr")), e.clearSelection(!0), $target.addClass(e.options.selectionClass), e.$element.trigger("selectionChange.extraTable"))
                }), this.updateEmptyLabel()
            },
            setData: function(e) {
                if (this.removeEmptyLabel(), t("tbody", this.$element).empty(), e)
                    for (var a = 0; a < e.length; a++) this.addDataRow(e[a], !1);
                this.updateEmptyLabel()
            },
            getData: function(e) {
                if ("number" == typeof e) return t("tbody tr:eq(" + e + ")", this.$element).data("model");
                var a = [];
                return t("tbody tr", this.$element).each(function() {
                    a.push(t(this).data("model"))
                }), a
            },
            updateDataRow: function(e, a) {
                var i = t("tbody tr:eq(" + a + ")", this.$element);
                this.addDataRow(e, a), i.remove()
            },
            addDataRow: function(e, a) {
                this.removeEmptyLabel();
                var i = t(this.options.rowGeneratorFn(e)).data("model", e);
                a || 0 === a ? t("tbody tr:eq(" + a + ")", this.$element).after(i) : t("tbody", this.$element).append(i), this.updateEmptyLabel()
            },
            deleteRow: function(e) {
                if (!this.emptyLabelAdded) {
                    var a = t("tbody tr:eq(" + e + ")", this.$element);
                    a.length && (a.remove(), this.updateEmptyLabel())
                }
            },
            deleteRows: function() {
                this.emptyLabelAdded || (t("tbody tr", this.$element).remove(), this.updateEmptyLabel())
            },
            getSelectedElement: function() {
                var e = t("tbody tr." + this.options.selectionClass, this.$element);
                return e.length ? e : !1
            },
            updateEmptyLabel: function() {
                if (!t("tbody tr", this.$element).length && !this.emptyLabelAdded) {
                    var e = t("thead th", this.$element).length;
                    t("tbody", this.$element).html('<tr><td colspan="' + e + '">' + this.options.emptyText + "</td></tr>"), this.emptyLabelAdded = !0
                }
            },
            removeEmptyLabel: function() {
                this.emptyLabelAdded && (t("tbody", this.$element).empty(), this.emptyLabelAdded = !1)
            },
            getSelectedData: function() {
                var t = this.getSelectedElement();
                return t ? t.data("model") : !1
            },
            clearSelection: function(e) {
                t("tbody tr", this.$element).removeClass(this.options.selectionClass), e || self.$element.trigger("selectionChange.extraTable")
            },
            moveUp: function() {
                var t = this.getSelectedElement();
                return t && 0 != t.index() ? (t.prev().before(t), !0) : !1
            },
            moveDown: function() {
                var t = this.getSelectedElement();
                return t && t.index() != t.siblings("tr").length ? (t.next().after(t), !0) : !1
            },
            destroy: function() {
                t("tbody", this.$element).off(".extraTable"), t("tbody tr", this.$element).removeData("model"), this.$element.removeData("plugin_" + a)
            }
        }, t.fn[a] = function(i) {
            return t.data(this, "plugin_" + a) ? t.data(this, "plugin_" + a) : t.data(this, "plugin_" + a, new e(this, i))
        }
    }(jQuery),
    function(t) {
        var e = function(t, e) {
            this.init(t, e)
        };
        e.prototype = {
            constructor: e,
            init: function(e, a) {
                this.$element = t(e), this.options = this.getOptions(a)
            },
            setObj: function(t) {
                var e, a, i = t && t[this.options.propertyName];
                this.$element.val(i), e = this.$element.siblings(this.options.label), a = i ? "function" == typeof this.options.labelTemplate ? this.options.labelTemplate(t) : this._templateReplace(this.options.labelTemplate, t) : this.options.labelFalseyVal, "html" == this.options.labelAttr ? e.html(a) : e.attr(this.options.labelAttr, a), this.$element.data("objValue", t)
            },
            getValue: function() {
                return this.$element.val()
            },
            getObj: function() {
                return this.$element.data("objValue") ? this.$element.data("objValue") : null
            },
            reset: function() {
                this.setObj(null)
            },
            getOptions: function(e) {
                return e = t.extend({}, t.fn.hiddenInput.defaults, e, this.$element.data())
            },
            _templateReplace: function(t, e) {
                for (var a, i = new RegExp("{[a-z|0-9|_|-]+}", "gi"), n = t; null != (a = i.exec(t));) n = n.replace(a[0], e[a[0].slice(0, -1).slice(1)]);
                return n
            },
            destroy: function() {
                this.$element.removeData("objValue"), this.$element.removeData("hiddenInput")
            }
        }, t.fn.hiddenInput = function(a, i) {
            var n = t(this).first(),
                l = n.data("hiddenInput"),
                s = "object" == typeof a && a;
            return l ? "string" == typeof a ? l[a](i) : void 0 : (n.data("hiddenInput", l = new e(this, s)), l)
        }, t.fn.hiddenInput.Constructor = e, t.fn.hiddenInput.defaults = {
            propertyName: "id",
            label: ".input-label",
            labelFalseyVal: "empty",
            labelAttr: "html",
            labelTemplate: "{name}"
        }
    }(jQuery),
    function(t) {
        var e = {
            init: function(e, a) {
                this.data("alwaysEnabledArray", a), this.on("click.controls", function(a) {
                    if (a.preventDefault(), $target = t(a.target), $target.attr("href") || ($target = $target.closest("a")), !$target.hasClass("disabled")) {
                        var i = e[$target[0].getAttribute("href", 2).slice(1)];
                        i.call(e)
                    }
                })
            },
            disable: function(e) {
                if (e) {
                    var a = this;
                    t.each(e, function(e, i) {
                        t('a[href="#' + i + '"]', a).addClass("disabled")
                    })
                } else {
                    var i = this.data("alwaysEnabledArray");
                    t("a", this).each(function() {
                        $a = t(this), i && -1 != i.indexOf($a.attr("href").slice(1)) || $a.addClass("disabled")
                    })
                }
            },
            enable: function(e) {
                e ? t.each(e, function(e, a) {
                    t('a[href="#' + a + '"]', this).removeClass("disabled")
                }) : t("a", this).removeClass("disabled")
            }
        };
        t.fn.controls = function(a) {
            return e[a] ? e[a].apply(this, Array.prototype.slice.call(arguments, 1)) : "object" != typeof a && a ? (t.error("Method " + a + " does not exist on jQuery.tooltip"), void 0) : e.init.apply(this, arguments)
        }
    }(jQuery),
    function(t) {
        t.fn.objectDeserialize = function(e) {
            var a, i, n, l;
            e && (t.each(e, function(a, i) {
                if (i && -1 != i.toString().indexOf("||||")) {
                    var n = i.split("||||");
                    t.each(n, function(t, i) {
                        e[a + "-multi-" + t] = i
                    })
                }
            }), t("[name]", this).each(function() {
                a = t(this), i = a.attr("name"), e && e.hasOwnProperty(i) && (n = e[i], l = a.attr("type"), "checkbox" == l ? a.prop("checked", parseInt(n) ? !0 : !1) : "radio" == l ? a.attr("value") == n && a.prop("checked", !0) : "hidden" == l ? a.data("hiddenInput") ? a.hiddenInput("setObj", e[i]) : a.val(n) : a.val(n))
            }))
        }
    }(jQuery);