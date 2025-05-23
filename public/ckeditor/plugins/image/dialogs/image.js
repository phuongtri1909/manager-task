﻿!(function() {
    var t = function(t, e) {
        function i() {
            var t = arguments,
                e = this.getContentElement("advanced", "txtdlgGenStyle");
            e && e.commit.apply(e, t),
                this.foreach(function(e) {
                    e.commit &&
                        "txtdlgGenStyle" != e.id &&
                        e.commit.apply(e, t);
                });
        }
        function n(t) {
            if (!a) {
                a = 1;
                var e = this.getDialog(),
                    i = e.imageElement;
                if (i) {
                    this.commit(1, i);
                    for (
                        var n, l = (t = [].concat(t)).length, s = 0;
                        s < l;
                        s++
                    )
                        (n = e.getContentElement.apply(e, t[s].split(":"))) &&
                            n.setup(1, i);
                }
                a = 0;
            }
        }
        var a,
            l,
            s = /^\s*(\d+)((px)|\%)?\s*$/i,
            o = /(^\s*(\d+)((px)|\%)?\s*$)|^$/i,
            r = /^\d+px$/,
            m = function() {
                var t = this.getValue(),
                    e = this.getDialog(),
                    i = t.match(s);
                i && ("%" == i[2] && d(e, !1), (t = i[1])),
                    e.lockRatio &&
                        "true" ==
                            (i = e.originalElement).getCustomData("isReady") &&
                            ("txtHeight" == this.id
                                ? (t &&
                                      "0" != t &&
                                      (t = Math.round(
                                          (t / i.$.height) * i.$.width
                                      )),
                                  isNaN(t) ||
                                      e.setValueOf("info", "txtWidth", t))
                                : (t &&
                                      "0" != t &&
                                      (t = Math.round(
                                          (t / i.$.width) * i.$.height
                                      )),
                                  isNaN(t) ||
                                      e.setValueOf("info", "txtHeight", t))),
                    g(e);
            },
            g = function(t) {
                return t.originalElement && t.preview
                    ? (t.commitContent(4, t.preview), 0)
                    : 1;
            },
            d = function(t, e) {
                if (!t.getContentElement("info", "ratioLock")) return null;
                if (!(a = t.originalElement)) return null;
                if ("check" == e) {
                    if (
                        !t.userlockRatio &&
                        "true" == a.getCustomData("isReady")
                    ) {
                        var i = t.getValueOf("info", "txtWidth"),
                            n = t.getValueOf("info", "txtHeight"),
                            a = a.$.width / a.$.height,
                            l = i / n;
                        (t.lockRatio = !1),
                            i || n
                                ? 1 == Math.round((a / l) * 100) / 100 &&
                                  (t.lockRatio = !0)
                                : (t.lockRatio = !0);
                    }
                } else
                    void 0 !== e
                        ? (t.lockRatio = e)
                        : ((t.userlockRatio = 1), (t.lockRatio = !t.lockRatio));
                return (
                    (i = CKEDITOR.document.getById(f)),
                    t.lockRatio
                        ? i.removeClass("cke_btn_unlocked")
                        : i.addClass("cke_btn_unlocked"),
                    i.setAttribute("aria-checked", t.lockRatio),
                    CKEDITOR.env.hc &&
                        i
                            .getChild(0)
                            .setHtml(
                                t.lockRatio
                                    ? CKEDITOR.env.ie
                                        ? "■"
                                        : "▣"
                                    : CKEDITOR.env.ie
                                    ? "□"
                                    : "▢"
                            ),
                    t.lockRatio
                );
            },
            h = function(t, e) {
                var i = t.originalElement;
                if ("true" == i.getCustomData("isReady")) {
                    var n,
                        a = t.getContentElement("info", "txtWidth"),
                        l = t.getContentElement("info", "txtHeight");
                    e ? (i = n = 0) : ((n = i.$.width), (i = i.$.height)),
                        a && a.setValue(n),
                        l && l.setValue(i);
                }
                g(t);
            },
            u = function(t, e) {
                function i(t, e) {
                    var i = t.match(s);
                    return i
                        ? ("%" == i[2] && ((i[1] += "%"), d(n, !1)), i[1])
                        : e;
                }
                if (1 == t) {
                    var n = this.getDialog(),
                        a = "",
                        l = "txtWidth" == this.id ? "width" : "height",
                        o = e.getAttribute(l);
                    o && (a = i(o, a)),
                        (a = i(e.getStyle(l), a)),
                        this.setValue(a);
                }
            },
            c = function() {
                var e = this.originalElement,
                    i = CKEDITOR.document.getById(y);
                e.setCustomData("isReady", "true"),
                    e.removeListener("load", c),
                    e.removeListener("error", p),
                    e.removeListener("abort", p),
                    i && i.setStyle("display", "none"),
                    this.dontResetSize ||
                        h(this, !1 === t.config.image_prefillDimensions),
                    this.firstLoad &&
                        CKEDITOR.tools.setTimeout(
                            function() {
                                d(this, "check");
                            },
                            0,
                            this
                        ),
                    (this.dontResetSize = this.firstLoad = !1),
                    g(this);
            },
            p = function() {
                var t = this.originalElement,
                    e = CKEDITOR.document.getById(y);
                t.removeListener("load", c),
                    t.removeListener("error", p),
                    t.removeListener("abort", p),
                    (t = CKEDITOR.getUrl(
                        CKEDITOR.plugins.get("image").path +
                            "images/noimage.png"
                    )),
                    this.preview && this.preview.setAttribute("src", t),
                    e && e.setStyle("display", "none"),
                    d(this, !1);
            },
            v = function(t) {
                return CKEDITOR.tools.getNextId() + "_" + t;
            },
            f = v("btnLockSizes"),
            b = v("btnResetSize"),
            y = v("ImagePreviewLoader"),
            E = v("previewLink"),
            C = v("previewImage");
        return {
            title: t.lang.image["image" == e ? "title" : "titleButton"],
            minWidth:
                "moono-lisa" == (CKEDITOR.skinName || t.config.skin)
                    ? 500
                    : 420,
            minHeight: 360,
            getModel: function(t) {
                var e =
                        (t = t.getSelection().getSelectedElement()) &&
                        "img" === t.getName(),
                    i =
                        t &&
                        "input" === t.getName() &&
                        "image" === t.getAttribute("type");
                return e || i ? t : null;
            },
            onShow: function() {
                (this.linkEditMode = this.imageEditMode = this.linkElement = this.imageElement = !1),
                    (this.lockRatio = !0),
                    (this.userlockRatio = 0),
                    (this.dontResetSize = !1),
                    (this.firstLoad = !0),
                    (this.addLink = !1);
                var t = this.getParentEditor(),
                    i = t.getSelection(),
                    n =
                        (i = i && i.getSelectedElement()) &&
                        t.elementPath(i).contains("a", 1),
                    a = CKEDITOR.document.getById(y);
                a && a.setStyle("display", "none"),
                    (l = new CKEDITOR.dom.element("img", t.document)),
                    (this.preview = CKEDITOR.document.getById(C)),
                    (this.originalElement = t.document.createElement("img")),
                    this.originalElement.setAttribute("alt", ""),
                    this.originalElement.setCustomData("isReady", "false"),
                    n &&
                        ((this.linkElement = n),
                        (this.addLink = this.linkEditMode = !0),
                        1 == (t = n.getChildren()).count() &&
                            (a = t.getItem(0)).type == CKEDITOR.NODE_ELEMENT &&
                                (a.is("img") || a.is("input")) &&
                                ((this.imageElement = t.getItem(0)),
                                this.imageElement.is("img")
                                    ? (this.imageEditMode = "img")
                                    : this.imageElement.is("input") &&
                                      (this.imageEditMode = "input")),
                        "image" == e && this.setupContent(2, n)),
                    this.customImageElement
                        ? ((this.imageEditMode = "img"),
                          (this.imageElement = this.customImageElement),
                          delete this.customImageElement)
                        : ((i &&
                              "img" == i.getName() &&
                              !i.data("cke-realelement")) ||
                              (i &&
                                  "input" == i.getName() &&
                                  "image" == i.getAttribute("type"))) &&
                          ((this.imageEditMode = i.getName()),
                          (this.imageElement = i)),
                    this.imageEditMode &&
                        ((this.cleanImageElement = this.imageElement),
                        (this.imageElement = this.cleanImageElement.clone(
                            !0,
                            !0
                        )),
                        this.setupContent(1, this.imageElement)),
                    d(this, !0),
                    CKEDITOR.tools.trim(this.getValueOf("info", "txtUrl")) ||
                        (this.preview.removeAttribute("src"),
                        this.preview.setStyle("display", "none"));
            },
            onOk: function() {
                if (this.imageEditMode) {
                    var i = this.imageEditMode;
                    "image" == e &&
                    "input" == i &&
                    confirm(t.lang.image.button2Img)
                        ? ((this.imageElement = t.document.createElement(
                              "img"
                          )),
                          this.imageElement.setAttribute("alt", ""),
                          t.insertElement(this.imageElement))
                        : "image" != e &&
                          "img" == i &&
                          confirm(t.lang.image.img2Button)
                        ? ((this.imageElement = t.document.createElement(
                              "input"
                          )),
                          this.imageElement.setAttributes({
                              type: "image",
                              alt: ""
                          }),
                          t.insertElement(this.imageElement))
                        : ((this.imageElement = this.cleanImageElement),
                          delete this.cleanImageElement);
                } else
                    "image" == e
                        ? (this.imageElement = t.document.createElement("img"))
                        : ((this.imageElement = t.document.createElement(
                              "input"
                          )),
                          this.imageElement.setAttribute("type", "image")),
                        this.imageElement.setAttribute("alt", "");
                this.linkEditMode ||
                    (this.linkElement = t.document.createElement("a")),
                    this.commitContent(1, this.imageElement),
                    this.commitContent(2, this.linkElement),
                    this.imageElement.getAttribute("style") ||
                        this.imageElement.removeAttribute("style"),
                    this.imageEditMode
                        ? !this.linkEditMode && this.addLink
                            ? (t.insertElement(this.linkElement),
                              this.imageElement.appendTo(this.linkElement))
                            : this.linkEditMode &&
                              !this.addLink &&
                              (t.getSelection().selectElement(this.linkElement),
                              t.insertElement(this.imageElement))
                        : this.addLink
                        ? this.linkEditMode
                            ? this.linkElement.equals(
                                  t.getSelection().getSelectedElement()
                              )
                                ? (this.linkElement.setHtml(""),
                                  this.linkElement.append(
                                      this.imageElement,
                                      !1
                                  ))
                                : t.insertElement(this.imageElement)
                            : (t.insertElement(this.linkElement),
                              this.linkElement.append(this.imageElement, !1))
                        : t.insertElement(this.imageElement);
            },
            onLoad: function() {
                "image" != e && this.hidePage("Link");
                var t = this._.element.getDocument();
                this.getContentElement("info", "ratioLock") &&
                    (this.addFocusable(t.getById(b), 5),
                    this.addFocusable(t.getById(f), 5)),
                    (this.commitContent = i);
            },
            onHide: function() {
                this.preview && this.commitContent(8, this.preview),
                    this.originalElement &&
                        (this.originalElement.removeListener("load", c),
                        this.originalElement.removeListener("error", p),
                        this.originalElement.removeListener("abort", p),
                        this.originalElement.remove(),
                        (this.originalElement = !1)),
                    delete this.imageElement;
            },
            contents: [
                {
                    id: "info",
                    label: t.lang.image.infoTab,
                    accessKey: "I",
                    elements: [
                        {
                            type: "vbox",
                            padding: 0,
                            children: [
                                {
                                    type: "hbox",
                                    widths: ["280px", "110px"],
                                    align: "right",
                                    className: "cke_dialog_image_url",
                                    children: [
                                        {
                                            id: "txtUrl",
                                            type: "text",
                                            label: t.lang.common.url,
                                            required: !0,
                                            onChange: function() {
                                                var t = this.getDialog(),
                                                    e = this.getValue();
                                                if (0 < e.length) {
                                                    var i = (t = this.getDialog())
                                                        .originalElement;
                                                    t.preview &&
                                                        t.preview.removeStyle(
                                                            "display"
                                                        ),
                                                        i.setCustomData(
                                                            "isReady",
                                                            "false"
                                                        );
                                                    var n = CKEDITOR.document.getById(
                                                        y
                                                    );
                                                    n &&
                                                        n.setStyle(
                                                            "display",
                                                            ""
                                                        ),
                                                        i.on("load", c, t),
                                                        i.on("error", p, t),
                                                        i.on("abort", p, t),
                                                        i.setAttribute(
                                                            "src",
                                                            e
                                                        ),
                                                        t.preview &&
                                                            (l.setAttribute(
                                                                "src",
                                                                e
                                                            ),
                                                            t.preview.setAttribute(
                                                                "src",
                                                                l.$.src
                                                            ),
                                                            g(t));
                                                } else
                                                    t.preview &&
                                                        (t.preview.removeAttribute(
                                                            "src"
                                                        ),
                                                        t.preview.setStyle(
                                                            "display",
                                                            "none"
                                                        ));
                                            },
                                            setup: function(t, e) {
                                                if (1 == t) {
                                                    var i =
                                                        e.data(
                                                            "cke-saved-src"
                                                        ) ||
                                                        e.getAttribute("src");
                                                    (this.getDialog().dontResetSize = !0),
                                                        this.setValue(i),
                                                        this.setInitValue();
                                                }
                                            },
                                            commit: function(t, e) {
                                                1 == t &&
                                                (this.getValue() ||
                                                    this.isChanged())
                                                    ? (e.data(
                                                          "cke-saved-src",
                                                          this.getValue()
                                                      ),
                                                      e.setAttribute(
                                                          "src",
                                                          this.getValue()
                                                      ))
                                                    : 8 == t &&
                                                      (e.setAttribute(
                                                          "src",
                                                          ""
                                                      ),
                                                      e.removeAttribute("src"));
                                            },
                                            validate: CKEDITOR.dialog.validate.notEmpty(
                                                t.lang.image.urlMissing
                                            )
                                        },
                                        {
                                            type: "button",
                                            id: "browse",
                                            style:
                                                "display:inline-block;margin-top:14px;",
                                            align: "center",
                                            label: t.lang.common.browseServer,
                                            hidden: !0,
                                            filebrowser: "info:txtUrl"
                                        }
                                    ]
                                }
                            ]
                        },
                        {
                            id: "txtAlt",
                            type: "text",
                            label: t.lang.image.alt,
                            accessKey: "T",
                            default: "",
                            onChange: function() {
                                g(this.getDialog());
                            },
                            setup: function(t, e) {
                                1 == t && this.setValue(e.getAttribute("alt"));
                            },
                            commit: function(t, e) {
                                1 == t
                                    ? (this.getValue() || this.isChanged()) &&
                                      e.setAttribute("alt", this.getValue())
                                    : 4 == t
                                    ? e.setAttribute("alt", this.getValue())
                                    : 8 == t && e.removeAttribute("alt");
                            }
                        },
                        {
                            type: "hbox",
                            children: [
                                {
                                    id: "basic",
                                    type: "vbox",
                                    children: [
                                        {
                                            type: "hbox",
                                            requiredContent:
                                                "img{width,height}",
                                            widths: ["50%", "50%"],
                                            children: [
                                                {
                                                    type: "vbox",
                                                    padding: 1,
                                                    children: [
                                                        {
                                                            type: "text",
                                                            width: "45px",
                                                            id: "txtWidth",
                                                            label:
                                                                t.lang.common
                                                                    .width,
                                                            onKeyUp: m,
                                                            onChange: function() {
                                                                n.call(
                                                                    this,
                                                                    "advanced:txtdlgGenStyle"
                                                                );
                                                            },
                                                            validate: function() {
                                                                var e = this.getValue().match(
                                                                    o
                                                                );
                                                                return (
                                                                    (e = !(
                                                                        !e ||
                                                                        0 ===
                                                                            parseInt(
                                                                                e[1],
                                                                                10
                                                                            )
                                                                    )) ||
                                                                        alert(
                                                                            t.lang.common.invalidLength
                                                                                .replace(
                                                                                    "%1",
                                                                                    t
                                                                                        .lang
                                                                                        .common
                                                                                        .width
                                                                                )
                                                                                .replace(
                                                                                    "%2",
                                                                                    "px, %"
                                                                                )
                                                                        ),
                                                                    e
                                                                );
                                                            },
                                                            setup: u,
                                                            commit: function(
                                                                e,
                                                                i
                                                            ) {
                                                                var n = this.getValue();
                                                                1 == e
                                                                    ? (n &&
                                                                      t.activeFilter.check(
                                                                          "img{width,height}"
                                                                      )
                                                                          ? i.setStyle(
                                                                                "width",
                                                                                CKEDITOR.tools.cssLength(
                                                                                    n
                                                                                )
                                                                            )
                                                                          : i.removeStyle(
                                                                                "width"
                                                                            ),
                                                                      i.removeAttribute(
                                                                          "width"
                                                                      ))
                                                                    : 4 == e
                                                                    ? n.match(s)
                                                                        ? i.setStyle(
                                                                              "width",
                                                                              CKEDITOR.tools.cssLength(
                                                                                  n
                                                                              )
                                                                          )
                                                                        : "true" ==
                                                                              (n = this.getDialog()
                                                                                  .originalElement).getCustomData(
                                                                                  "isReady"
                                                                              ) &&
                                                                          i.setStyle(
                                                                              "width",
                                                                              n
                                                                                  .$
                                                                                  .width +
                                                                                  "px"
                                                                          )
                                                                    : 8 == e &&
                                                                      (i.removeAttribute(
                                                                          "width"
                                                                      ),
                                                                      i.removeStyle(
                                                                          "width"
                                                                      ));
                                                            }
                                                        },
                                                        {
                                                            type: "text",
                                                            id: "txtHeight",
                                                            width: "45px",
                                                            label:
                                                                t.lang.common
                                                                    .height,
                                                            onKeyUp: m,
                                                            onChange: function() {
                                                                n.call(
                                                                    this,
                                                                    "advanced:txtdlgGenStyle"
                                                                );
                                                            },
                                                            validate: function() {
                                                                var e = this.getValue().match(
                                                                    o
                                                                );
                                                                return (
                                                                    (e = !(
                                                                        !e ||
                                                                        0 ===
                                                                            parseInt(
                                                                                e[1],
                                                                                10
                                                                            )
                                                                    )) ||
                                                                        alert(
                                                                            t.lang.common.invalidLength
                                                                                .replace(
                                                                                    "%1",
                                                                                    t
                                                                                        .lang
                                                                                        .common
                                                                                        .height
                                                                                )
                                                                                .replace(
                                                                                    "%2",
                                                                                    "px, %"
                                                                                )
                                                                        ),
                                                                    e
                                                                );
                                                            },
                                                            setup: u,
                                                            commit: function(
                                                                e,
                                                                i
                                                            ) {
                                                                var n = this.getValue();
                                                                1 == e
                                                                    ? (n &&
                                                                      t.activeFilter.check(
                                                                          "img{width,height}"
                                                                      )
                                                                          ? i.setStyle(
                                                                                "height",
                                                                                CKEDITOR.tools.cssLength(
                                                                                    n
                                                                                )
                                                                            )
                                                                          : i.removeStyle(
                                                                                "height"
                                                                            ),
                                                                      i.removeAttribute(
                                                                          "height"
                                                                      ))
                                                                    : 4 == e
                                                                    ? n.match(s)
                                                                        ? i.setStyle(
                                                                              "height",
                                                                              CKEDITOR.tools.cssLength(
                                                                                  n
                                                                              )
                                                                          )
                                                                        : "true" ==
                                                                              (n = this.getDialog()
                                                                                  .originalElement).getCustomData(
                                                                                  "isReady"
                                                                              ) &&
                                                                          i.setStyle(
                                                                              "height",
                                                                              n
                                                                                  .$
                                                                                  .height +
                                                                                  "px"
                                                                          )
                                                                    : 8 == e &&
                                                                      (i.removeAttribute(
                                                                          "height"
                                                                      ),
                                                                      i.removeStyle(
                                                                          "height"
                                                                      ));
                                                            }
                                                        }
                                                    ]
                                                },
                                                {
                                                    id: "ratioLock",
                                                    type: "html",
                                                    className:
                                                        "cke_dialog_image_ratiolock",
                                                    style:
                                                        "margin-top:30px;width:40px;height:40px;",
                                                    onLoad: function() {
                                                        var t = CKEDITOR.document.getById(
                                                                b
                                                            ),
                                                            e = CKEDITOR.document.getById(
                                                                f
                                                            );
                                                        t &&
                                                            (t.on(
                                                                "click",
                                                                function(t) {
                                                                    h(this),
                                                                        t.data &&
                                                                            t.data.preventDefault();
                                                                },
                                                                this.getDialog()
                                                            ),
                                                            t.on(
                                                                "mouseover",
                                                                function() {
                                                                    this.addClass(
                                                                        "cke_btn_over"
                                                                    );
                                                                },
                                                                t
                                                            ),
                                                            t.on(
                                                                "mouseout",
                                                                function() {
                                                                    this.removeClass(
                                                                        "cke_btn_over"
                                                                    );
                                                                },
                                                                t
                                                            )),
                                                            e &&
                                                                (e.on(
                                                                    "click",
                                                                    function(
                                                                        t
                                                                    ) {
                                                                        d(this);
                                                                        var e = this
                                                                                .originalElement,
                                                                            i = this.getValueOf(
                                                                                "info",
                                                                                "txtWidth"
                                                                            );
                                                                        "true" ==
                                                                            e.getCustomData(
                                                                                "isReady"
                                                                            ) &&
                                                                            i &&
                                                                            ((e =
                                                                                (e
                                                                                    .$
                                                                                    .height /
                                                                                    e
                                                                                        .$
                                                                                        .width) *
                                                                                i),
                                                                            isNaN(
                                                                                e
                                                                            ) ||
                                                                                (this.setValueOf(
                                                                                    "info",
                                                                                    "txtHeight",
                                                                                    Math.round(
                                                                                        e
                                                                                    )
                                                                                ),
                                                                                g(
                                                                                    this
                                                                                ))),
                                                                            t.data &&
                                                                                t.data.preventDefault();
                                                                    },
                                                                    this.getDialog()
                                                                ),
                                                                e.on(
                                                                    "mouseover",
                                                                    function() {
                                                                        this.addClass(
                                                                            "cke_btn_over"
                                                                        );
                                                                    },
                                                                    e
                                                                ),
                                                                e.on(
                                                                    "mouseout",
                                                                    function() {
                                                                        this.removeClass(
                                                                            "cke_btn_over"
                                                                        );
                                                                    },
                                                                    e
                                                                ));
                                                    },
                                                    html:
                                                        '<div><a href="javascript:void(0)" tabindex="-1" title="' +
                                                        t.lang.image.lockRatio +
                                                        '" class="cke_btn_locked" id="' +
                                                        f +
                                                        '" role="checkbox"><span class="cke_icon"></span><span class="cke_label">' +
                                                        t.lang.image.lockRatio +
                                                        '</span></a><a href="javascript:void(0)" tabindex="-1" title="' +
                                                        t.lang.image.resetSize +
                                                        '" class="cke_btn_reset" id="' +
                                                        b +
                                                        '" role="button"><span class="cke_label">' +
                                                        t.lang.image.resetSize +
                                                        "</span></a></div>"
                                                }
                                            ]
                                        },
                                        {
                                            type: "vbox",
                                            padding: 1,
                                            children: [
                                                {
                                                    type: "text",
                                                    id: "txtBorder",
                                                    requiredContent:
                                                        "img{border-width}",
                                                    width: "60px",
                                                    label: t.lang.image.border,
                                                    default: "",
                                                    onKeyUp: function() {
                                                        g(this.getDialog());
                                                    },
                                                    onChange: function() {
                                                        n.call(
                                                            this,
                                                            "advanced:txtdlgGenStyle"
                                                        );
                                                    },
                                                    validate: CKEDITOR.dialog.validate.integer(
                                                        t.lang.image
                                                            .validateBorder
                                                    ),
                                                    setup: function(t, e) {
                                                        var i;
                                                        1 == t &&
                                                            ((i =
                                                                (i =
                                                                    (i = e.getStyle(
                                                                        "border-width"
                                                                    )) &&
                                                                    i.match(
                                                                        /^(\d+px)(?: \1 \1 \1)?$/
                                                                    )) &&
                                                                parseInt(
                                                                    i[1],
                                                                    10
                                                                )),
                                                            isNaN(
                                                                parseInt(i, 10)
                                                            ) &&
                                                                (i = e.getAttribute(
                                                                    "border"
                                                                )),
                                                            this.setValue(i));
                                                    },
                                                    commit: function(t, e) {
                                                        var i = parseInt(
                                                            this.getValue(),
                                                            10
                                                        );
                                                        1 == t || 4 == t
                                                            ? (isNaN(i)
                                                                  ? !i &&
                                                                    this.isChanged() &&
                                                                    e.removeStyle(
                                                                        "border"
                                                                    )
                                                                  : (e.setStyle(
                                                                        "border-width",
                                                                        CKEDITOR.tools.cssLength(
                                                                            i
                                                                        )
                                                                    ),
                                                                    e.setStyle(
                                                                        "border-style",
                                                                        "solid"
                                                                    )),
                                                              1 == t &&
                                                                  e.removeAttribute(
                                                                      "border"
                                                                  ))
                                                            : 8 == t &&
                                                              (e.removeAttribute(
                                                                  "border"
                                                              ),
                                                              e.removeStyle(
                                                                  "border-width"
                                                              ),
                                                              e.removeStyle(
                                                                  "border-style"
                                                              ),
                                                              e.removeStyle(
                                                                  "border-color"
                                                              ));
                                                    }
                                                },
                                                {
                                                    type: "text",
                                                    id: "txtHSpace",
                                                    requiredContent:
                                                        "img{margin-left,margin-right}",
                                                    width: "60px",
                                                    label: t.lang.image.hSpace,
                                                    default: "",
                                                    onKeyUp: function() {
                                                        g(this.getDialog());
                                                    },
                                                    onChange: function() {
                                                        n.call(
                                                            this,
                                                            "advanced:txtdlgGenStyle"
                                                        );
                                                    },
                                                    validate: CKEDITOR.dialog.validate.integer(
                                                        t.lang.image
                                                            .validateHSpace
                                                    ),
                                                    setup: function(t, e) {
                                                        var i, n;
                                                        1 == t &&
                                                            ((i = e.getStyle(
                                                                "margin-left"
                                                            )),
                                                            (n = e.getStyle(
                                                                "margin-right"
                                                            )),
                                                            (i =
                                                                i &&
                                                                i.match(r)),
                                                            (n =
                                                                n &&
                                                                n.match(r)),
                                                            (i =
                                                                (i = parseInt(
                                                                    i,
                                                                    10
                                                                )) ==
                                                                    (n = parseInt(
                                                                        n,
                                                                        10
                                                                    )) && i),
                                                            isNaN(
                                                                parseInt(i, 10)
                                                            ) &&
                                                                (i = e.getAttribute(
                                                                    "hspace"
                                                                )),
                                                            this.setValue(i));
                                                    },
                                                    commit: function(t, e) {
                                                        var i = parseInt(
                                                            this.getValue(),
                                                            10
                                                        );
                                                        1 == t || 4 == t
                                                            ? (isNaN(i)
                                                                  ? !i &&
                                                                    this.isChanged() &&
                                                                    (e.removeStyle(
                                                                        "margin-left"
                                                                    ),
                                                                    e.removeStyle(
                                                                        "margin-right"
                                                                    ))
                                                                  : (e.setStyle(
                                                                        "margin-left",
                                                                        CKEDITOR.tools.cssLength(
                                                                            i
                                                                        )
                                                                    ),
                                                                    e.setStyle(
                                                                        "margin-right",
                                                                        CKEDITOR.tools.cssLength(
                                                                            i
                                                                        )
                                                                    )),
                                                              1 == t &&
                                                                  e.removeAttribute(
                                                                      "hspace"
                                                                  ))
                                                            : 8 == t &&
                                                              (e.removeAttribute(
                                                                  "hspace"
                                                              ),
                                                              e.removeStyle(
                                                                  "margin-left"
                                                              ),
                                                              e.removeStyle(
                                                                  "margin-right"
                                                              ));
                                                    }
                                                },
                                                {
                                                    type: "text",
                                                    id: "txtVSpace",
                                                    requiredContent:
                                                        "img{margin-top,margin-bottom}",
                                                    width: "60px",
                                                    label: t.lang.image.vSpace,
                                                    default: "",
                                                    onKeyUp: function() {
                                                        g(this.getDialog());
                                                    },
                                                    onChange: function() {
                                                        n.call(
                                                            this,
                                                            "advanced:txtdlgGenStyle"
                                                        );
                                                    },
                                                    validate: CKEDITOR.dialog.validate.integer(
                                                        t.lang.image
                                                            .validateVSpace
                                                    ),
                                                    setup: function(t, e) {
                                                        var i, n;
                                                        1 == t &&
                                                            ((i = e.getStyle(
                                                                "margin-top"
                                                            )),
                                                            (n = e.getStyle(
                                                                "margin-bottom"
                                                            )),
                                                            (i =
                                                                i &&
                                                                i.match(r)),
                                                            (n =
                                                                n &&
                                                                n.match(r)),
                                                            (i =
                                                                (i = parseInt(
                                                                    i,
                                                                    10
                                                                )) ==
                                                                    (n = parseInt(
                                                                        n,
                                                                        10
                                                                    )) && i),
                                                            isNaN(
                                                                parseInt(i, 10)
                                                            ) &&
                                                                (i = e.getAttribute(
                                                                    "vspace"
                                                                )),
                                                            this.setValue(i));
                                                    },
                                                    commit: function(t, e) {
                                                        var i = parseInt(
                                                            this.getValue(),
                                                            10
                                                        );
                                                        1 == t || 4 == t
                                                            ? (isNaN(i)
                                                                  ? !i &&
                                                                    this.isChanged() &&
                                                                    (e.removeStyle(
                                                                        "margin-top"
                                                                    ),
                                                                    e.removeStyle(
                                                                        "margin-bottom"
                                                                    ))
                                                                  : (e.setStyle(
                                                                        "margin-top",
                                                                        CKEDITOR.tools.cssLength(
                                                                            i
                                                                        )
                                                                    ),
                                                                    e.setStyle(
                                                                        "margin-bottom",
                                                                        CKEDITOR.tools.cssLength(
                                                                            i
                                                                        )
                                                                    )),
                                                              1 == t &&
                                                                  e.removeAttribute(
                                                                      "vspace"
                                                                  ))
                                                            : 8 == t &&
                                                              (e.removeAttribute(
                                                                  "vspace"
                                                              ),
                                                              e.removeStyle(
                                                                  "margin-top"
                                                              ),
                                                              e.removeStyle(
                                                                  "margin-bottom"
                                                              ));
                                                    }
                                                },
                                                {
                                                    id: "cmbAlign",
                                                    requiredContent:
                                                        "img{float}",
                                                    type: "select",
                                                    widths: ["35%", "65%"],
                                                    style: "width:90px",
                                                    label: t.lang.common.align,
                                                    default: "",
                                                    items: [
                                                        [
                                                            t.lang.common
                                                                .notSet,
                                                            ""
                                                        ],
                                                        [
                                                            t.lang.common.left,
                                                            "left"
                                                        ],
                                                        [
                                                            t.lang.common.right,
                                                            "right"
                                                        ],
                                                        [
                                                            t.lang.common
                                                                .center,
                                                            "center"
                                                        ]
                                                    ],
                                                    onChange: function() {
                                                        g(this.getDialog()),
                                                            n.call(
                                                                this,
                                                                "advanced:txtdlgGenStyle"
                                                            );
                                                    },
                                                    setup: function(t, e) {
                                                        if (1 == t) {
                                                            var i = e.getStyle(
                                                                "float"
                                                            );
                                                            switch (i) {
                                                                case "inherit":
                                                                case "none":
                                                                    i = "";
                                                            }
                                                            !i &&
                                                                (i = (
                                                                    e.getAttribute(
                                                                        "align"
                                                                    ) || ""
                                                                ).toLowerCase()),
                                                                this.setValue(
                                                                    i
                                                                );
                                                        }
                                                    },
                                                    commit: function(t, e) {
                                                        var i = this.getValue();
                                                        if (1 == t || 4 == t) {
                                                            if (
                                                                (i
                                                                    ? "center" ===
                                                                      i
                                                                        ? (e.removeStyle(
                                                                              "float"
                                                                          ),
                                                                          e.setStyle(
                                                                              "display",
                                                                              "block"
                                                                          ),
                                                                          e.setStyle(
                                                                              "margin",
                                                                              "0 auto"
                                                                          ))
                                                                        : (e.setStyle(
                                                                              "float",
                                                                              i
                                                                          ),
                                                                          e.removeStyle(
                                                                              "display"
                                                                          ),
                                                                          e.removeStyle(
                                                                              "margin"
                                                                          ))
                                                                    : (e.removeStyle(
                                                                          "float"
                                                                      ),
                                                                      e.removeStyle(
                                                                          "display"
                                                                      ),
                                                                      e.removeStyle(
                                                                          "margin"
                                                                      )),
                                                                1 == t)
                                                            )
                                                                switch (
                                                                    (
                                                                        e.getAttribute(
                                                                            "align"
                                                                        ) || ""
                                                                    ).toLowerCase()
                                                                ) {
                                                                    case "left":
                                                                    case "right":
                                                                    case "center":
                                                                        e.removeAttribute(
                                                                            "align"
                                                                        );
                                                                }
                                                        } else
                                                            8 == t &&
                                                                (e.removeStyle(
                                                                    "float"
                                                                ),
                                                                e.removeStyle(
                                                                    "display"
                                                                ),
                                                                e.removeStyle(
                                                                    "margin"
                                                                ));
                                                    }
                                                }
                                            ]
                                        }
                                    ]
                                },
                                {
                                    type: "vbox",
                                    height: "250px",
                                    children: [
                                        {
                                            type: "html",
                                            id: "htmlPreview",
                                            style: "width:95%;",
                                            html:
                                                "<div>" +
                                                CKEDITOR.tools.htmlEncode(
                                                    t.lang.common.preview
                                                ) +
                                                '<br><div id="' +
                                                y +
                                                '" class="ImagePreviewLoader" style="display:none"><div class="loading">&nbsp;</div></div><div class="ImagePreviewBox"><table><tr><td><a href="javascript:void(0)" target="_blank" onclick="return false;" id="' +
                                                E +
                                                '"><img id="' +
                                                C +
                                                '" alt="" /></a>' +
                                                (t.config.image_previewText ||
                                                    "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Maecenas feugiat consequat diam. Maecenas metus. Vivamus diam purus, cursus a, commodo non, facilisis vitae, nulla. Aenean dictum lacinia tortor. Nunc iaculis, nibh non iaculis aliquam, orci felis euismod neque, sed ornare massa mauris sed velit. Nulla pretium mi et risus. Fusce mi pede, tempor id, cursus ac, ullamcorper nec, enim. Sed tortor. Curabitur molestie. Duis velit augue, condimentum at, ultrices a, luctus ut, orci. Donec pellentesque egestas eros. Integer cursus, augue in cursus faucibus, eros pede bibendum sem, in tempus tellus justo quis ligula. Etiam eget tortor. Vestibulum rutrum, est ut placerat elementum, lectus nisl aliquam velit, tempor aliquam eros nunc nonummy metus. In eros metus, gravida a, gravida sed, lobortis id, turpis. Ut ultrices, ipsum at venenatis fringilla, sem nulla lacinia tellus, eget aliquet turpis mauris non enim. Nam turpis. Suspendisse lacinia. Curabitur ac tortor ut ipsum egestas elementum. Nunc imperdiet gravida mauris.") +
                                                "</td></tr></table></div></div>"
                                        }
                                    ]
                                }
                            ]
                        }
                    ]
                },
                {
                    id: "Link",
                    requiredContent: "a[href]",
                    label: t.lang.image.linkTab,
                    padding: 0,
                    elements: [
                        {
                            id: "txtUrl",
                            type: "text",
                            label: t.lang.common.url,
                            style: "width: 100%",
                            default: "",
                            setup: function(t, e) {
                                if (2 == t) {
                                    var i = e.data("cke-saved-href");
                                    i || (i = e.getAttribute("href")),
                                        this.setValue(i);
                                }
                            },
                            commit: function(e, i) {
                                if (
                                    2 == e &&
                                    (this.getValue() || this.isChanged())
                                ) {
                                    var n = this.getValue();
                                    i.data("cke-saved-href", n),
                                        i.setAttribute("href", n),
                                        this.getValue() ||
                                        !t.config.image_removeLinkByEmptyURL
                                            ? (this.getDialog().addLink = !0)
                                            : (this.getDialog().addLink = !1);
                                }
                            }
                        },
                        {
                            type: "button",
                            id: "browse",
                            className: "cke_dialog_image_browse",
                            filebrowser: {
                                action: "Browse",
                                target: "Link:txtUrl",
                                url: t.config.filebrowserImageBrowseLinkUrl
                            },
                            style: "float:right",
                            hidden: !0,
                            label: t.lang.common.browseServer
                        },
                        {
                            id: "cmbTarget",
                            type: "select",
                            requiredContent: "a[target]",
                            label: t.lang.common.target,
                            default: "",
                            items: [
                                [t.lang.common.notSet, ""],
                                [t.lang.common.targetNew, "_blank"],
                                [t.lang.common.targetTop, "_top"],
                                [t.lang.common.targetSelf, "_self"],
                                [t.lang.common.targetParent, "_parent"]
                            ],
                            setup: function(t, e) {
                                2 == t &&
                                    this.setValue(
                                        e.getAttribute("target") || ""
                                    );
                            },
                            commit: function(t, e) {
                                2 == t &&
                                    (this.getValue() || this.isChanged()) &&
                                    e.setAttribute("target", this.getValue());
                            }
                        }
                    ]
                },
                {
                    id: "Upload",
                    hidden: !0,
                    filebrowser: "uploadButton",
                    label: t.lang.image.upload,
                    elements: [
                        {
                            type: "file",
                            id: "upload",
                            label: t.lang.image.btnUpload,
                            style: "height:40px",
                            size: 38
                        },
                        {
                            type: "fileButton",
                            id: "uploadButton",
                            filebrowser: "info:txtUrl",
                            label: t.lang.image.btnUpload,
                            for: ["Upload", "upload"]
                        }
                    ]
                },
                {
                    id: "advanced",
                    label: t.lang.common.advancedTab,
                    elements: [
                        {
                            type: "hbox",
                            widths: ["50%", "25%", "25%"],
                            children: [
                                {
                                    type: "text",
                                    id: "linkId",
                                    requiredContent: "img[id]",
                                    label: t.lang.common.id,
                                    setup: function(t, e) {
                                        1 == t &&
                                            this.setValue(e.getAttribute("id"));
                                    },
                                    commit: function(t, e) {
                                        1 == t &&
                                            (this.getValue() ||
                                                this.isChanged()) &&
                                            e.setAttribute(
                                                "id",
                                                this.getValue()
                                            );
                                    }
                                },
                                {
                                    id: "cmbLangDir",
                                    type: "select",
                                    requiredContent: "img[dir]",
                                    style: "width : 100px;",
                                    label: t.lang.common.langDir,
                                    default: "",
                                    items: [
                                        [t.lang.common.notSet, ""],
                                        [t.lang.common.langDirLtr, "ltr"],
                                        [t.lang.common.langDirRtl, "rtl"]
                                    ],
                                    setup: function(t, e) {
                                        1 == t &&
                                            this.setValue(
                                                e.getAttribute("dir")
                                            );
                                    },
                                    commit: function(t, e) {
                                        1 == t &&
                                            (this.getValue() ||
                                                this.isChanged()) &&
                                            e.setAttribute(
                                                "dir",
                                                this.getValue()
                                            );
                                    }
                                },
                                {
                                    type: "text",
                                    id: "txtLangCode",
                                    requiredContent: "img[lang]",
                                    label: t.lang.common.langCode,
                                    default: "",
                                    setup: function(t, e) {
                                        1 == t &&
                                            this.setValue(
                                                e.getAttribute("lang")
                                            );
                                    },
                                    commit: function(t, e) {
                                        1 == t &&
                                            (this.getValue() ||
                                                this.isChanged()) &&
                                            e.setAttribute(
                                                "lang",
                                                this.getValue()
                                            );
                                    }
                                }
                            ]
                        },
                        {
                            type: "text",
                            id: "txtGenLongDescr",
                            requiredContent: "img[longdesc]",
                            label: t.lang.common.longDescr,
                            setup: function(t, e) {
                                1 == t &&
                                    this.setValue(e.getAttribute("longDesc"));
                            },
                            commit: function(t, e) {
                                1 == t &&
                                    (this.getValue() || this.isChanged()) &&
                                    e.setAttribute("longDesc", this.getValue());
                            }
                        },
                        {
                            type: "hbox",
                            widths: ["50%", "50%"],
                            children: [
                                {
                                    type: "text",
                                    id: "txtGenClass",
                                    requiredContent: "img(cke-xyz)",
                                    label: t.lang.common.cssClass,
                                    default: "",
                                    setup: function(t, e) {
                                        1 == t &&
                                            this.setValue(
                                                e.getAttribute("class")
                                            );
                                    },
                                    commit: function(t, e) {
                                        1 == t &&
                                            (this.getValue() ||
                                                this.isChanged()) &&
                                            e.setAttribute(
                                                "class",
                                                this.getValue()
                                            );
                                    }
                                },
                                {
                                    type: "text",
                                    id: "txtGenTitle",
                                    requiredContent: "img[title]",
                                    label: t.lang.common.advisoryTitle,
                                    default: "",
                                    onChange: function() {
                                        g(this.getDialog());
                                    },
                                    setup: function(t, e) {
                                        1 == t &&
                                            this.setValue(
                                                e.getAttribute("title")
                                            );
                                    },
                                    commit: function(t, e) {
                                        1 == t
                                            ? (this.getValue() ||
                                                  this.isChanged()) &&
                                              e.setAttribute(
                                                  "title",
                                                  this.getValue()
                                              )
                                            : 4 == t
                                            ? e.setAttribute(
                                                  "title",
                                                  this.getValue()
                                              )
                                            : 8 == t &&
                                              e.removeAttribute("title");
                                    }
                                }
                            ]
                        },
                        {
                            type: "text",
                            id: "txtdlgGenStyle",
                            requiredContent: "img{cke-xyz}",
                            label: t.lang.common.cssStyle,
                            validate: CKEDITOR.dialog.validate.inlineStyle(
                                t.lang.common.invalidInlineStyle
                            ),
                            default: "",
                            setup: function(t, e) {
                                if (1 == t) {
                                    !(n = e.getAttribute("style")) &&
                                        e.$.style.cssText &&
                                        (n = e.$.style.cssText),
                                        this.setValue(n);
                                    var i = e.$.style.height,
                                        n = e.$.style.width;
                                    (i = (i || "").match(s)),
                                        (n = (n || "").match(s));
                                    this.attributesInStyle = {
                                        height: !!i,
                                        width: !!n
                                    };
                                }
                            },
                            onChange: function() {
                                n.call(
                                    this,
                                    "info:cmbFloat info:cmbAlign info:txtVSpace info:txtHSpace info:txtBorder info:txtWidth info:txtHeight".split(
                                        " "
                                    )
                                ),
                                    g(this);
                            },
                            commit: function(t, e) {
                                1 == t &&
                                    (this.getValue() || this.isChanged()) &&
                                    e.setAttribute("style", this.getValue());
                            }
                        }
                    ]
                }
            ]
        };
    };
    CKEDITOR.dialog.add("image", function(e) {
        return t(e, "image");
    }),
        CKEDITOR.dialog.add("imagebutton", function(e) {
            return t(e, "imagebutton");
        });
})();
