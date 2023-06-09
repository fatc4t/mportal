(function() {
    var m = this,
        aa = function(a) {
            var b = typeof a;
            return "object" == b && null != a || "function" == b
        },
        ba = function(a, b, c) {
            return a.call.apply(a.bind, arguments)
        },
        ca = function(a, b, c) {
            if (!a) throw Error();
            if (2 < arguments.length) {
                var d = Array.prototype.slice.call(arguments, 2);
                return function() {
                    var c = Array.prototype.slice.call(arguments);
                    Array.prototype.unshift.apply(c, d);
                    return a.apply(b, c)
                }
            }
            return function() {
                return a.apply(b, arguments)
            }
        },
        n = function(a, b, c) {
            n = Function.prototype.bind && -1 != Function.prototype.bind.toString().indexOf("native code") ?
                ba : ca;
            return n.apply(null, arguments)
        },
        da = function(a, b) {
            var c = Array.prototype.slice.call(arguments, 1);
            return function() {
                var b = c.slice();
                b.push.apply(b, arguments);
                return a.apply(this, b)
            }
        },
        ea = Date.now || function() {
            return +new Date
        },
        fa = function(a, b) {
            function c() {}
            c.prototype = b.prototype;
            a.ga = b.prototype;
            a.prototype = new c;
            a.prototype.constructor = a;
            a.Ia = function(a, c, e) {
                for (var g = Array(arguments.length - 2), h = 2; h < arguments.length; h++) g[h - 2] = arguments[h];
                return b.prototype[c].apply(a, g)
            }
        };
    var ga = (new Date).getTime();
    var q = function(a) {
            a = parseFloat(a);
            return isNaN(a) || 1 < a || 0 > a ? 0 : a
        },
        ha = function(a, b) {
            var c = parseInt(a, 10);
            return isNaN(c) ? b : c
        },
        ia = function(a, b) {
            return /^true$/.test(a) ? !0 : /^false$/.test(a) ? !1 : b
        },
        ja = /^([\w-]+\.)*([\w-]{2,})(\:[0-9]+)?$/,
        ka = function(a, b) {
            if (!a) return b;
            var c = a.match(ja);
            return c ? c[0] : b
        };
    var la = ha("101", -1),
        ma = ha("0", 0),
        na = q("0.05"),
        oa = q("0.001"),
        pa = q("0.001"),
        qa = q("0.01"),
        ta = q("0.0"),
        ua = q(""),
        va = q("0.1");
    var wa = function() {
            return "r20160419"
        },
        xa = ia("false", !1),
        ya = ia("false", !1),
        za = ia("false", !1),
        Aa = za || !ya;
    var t = function(a) {
        t[" "](a);
        return a
    };
    t[" "] = function() {};
    var Ba = function(a) {
            try {
                var b;
                if (b = !!a && null != a.location.href) a: {
                    try {
                        t(a.foo);
                        b = !0;
                        break a
                    } catch (c) {}
                    b = !1
                }
                return b
            } catch (c) {
                return !1
            }
        },
        Ca = function(a, b) {
            return b.getComputedStyle ? b.getComputedStyle(a, null) : a.currentStyle
        },
        Ea = function(a, b) {
            if (!(1E-4 > Math.random())) {
                var c = Math.random();
                if (c < b) return c = Da(window), a[Math.floor(c * a.length)]
            }
            return null
        },
        Da = function(a) {
            try {
                var b = new Uint32Array(1);
                a.crypto.getRandomValues(b);
                return b[0] / 65536 / 65536
            } catch (c) {
                return Math.random()
            }
        },
        Fa = function(a, b) {
            for (var c in a) Object.prototype.hasOwnProperty.call(a,
                c) && b.call(void 0, a[c], c, a)
        },
        Ga = function(a) {
            var b = a.length;
            if (0 == b) return 0;
            for (var c = 305419896, d = 0; d < b; d++) c ^= (c << 5) + (c >> 2) + a.charCodeAt(d) & 4294967295;
            return 0 < c ? c : 4294967296 + c
        },
        Ha = /^([0-9.]+)px$/,
        Ia = /^(-?[0-9.]{1,30})$/,
        Ja = function(a) {
            return Ia.test(a) && (a = Number(a), !isNaN(a)) ? a : null
        },
        Ka = function(a) {
            return (a = Ha.exec(a)) ? +a[1] : null
        };
    var La = function(a, b, c) {
        a.addEventListener ? a.addEventListener(b, c, !1) : a.attachEvent && a.attachEvent("on" + b, c)
    };
    var Oa = function(a, b, c, d, f, e) {
            try {
                if ((d ? a.da : Math.random()) < (f || a.V)) {
                    var g = a.T + b + ("&" + Ma(c, 1)),
                        g = g.substring(0, 2E3);
                    "undefined" === typeof e ? Na(g) : Na(g, e)
                }
            } catch (h) {}
        },
        Ma = function(a, b) {
            var c = [];
            Fa(a, function(a, f) {
                var e = null;
                if (aa(a) && 2 > b) e = Ma(a, b + 1);
                else if (0 === a || a) e = String(a);
                e && c.push(f + "=" + encodeURIComponent(e))
            });
            return c.join("&")
        },
        Na = function(a, b) {
            m.google_image_requests || (m.google_image_requests = []);
            var c = m.document.createElement("img");
            if (b) {
                var d = function(a) {
                    b(a);
                    a = d;
                    c.removeEventListener ?
                        c.removeEventListener("load", a, !1) : c.detachEvent && c.detachEvent("onload", a);
                    a = d;
                    c.removeEventListener ? c.removeEventListener("error", a, !1) : c.detachEvent && c.detachEvent("onerror", a)
                };
                La(c, "load", d);
                La(c, "error", d)
            }
            c.src = a;
            m.google_image_requests.push(c)
        };
    var Pa = function(a, b, c) {
            this.aa = a;
            this.X = b;
            this.G = c;
            this.B = null;
            this.W = this.s;
            this.ka = !1
        },
        Qa = function(a, b, c) {
            this.message = a;
            this.fileName = b || "";
            this.lineNumber = c || -1
        },
        Sa = function(a, b, c, d, f, e) {
            var g;
            try {
                g = c()
            } catch (p) {
                var h = a.G;
                try {
                    var k = Ra(p),
                        h = (e || a.W).call(a, b, k, void 0, d)
                } catch (l) {
                    a.s("pAR", l)
                }
                if (!h) throw p;
            } finally {
                if (f) try {
                    f()
                } catch (p) {}
            }
            return g
        },
        x = function(a, b, c) {
            var d = w;
            return function() {
                for (var f = [], e = 0; e < arguments.length; ++e) f[e] = arguments[e];
                return Sa(d, a, function() {
                    return b.apply(void 0,
                        f)
                }, void 0, c)
            }
        };
    Pa.prototype.s = function(a, b, c, d, f) {
        var e = {};
        e.context = a;
        b instanceof Qa || (b = Ra(b));
        e.msg = b.message.substring(0, 512);
        b.fileName && (e.file = b.fileName);
        0 < b.lineNumber && (e.line = b.lineNumber.toString());
        a = m.document;
        e.url = a.URL.substring(0, 512);
        e.ref = (a.referrer || "").substring(0, 512);
        Ta(this, e, d);
        Oa(this.aa, f || this.X, e, this.ka, c);
        return this.G
    };
    var Ta = function(a, b, c) {
            if (a.B) try {
                a.B(b)
            } catch (d) {}
            if (c) try {
                c(b)
            } catch (d) {}
        },
        Ra = function(a) {
            var b = a.toString();
            a.name && -1 == b.indexOf(a.name) && (b += ": " + a.name);
            a.message && -1 == b.indexOf(a.message) && (b += ": " + a.message);
            if (a.stack) {
                var c = a.stack,
                    d = b;
                try {
                    -1 == c.indexOf(d) && (c = d + "\n" + c);
                    for (var f; c != f;) f = c, c = c.replace(/((https?:\/..*\/)[^\/:]*:\d+(?:.|\n)*)\2/, "$1");
                    b = c.replace(/\n */g, "\n")
                } catch (e) {
                    b = d
                }
            }
            return new Qa(b, a.fileName, a.lineNumber)
        };
    var Ua = String.prototype.trim ? function(a) {
            return a.trim()
        } : function(a) {
            return a.replace(/^[\s\xa0]+|[\s\xa0]+$/g, "")
        },
        Va = /&/g,
        Wa = /</g,
        Xa = />/g,
        Ya = /"/g,
        Za = /'/g,
        $a = /\x00/g,
        ab = {
            "\x00": "\\0",
            "\b": "\\b",
            "\f": "\\f",
            "\n": "\\n",
            "\r": "\\r",
            "\t": "\\t",
            "\x0B": "\\x0B",
            '"': '\\"',
            "\\": "\\\\",
            "<": "<"
        },
        bb = {
            "'": "\\'"
        },
        cb = function(a, b) {
            return a < b ? -1 : a > b ? 1 : 0
        };
    var db = Array.prototype.forEach ? function(a, b, c) {
        Array.prototype.forEach.call(a, b, c)
    } : function(a, b, c) {
        for (var d = a.length, f = "string" == typeof a ? a.split("") : a, e = 0; e < d; e++) e in f && b.call(c, f[e], e, a)
    };
    var A;
    a: {
        var eb = m.navigator;
        if (eb) {
            var fb = eb.userAgent;
            if (fb) {
                A = fb;
                break a
            }
        }
        A = ""
    }
    var B = function(a) {
        return -1 != A.indexOf(a)
    };
    var gb = B("Opera"),
        C = B("Trident") || B("MSIE"),
        hb = B("Edge"),
        E = B("Gecko") && !(-1 != A.toLowerCase().indexOf("webkit") && !B("Edge")) && !(B("Trident") || B("MSIE")) && !B("Edge"),
        ib = -1 != A.toLowerCase().indexOf("webkit") && !B("Edge"),
        jb = function() {
            var a = m.document;
            return a ? a.documentMode : void 0
        },
        kb;
    a: {
        var lb = "",
            mb = function() {
                var a = A;
                if (E) return /rv\:([^\);]+)(\)|;)/.exec(a);
                if (hb) return /Edge\/([\d\.]+)/.exec(a);
                if (C) return /\b(?:MSIE|rv)[: ]([^\);]+)(\)|;)/.exec(a);
                if (ib) return /WebKit\/(\S+)/.exec(a);
                if (gb) return /(?:Version)[ \/]?(\S+)/.exec(a)
            }();mb && (lb = mb ? mb[1] : "");
        if (C) {
            var nb = jb();
            if (null != nb && nb > parseFloat(lb)) {
                kb = String(nb);
                break a
            }
        }
        kb = lb
    }
    var ob = kb,
        pb = {},
        qb = function(a) {
            if (!pb[a]) {
                for (var b = 0, c = Ua(String(ob)).split("."), d = Ua(String(a)).split("."), f = Math.max(c.length, d.length), e = 0; 0 == b && e < f; e++) {
                    var g = c[e] || "",
                        h = d[e] || "",
                        k = RegExp("(\\d*)(\\D*)", "g"),
                        p = RegExp("(\\d*)(\\D*)", "g");
                    do {
                        var l = k.exec(g) || ["", "", ""],
                            D = p.exec(h) || ["", "", ""];
                        if (0 == l[0].length && 0 == D[0].length) break;
                        b = cb(0 == l[1].length ? 0 : parseInt(l[1], 10), 0 == D[1].length ? 0 : parseInt(D[1], 10)) || cb(0 == l[2].length, 0 == D[2].length) || cb(l[2], D[2])
                    } while (0 == b)
                }
                pb[a] = 0 <= b
            }
        },
        rb = m.document,
        sb = rb && C ? jb() || ("CSS1Compat" == rb.compatMode ? parseInt(ob, 10) : 5) : void 0;
    var tb;
    if (!(tb = !E && !C)) {
        var ub;
        if (ub = C) ub = 9 <= Number(sb);
        tb = ub
    }
    tb || E && qb("1.9.1");
    C && qb("9");
    var F = document,
        G = window;
    var vb = Object.prototype.hasOwnProperty,
        H = function(a, b) {
            for (var c in a) vb.call(a, c) && b.call(void 0, a[c], c, a)
        },
        I = function(a) {
            return !(!a || !a.call) && "function" === typeof a
        },
        wb = function(a, b) {
            for (var c = 1, d = arguments.length; c < d; ++c) a.push(arguments[c])
        },
        M = function(a, b) {
            if (a.indexOf) {
                var c = a.indexOf(b);
                return 0 < c || 0 === c
            }
            for (c = 0; c < a.length; c++)
                if (a[c] === b) return !0;
            return !1
        },
        xb = function(a) {
            a.google_unique_id ? ++a.google_unique_id : a.google_unique_id = 1
        },
        yb = /(^| )adsbygoogle($| )/,
        zb = {
            "http://googleads.g.doubleclick.net": !0,
            "http://pagead2.googlesyndication.com": !0,
            "https://googleads.g.doubleclick.net": !0,
            "https://pagead2.googlesyndication.com": !0
        },
        Ab = /\.google\.com(:\d+)?$/,
        Bb = function(a) {
            a = xa && a.google_top_window || a.top;
            return Ba(a) ? a : null
        };
    var Cb, w;
    Cb = new function() {
        this.T = "http" + ("http:" === G.location.protocol ? "" : "s") + "://pagead2.googlesyndication.com/pagead/gen_204?id=";
        this.V = .01;
        this.da = Math.random()
    };
    w = new Pa(Cb, "jserror", !0);
    var Eb = function() {
            var a = [Db];
            w.B = function(b) {
                db(a, function(a) {
                    a(b)
                })
            }
        },
        Fb = function(a, b, c, d) {
            Sa(w, a, c, d, void 0, b)
        },
        Gb = w.s,
        Hb = function(a, b, c) {
            Oa(Cb, a, b, "jserror" != a, c, void 0)
        };
    var Ib = function(a, b) {
        this.start = a < b ? a : b;
        this.end = a < b ? b : a
    };
    Ib.prototype.clone = function() {
        return new Ib(this.start, this.end)
    };
    var Jb = function(a) {
        var b;
        try {
            b = parseInt(a.localStorage.getItem("google_experiment_mod"), 10)
        } catch (c) {
            return null
        }
        if (0 <= b && 1E3 > b) return b;
        b = Math.floor(1E3 * Da(a));
        try {
            return a.localStorage.setItem("google_experiment_mod", "" + b), b
        } catch (c) {
            return null
        }
    };
    var Kb = null,
        Lb = function() {
            if (!Kb) {
                for (var a = window, b = a, c = 0; a && a != a.parent;)
                    if (a = a.parent, c++, Ba(a)) b = a;
                    else break;
                Kb = b
            }
            return Kb
        };
    var Mb = {
            l: "10573511",
            j: "10573512"
        },
        Nb = {
            l: "10573695",
            j: "10573696"
        },
        N = {
            qa: {},
            Ha: {
                l: "453848100",
                j: "453848101"
            },
            Aa: {
                l: "24819308",
                j: "24819309",
                na: "24819320",
                ta: "24819321"
            },
            za: {
                l: "24819330",
                j: "24819331"
            },
            wa: {
                l: "86724438",
                j: "86724439"
            },
            Da: {
                l: "10573505",
                j: "10573506"
            },
            A: {
                l: "10573595",
                j: "10573596"
            },
            J: {
                l: "10573581",
                j: "10573582"
            },
            Ca: {
                l: "10573605",
                j: "10573606"
            },
            ra: {
                l: "26835105",
                j: "26835106"
            },
            va: {
                l: "35923720",
                j: "35923721"
            },
            H: {
                l: "35923760",
                j: "35923761"
            },
            I: {
                l: "20040000",
                j: "20040001"
            },
            oa: {
                l: "20040016",
                j: "20040017"
            },
            Ba: {
                l: "19188000",
                j: "19188001"
            },
            pa: {
                ma: "314159230",
                ya: "314159231"
            },
            xa: {
                Ea: "27285692",
                Ga: "27285712",
                Fa: "27285713"
            },
            sa: {
                l: "29222061",
                j: "29222062",
                ua: "29222063"
            }
        };
    var Pb = function() {},
        Rb = function(a, b, c) {
            switch (typeof b) {
                case "string":
                    Qb(b, c);
                    break;
                case "number":
                    c.push(isFinite(b) && !isNaN(b) ? String(b) : "null");
                    break;
                case "boolean":
                    c.push(String(b));
                    break;
                case "undefined":
                    c.push("null");
                    break;
                case "object":
                    if (null == b) {
                        c.push("null");
                        break
                    }
                    if (b instanceof Array || void 0 != b.length && b.splice) {
                        var d = b.length;
                        c.push("[");
                        for (var f = "", e = 0; e < d; e++) c.push(f), Rb(a, b[e], c), f = ",";
                        c.push("]");
                        break
                    }
                    c.push("{");
                    d = "";
                    for (f in b) b.hasOwnProperty(f) && (e = b[f], "function" != typeof e &&
                        (c.push(d), Qb(f, c), c.push(":"), Rb(a, e, c), d = ","));
                    c.push("}");
                    break;
                case "function":
                    break;
                default:
                    throw Error("Unknown type: " + typeof b);
            }
        },
        Sb = {
            '"': '\\"',
            "\\": "\\\\",
            "/": "\\/",
            "\b": "\\b",
            "\f": "\\f",
            "\n": "\\n",
            "\r": "\\r",
            "\t": "\\t",
            "\x0B": "\\u000b"
        },
        Tb = /\uffff/.test("\uffff") ? /[\\\"\x00-\x1f\x7f-\uffff]/g : /[\\\"\x00-\x1f\x7f-\xff]/g,
        Qb = function(a, b) {
            b.push('"');
            b.push(a.replace(Tb, function(a) {
                if (a in Sb) return Sb[a];
                var b = a.charCodeAt(0),
                    f = "\\u";
                16 > b ? f += "000" : 256 > b ? f += "00" : 4096 > b && (f += "0");
                return Sb[a] =
                    f + b.toString(16)
            }));
            b.push('"')
        };
    var Ub = B("Safari") && !((B("Chrome") || B("CriOS")) && !B("Edge") || B("Coast") || B("Opera") || B("Edge") || B("Silk") || B("Android")) && !(B("iPhone") && !B("iPod") && !B("iPad") || B("iPad") || B("iPod"));
    var Vb = null,
        Wb = E || ib && !Ub || gb || "function" == typeof m.btoa;
    var Xb = {
            google_ad_modifications: !0,
            google_analytics_domain_name: !0,
            google_analytics_uacct: !0
        },
        Yb = function(a) {
            a.google_page_url && (a.google_page_url = String(a.google_page_url));
            var b = [];
            H(a, function(a, d) {
                if (null != a) {
                    var f;
                    try {
                        var e = [];
                        Rb(new Pb, a, e);
                        f = e.join("")
                    } catch (g) {}
                    f && (f = f.replace(/\//g, "\\$&"), wb(b, d, "=", f, ";"))
                }
            });
            return b.join("")
        };
    var Zb = function(a, b, c) {
            Fb("files::getSrc", Gb, function() {
                if ("https:" == G.location.protocol && "http" == c) throw c = "https", Error("Requested url " + a + b);
            });
            return [c, "://", a, b].join("")
        },
        $b = function(a, b, c) {
            c || (c = Aa ? "https" : "http");
            return Zb(a, b, c)
        };
    var ac = function(a) {
            return (a = a.google_ad_modifications) ? a.eids || [] : []
        },
        O = function(a) {
            return (a = a.google_ad_modifications) ? a.loeids || [] : []
        },
        bc = function(a, b, c) {
            if (!a) return null;
            for (var d = 0; d < a.length; ++d)
                if ((a[d].ad_slot || b) == b && (a[d].ad_tag_origin || c) == c) return a[d];
            return null
        };
    var cc = {
        overlays: 1,
        interstitials: 2,
        vignettes: 2,
        inserts: 3,
        immersives: 4
    };
    var R = function(a) {
        a = a.document;
        return ("CSS1Compat" == a.compatMode ? a.documentElement : a.body) || {}
    };
    var dc = function() {
        this.wasReactiveAdConfigReceived = {};
        this.wasReactiveAdCreated = {};
        this.wasReactiveAdVisible = {};
        this.stateForType = {};
        this.reactiveTypeEnabledByReactiveTag = {};
        this.wasPubConfigAvailableAtHandlerRegistration = this.wasReactiveAdConfigHandlerRegistered = this.wasReactiveTagRequestSent = !1;
        this.reactiveTypeDisabledByPublisher = {};
        this.debugCard = null;
        this.debugCardRequested = !1;
        this.interstitialJSRefactorExperiment = 0;
        this.vignetteNmoScaledExperimentAndEligible = this.vignetteNmoScaledExperiment =
            this.floatingNmoOrientationExperimentAndEligible = this.floatingNmoOrientationExperiment = this.floatingNmoScaledExperimentAndEligible = this.floatingNmoScaledExperiment = !1
    };
    var ec = function(a) {
        a.google_reactive_ads_global_state || (a.google_reactive_ads_global_state = new dc);
        a = a.google_reactive_ads_global_state;
        return !!a && a.vignetteNmoScaledExperimentAndEligible
    };
    var fc = new function() {
        this.ba = new Ib(100, 199)
    };
    var gc = function(a, b, c) {
            return G.location && G.location.hash == "#google_plle_" + c ? c : Ea([b, c], a)
        },
        hc = function(a, b, c, d) {
            a = a.google_active_plles = a.google_active_plles || [];
            M(O(b), c) || M(ac(b), c) ? a.push(c) : (M(O(b), d) || M(ac(b), d)) && a.push(d)
        };
    var ic = function() {
        return !(B("iPad") || B("Android") && !B("Mobile") || B("Silk")) && (B("iPod") || B("iPhone") || B("Android") || B("IEMobile"))
    };
    var jc = function(a) {
            this.o = a;
            a.google_iframe_oncopy || (a.google_iframe_oncopy = {
                handlers: {},
                upd: n(this.ja, this)
            });
            this.fa = a.google_iframe_oncopy
        },
        kc;
    var S = "var i=this.id,s=window.google_iframe_oncopy,H=s&&s.handlers,h=H&&H[i],w=this.contentWindow,d;try{d=w.document}catch(e){}if(h&&d&&(!d.body||!d.body.firstChild)){if(h.call){setTimeout(h,0)}else if(h.match){try{h=s.upd(h,i)}catch(e){}w.location.replace(h)}}";
    /[\x00&<>"']/.test(S) && (-1 != S.indexOf("&") && (S = S.replace(Va, "&amp;")), -1 != S.indexOf("<") && (S = S.replace(Wa, "&lt;")), -1 != S.indexOf(">") && (S = S.replace(Xa, "&gt;")), -1 != S.indexOf('"') && (S = S.replace(Ya, "&quot;")), -1 != S.indexOf("'") && (S = S.replace(Za, "&#39;")), -1 != S.indexOf("\x00") && (S = S.replace($a, "&#0;")));
    kc = S;
    jc.prototype.set = function(a, b) {
        this.fa.handlers[a] = b;
        this.o.addEventListener && this.o.addEventListener("load", n(this.Y, this, a), !1)
    };
    jc.prototype.Y = function(a) {
        a = this.o.document.getElementById(a);
        try {
            var b = a.contentWindow.document;
            if (a.onload && b && (!b.body || !b.body.firstChild)) a.onload()
        } catch (c) {}
    };
    jc.prototype.ja = function(a, b) {
        var c = lc("rx", a),
            d;
        a: {
            if (a && (d = a.match("dt=([^&]+)")) && 2 == d.length) {
                d = d[1];
                break a
            }
            d = ""
        }
        d = (new Date).getTime() - d;
        c = c.replace(/&dtd=(\d+|-?M)/, "&dtd=" + (1E5 <= d ? "M" : 0 <= d ? d : "-M"));
        this.set(b, c);
        return c
    };
    var lc = function(a, b) {
        var c = new RegExp("\\b" + a + "=(\\d+)"),
            d = c.exec(b);
        d && (b = b.replace(c, a + "=" + (+d[1] + 1 || 1)));
        return b
    };
    E || ib || C && qb(11);
    var mc = !1,
        nc = function(a, b, c) {
            var d = ["<iframe"],
                f;
            for (f in a) a.hasOwnProperty(f) && wb(d, f + "=" + a[f]);
            f = "left:0;position:absolute;top:0;";
            mc && (f = f + "width:" + b + "px;height:" + c + "px;");
            d.push('style="' + f + '"');
            d.push("></iframe>");
            a = a.id;
            b = "border:none;height:" + c + "px;margin:0;padding:0;position:relative;visibility:visible;width:" + b + "px;background-color:transparent";
            return ['<ins id="', a + "_expand", '" style="display:inline-table;', b, '"><ins id="', a + "_anchor", '" style="display:block;', b, '">', d.join(" "), "</ins></ins>"].join("")
        };
    var oc = function(a) {
        if (!a) return "";
        (a = a.toLowerCase()) && "ca-" != a.substring(0, 3) && (a = "ca-" + a);
        return a
    };
    var pc = null;
    var qc = {
        "120x90": !0,
        "160x90": !0,
        "180x90": !0,
        "200x90": !0,
        "468x15": !0,
        "728x15": !0
    };
    var rc, T = function(a) {
            this.w = [];
            this.o = a || window;
            this.m = 0;
            this.v = null;
            this.S = 0
        },
        sc = function(a, b) {
            this.Z = a;
            this.la = b
        };
    T.prototype.enqueue = function(a, b) {
        0 != this.m || 0 != this.w.length || b && b != window ? this.L(a, b) : (this.m = 2, this.P(new sc(a, window)))
    };
    T.prototype.L = function(a, b) {
        this.w.push(new sc(a, b || this.o));
        tc(this)
    };
    T.prototype.$ = function(a) {
        this.m = 1;
        if (a) {
            var b = n(this.O, this, !0);
            this.v = this.o.setTimeout(x("sjr::timeout", b, void 0), a)
        }
    };
    T.prototype.O = function(a) {
        a && ++this.S;
        1 == this.m && (null != this.v && (this.o.clearTimeout(this.v), this.v = null), this.m = 0);
        tc(this)
    };
    T.prototype.ea = function() {
        return !(!window || !Array)
    };
    T.prototype.ha = function() {
        return this.S
    };
    T.prototype.nq = T.prototype.enqueue;
    T.prototype.nqa = T.prototype.L;
    T.prototype.al = T.prototype.$;
    T.prototype.rl = T.prototype.O;
    T.prototype.sz = T.prototype.ea;
    T.prototype.tc = T.prototype.ha;
    var tc = function(a) {
        var b = n(a.ia, a);
        a.o.setTimeout(x("sjr::tryrun", b, void 0), 0)
    };
    T.prototype.ia = function() {
        if (0 == this.m && this.w.length) {
            var a = this.w.shift();
            this.m = 2;
            var b = n(this.P, this, a);
            a.la.setTimeout(x("sjr::run", b, void 0), 0);
            tc(this)
        }
    };
    T.prototype.P = function(a) {
        this.m = 0;
        a.Z()
    };
    var uc = function(a) {
            try {
                return a.sz()
            } catch (b) {
                return !1
            }
        },
        vc = function(a) {
            return !!a && ("object" === typeof a || "function" === typeof a) && uc(a) && I(a.nq) && I(a.nqa) && I(a.al) && I(a.rl)
        },
        wc = function() {
            if (rc && uc(rc)) return rc;
            var a = Lb(),
                b = a.google_jobrunner;
            return vc(b) ? rc = b : a.google_jobrunner = rc = new T(a)
        },
        xc = function(a, b) {
            wc().nq(a, b)
        },
        yc = function(a, b) {
            wc().nqa(a, b)
        };
    var V = function(a) {
        this.name = "TagError";
        this.message = a || "";
        Error.captureStackTrace ? Error.captureStackTrace(this, V) : this.stack = Error().stack || ""
    };
    fa(V, Error);
    var zc = function() {
            var a = za ? "https" : "http",
                b = t("script"),
                c;
            a: {
                if (xa) try {
                    var d = G.google_cafe_host || G.top.google_cafe_host;
                    if (d) {
                        c = d;
                        break a
                    }
                } catch (f) {}
                c = ka("", "pagead2.googlesyndication.com")
            }
            return ["<", b, ' src="', $b(c, ["/pagead/js/", wa(), "/r20151006/show_ads_impl.js"].join(""), a), '"></', b, ">"].join("")
        },
        Ac = function(a, b, c, d) {
            return function() {
                var f = !1;
                d && wc().al(3E4);
                try {
                    var e = a.document.getElementById(b).contentWindow;
                    if (Ba(e)) {
                        var g = a.document.getElementById(b).contentWindow,
                            h = g.document;
                        h.body && h.body.firstChild || (/Firefox/.test(navigator.userAgent) ? h.open("text/html", "replace") : h.open(), g.google_async_iframe_close = !0, h.write(c))
                    } else {
                        for (var k = a.document.getElementById(b).contentWindow, e = c, e = String(e), g = ['"'], h = 0; h < e.length; h++) {
                            var p = e.charAt(h),
                                l = p.charCodeAt(0),
                                D = h + 1,
                                P;
                            if (!(P = ab[p])) {
                                var z;
                                if (31 < l && 127 > l) z = p;
                                else {
                                    var u = p;
                                    if (u in bb) z =
                                        bb[u];
                                    else if (u in ab) z = bb[u] = ab[u];
                                    else {
                                        var r = u,
                                            v = u.charCodeAt(0);
                                        if (31 < v && 127 > v) r = u;
                                        else {
                                            if (256 > v) {
                                                if (r = "\\x", 16 > v || 256 < v) r += "0"
                                            } else r = "\\u", 4096 > v && (r += "0");
                                            r += v.toString(16).toUpperCase()
                                        }
                                        z = bb[u] = r
                                    }
                                }
                                P = z
                            }
                            g[D] = P
                        }
                        g.push('"');
                        k.location.replace("javascript:" + g.join(""))
                    }
                    f = !0
                } catch (L) {
                    k = Lb().google_jobrunner, vc(k) && k.rl()
                }
                f && (f = lc("google_async_rrc", c), (new jc(a)).set(b, Ac(a, b, f, !1)))
            }
        },
        Bc = function(a) {
            var b = ["<iframe"];
            H(a, function(a, d) {
                null != a && b.push(" " + d + '="' + a + '"')
            });
            b.push("></iframe>");
            return b.join("")
        },
        Dc = function(a, b, c) {
            Cc(a, b, c, function(a, b, e) {
                for (var g = a.document, h = b.id, k = 0; !h || g.getElementById(h);) h = "aswift_" + k++;
                b.id = h;
                b.name = h;
                var h = Number(e.google_ad_width),
                    k = Number(e.google_ad_height),
                    p = N.H;
                hc(e, a, p.l, p.j);
                mc = M(O(a), p.j);
                16 == e.google_reactive_ad_format ? (a = g.createElement("div"), e = nc(b, h, k), a.innerHTML = e, c.appendChild(a.firstChild)) : (a = nc(b, h, k), c.innerHTML = a);
                return b.id
            })
        },
        Cc = function(a, b, c, d) {
            var f = t("script"),
                e = {},
                g = b.google_ad_height;
            e.width = '"' + b.google_ad_width + '"';
            e.height = '"' + g +
                '"';
            e.frameborder = '"0"';
            e.marginwidth = '"0"';
            e.marginheight = '"0"';
            e.vspace = '"0"';
            e.hspace = '"0"';
            e.allowtransparency = '"true"';
            e.scrolling = '"no"';
            e.allowfullscreen = '"true"';
            e.onload = '"' + kc + '"';
            d = d(a, e, b);
            g = b.google_ad_output;
            (e = b.google_ad_format) || "html" != g && null != g || (e = b.google_ad_width + "x" + b.google_ad_height);
            g = !b.google_ad_slot || b.google_override_format || !qc[b.google_ad_width + "x" + b.google_ad_height] && "aa" == b.google_loader_used;
            e = e && g ? e.toLowerCase() : "";
            b.google_ad_format = e;
            for (var e = [b.google_ad_slot,
                    b.google_ad_format, b.google_ad_type, b.google_ad_width, b.google_ad_height
                ], g = [], h = 0, k = c; k && 25 > h; k = k.parentNode, ++h) g.push(9 !== k.nodeType && k.id || "");
            (g = g.join()) && e.push(g);
            b.google_ad_unit_key = Ga(e.join(":")).toString();
            e = a.google_adk2_experiment = a.google_adk2_experiment || Ea(["C", "E"], pa) || "N";
            if ("E" == e) {
                e = [];
                for (g = 0; c && 25 > g; ++g) {
                    h = "";
                    h = (h = 9 !== c.nodeType && c.id) ? "/" + h : "";
                    a: {
                        if (c && c.nodeName && c.parentElement)
                            for (var k = c.nodeName.toString().toLowerCase(), p = c.parentElement.childNodes, l = 0, D = 0; D < p.length; ++D) {
                                var P =
                                    p[D];
                                if (P.nodeName && P.nodeName.toString().toLowerCase() === k) {
                                    if (c === P) {
                                        k = "." + l;
                                        break a
                                    }++l
                                }
                            }
                        k = ""
                    }
                    e.push((c.nodeName && c.nodeName.toString().toLowerCase()) + h + k);
                    c = c.parentElement
                }
                c = e.join() + ":";
                e = a;
                g = [];
                if (e) try {
                    for (var z = e.parent, h = 0; z && z !== e && 25 > h; ++h) {
                        for (var u = z.frames, k = 0; k < u.length; ++k)
                            if (e === u[k]) {
                                g.push(k);
                                break
                            }
                        e = z;
                        z = e.parent
                    }
                } catch (ra) {}
                b.google_ad_dom_fingerprint = Ga(c + g.join()).toString()
            } else "C" == e && (b.google_ad_dom_fingerprint = "ctrl");
            z = Yb(b);
            u = null;
            c = Ea(["C", "E"], qa);
            if ("E" == c) {
                a: {
                    try {
                        if (window.JSON &&
                            window.JSON.stringify && window.encodeURIComponent) {
                            var r = encodeURIComponent(window.JSON.stringify(b)),
                                v;
                            if (Wb) v = m.btoa(r);
                            else {
                                e = [];
                                for (h = g = 0; h < r.length; h++) {
                                    for (var L = r.charCodeAt(h); 255 < L;) e[g++] = L & 255, L >>= 8;
                                    e[g++] = L
                                }
                                if (!Vb)
                                    for (Vb = {}, r = 0; 65 > r; r++) Vb[r] = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=".charAt(r);
                                r = Vb;
                                L = [];
                                for (g = 0; g < e.length; g += 3) {
                                    var J = e[g],
                                        sa = g + 1 < e.length,
                                        Q = sa ? e[g + 1] : 0,
                                        U = g + 2 < e.length,
                                        Ob = U ? e[g + 2] : 0,
                                        h = J >> 2,
                                        k = (J & 3) << 4 | Q >> 4,
                                        p = (Q & 15) << 2 | Ob >> 6,
                                        l = Ob & 63;
                                    U || (l = 64, sa || (p =
                                        64));
                                    L.push(r[h], r[k], r[p], r[l])
                                }
                                v = L.join("")
                            }
                            u = v;
                            break a
                        }
                        Hb("sblob", {
                            json: window.JSON ? 1 : 0,
                            eURI: window.encodeURIComponent ? 1 : 0
                        })
                    } catch (ra) {
                        w.s("sblob", ra, void 0, void 0)
                    }
                    u = ""
                }
                u || (u = "{X}")
            }
            else "C" == c && (u = "{C}");
            var y;
            b = b.google_ad_client;
            if (!pc) b: {
                J = [m.top];v = [];
                for (sa = 0; Q = J[sa++];) {
                    v.push(Q);
                    try {
                        if (Q.frames)
                            for (var K = Q.frames.length, U = 0; U < K && 1024 > J.length; ++U) J.push(Q.frames[U])
                    } catch (ra) {}
                }
                for (K = 0; K < v.length; K++) try {
                    if (y = v[K].frames.google_esf) {
                        pc = y;
                        break b
                    }
                } catch (ra) {}
                pc = null
            }
            pc ? y = "" : (y = {
                    style: "display:none"
                },
                /[^a-z0-9-]/.test(b) ? y = "" : (y["data-ad-client"] = oc(b), y.id = "google_esf", y.name = "google_esf", y.src = $b(ka("", "googleads.g.doubleclick.net"), ["/pagead/html/", wa(), "/r20151006/zrt_lookup.html"].join("")), y = Bc(y)));
            K = ga;
            b = (new Date).getTime();
            if (v = a.google_async_for_oa_experiment) a.google_async_for_oa_experiment = void 0;
            J = a.google_unique_id;
            y = ["<!doctype html><html><body>", y, "<", f, ">", z, "google_show_ads_impl=true;google_unique_id=",
                "number" === typeof J ? J : 0, ';google_async_iframe_id="', d, '";google_start_time=', K, ";", c ? 'google_pub_vars = "' + u + '";' : "", v ? 'google_async_for_oa_experiment="' + v + '";' : "", "google_bpp=", b > K ? b - K : 1, ";google_async_rrc=0;google_iframe_start_time=new Date().getTime();</", f, ">", zc(), "</body></html>"
            ].join("");
            (a.document.getElementById(d) ? xc : yc)(Ac(a, d, y, !0))
        },
        Ec = function(a, b) {
            var c = navigator;
            if (a && b && c) {
                var c = a.document,
                    d = oc(b);
                if (!/[^a-z0-9-]/.test(d)) {
                    var f = Ua("r20160212");
                    f && (f += "/");
                    f = $b("pagead2.googlesyndication.com",
                        "/pub-config/" + f + d + ".js");
                    d = c.createElement("script");
                    d.src = f;
                    (c = c.getElementsByTagName("script")[0]) && c.parentNode && c.parentNode.insertBefore(d, c)
                }
            }
        };
    var W = function(a, b) {
        this.N = a;
        this.M = b
    };
    W.prototype.minWidth = function() {
        return this.N
    };
    W.prototype.height = function() {
        return this.M
    };
    W.prototype.u = function(a) {
        return 300 < a && 300 < this.M ? this.N : 1200 < a ? 1200 : Math.round(a)
    };
    W.prototype.F = function(a) {
        return this.u(a) + "x" + this.height()
    };
    var Fc = {
            rectangle: 1,
            horizontal: 2,
            vertical: 4
        },
        X = function(a, b, c) {
            W.call(this, a, b);
            this.ca = c
        };
    fa(X, W);
    var Gc = function(a) {
            return function(b) {
                return !!(b.ca & a)
            }
        },
        Hc = [new X(970, 90, 2), new X(728, 90, 2), new X(468, 60, 2), new X(336, 280, 1), new X(320, 100, 2), new X(320, 50, 2), new X(300, 600, 4), new X(300, 250, 1), new X(250, 250, 1), new X(234, 60, 2), new X(200, 200, 1), new X(180, 150, 1), new X(160, 600, 4), new X(125, 125, 1), new X(120, 600, 4), new X(120, 240, 4)];
    var Ic = function(a, b) {
            for (var c = ["width", "height"], d = 0; d < c.length; d++) {
                var f = "google_ad_" + c[d];
                if (!b.hasOwnProperty(f)) {
                    var e;
                    e = Ka(a[c[d]]);
                    e = null === e ? null : Math.round(e);
                    null != e && (b[f] = e)
                }
            }
        },
        Jc = function(a, b) {
            try {
                var c = b.document.documentElement.getBoundingClientRect(),
                    d = a.getBoundingClientRect();
                return {
                    x: d.left - c.left,
                    y: d.top - c.top
                }
            } catch (f) {
                return null
            }
        },
        Kc = function(a) {
            var b = 0,
                c;
            for (c in Fc) - 1 != a.indexOf(c) && (b |= Fc[c]);
            return b
        };
    var Lc = function(a) {
            return function(b) {
                return b.minWidth() <= a
            }
        },
        Nc = function(a, b, c) {
            var d = a && Mc(c, b);
            return function(a) {
                return !(d && 250 <= a.height())
            }
        },
        Mc = function(a, b) {
            var c = Math.min(R(b).clientHeight, 16 * R(b).clientWidth / 9),
                d = Jc(a, b);
            return (d ? d.y : 0) < c - 100
        },
        Pc = function(a, b) {
            var c = b,
                d = Infinity;
            do {
                var f = Oc(c, a, "height");
                f && (d = Math.min(d, f));
                (f = Oc(c, a, "maxHeight")) && (d = Math.min(d, f))
            } while ((c = c.parentElement) && "HTML" != c.tagName);
            return d
        },
        Oc = function(a, b, c) {
            if (a.style) {
                var d = Ka(a.style[c]);
                if (d) return d
            }
            if (a =
                Ca(a, b))
                if (d = Ka(a[c])) return d;
            return null
        };
    var Qc = function(a) {
            return function(b) {
                for (var c = a.length - 1; 0 <= c; --c)
                    if (!a[c](b)) return !1;
                return !0
            }
        },
        Rc = function(a, b) {
            for (var c = a.length, d = 0; d < c; ++d) {
                var f = a[d];
                if (b(f)) return f
            }
            return null
        };
    var Tc = function(a, b, c, d) {
            var f = Hc.slice(0);
            if (M(O(c), N.J.j))
                for (var e = Math.random, g = f.length - 1; 0 < g; g--) {
                    var h = Math.floor(e() * (g + 1)),
                        k = f[g];
                    f[g] = f[h];
                    f[h] = k
                }
            e = 488 > R(c).clientWidth;
            b = [Lc(a), Sc(e), Nc(e, c, d), Gc(b)];
            f = Rc(f, Qc(b));
            if (!f) throw new V("adsbygoogle.push() error: No slot size for availableWidth=" + a);
            return f
        },
        Sc = function(a) {
            return function(b) {
                return !(320 == b.minWidth() && (a && 50 == b.height() || !a && 100 == b.height()))
            }
        };
    var Y = function(a, b) {
        W.call(this, a, b)
    };
    fa(Y, W);
    Y.prototype.u = function(a) {
        return Math.min(1200, Math.round(a))
    };
    var Uc = [new Y(468, 350), new Y(414, 828), new Y(384, 768), new Y(375, 750), new Y(360, 720), new Y(320, 640), new Y(120, 600)],
        Vc = [new Y(468, 420), new Y(414, 828), new Y(384, 768), new Y(375, 750), new Y(360, 720), new Y(320, 640), new Y(120, 600)],
        Wc = function(a, b) {
            var c = Rc(a, Lc(b));
            if (!c) throw new V("adsbygoogle.push() error: No autorelaxed size for width=" + b + "px");
            return c
        };
    var Xc = [{
            C: [0],
            D: [6, 15, 0, 1, 3, 2, 7, 8, 10, 13, 9, 4, 5, 11, 12, 14]
        }, {
            C: [1],
            D: [6, 15, 0, 1, 2, 3, 7, 8, 4, 10, 9, 13, 5, 11, 12, 14]
        }, {
            C: [2],
            D: [0, 15, 1, 2, 3, 4, 7, 8, 13, 5, 6, 9, 10, 11, 12, 14]
        }],
        Yc = function(a, b) {
            var c = Pc(a, b);
            return function(a) {
                return a.height() <= c
            }
        };
    var Z = function(a, b) {
        W.call(this, a, b)
    };
    fa(Z, W);
    Z.prototype.u = function() {
        return this.minWidth()
    };
    Z.prototype.F = function(a) {
        return Z.ga.F.call(this, a) + "_0ads_al"
    };
    var Zc = [new Z(728, 15), new Z(468, 15), new Z(200, 90), new Z(180, 90), new Z(160, 90), new Z(120, 90)];
    var cd = function() {
            var a = window;
            if (void 0 === a.google_dre) {
                var b = "";
                a.postMessage && Bb(a) && ic() && (b = Ea(["20050000", "20050001"], ta)) && (a.google_ad_modifications = a.google_ad_modifications || {}, a.google_ad_modifications.eids = a.google_ad_modifications.eids || [], a.google_ad_modifications.eids.push(b));
                a.google_dre = b;
                "20050001" == a.google_dre && (La(a.top, "message", x("dr::mh", da($c, a), da(ad, a))), a.setTimeout(x("dr::to", da(bd, a, !0), da(ad, a)), 2E3), a.google_drc = 0, a.google_q = a.google_q || {}, a.google_q.tags = a.google_q.tags || [])
            }
        },
        dd = function(a) {
            "20050001" == m.google_dre && (a.params = a.params || {}, a.params.google_delay_requests_delay = 0, a.params.google_delay_requests_count = m.google_drc++, a.U = ea())
        },
        ed = function(a) {
            if ("20050001" == m.google_dre) {
                var b = ea() - a.U;
                a.params.google_delay_requests_delay = b
            }
        },
        $c = function(a, b) {
            var c;
            if (c = b && "afb" == b.data) c = b.origin, c = !!zb[c] || xa && Ab.test(c);
            c && bd(a, !1)
        },
        bd = function(a, b) {
            if (a.google_q && a.google_q.tags) {
                var c = a.google_q.tags;
                ad(a);
                c.length && (b && Hb("drt", {
                    Ja: c.length,
                    duration: 2E3
                }, 1), fd(c))
            }
        };
    var Db = function(a) {
        a.shv = wa()
    };
    w.G = !xa;
    var gd = function(a) {
            return yb.test(a.className) && "done" != a.getAttribute("data-adsbygoogle-status")
        },
        id = function(a, b) {
            var c = window;
            a.setAttribute("data-adsbygoogle-status", "done");
            hd(a, b, c)
        },
        hd = function(a, b, c) {
            jd(a, b, c);
            if (!kd(a, b, c)) {
                if (b.google_reactive_ads_config) {
                    if (ld) throw new V("adsbygoogle.push() error: Only one 'enable_page_level_ads' allowed per page.");
                    ld = !0
                } else b.google_ama || xb(c);
                md || (md = !0, Ec(c, b.google_ad_client));
                H(Xb, function(a, d) {
                    b[d] = b[d] || c[d]
                });
                b.google_loader_used = "aa";
                var d =
                    b.google_ad_output;
                if (d && "html" != d) throw new V("adsbygoogle.push() error: No support for google_ad_output=" + d);
                Fb("aa::load", Gb, function() {
                    Dc(c, b, a)
                })
            }
        },
        kd = function(a, b, c) {
            var d = b.google_reactive_ads_config;
            if (d) var f = d.page_level_pubvars,
                e = (aa(f) ? f : {}).google_tag_origin;
            if (b.google_ama) return !1;
            var g = b.google_ad_slot,
                f = c.google_ad_modifications;
            !f || bc(f.ad_whitelist, g, e || b.google_tag_origin) ? f = null : (e = f.space_collapsing || "none", f = (g = bc(f.ad_blacklist, g)) ? {
                K: !0,
                R: g.space_collapsing || e
            } : f.remove_ads_by_default ? {
                K: !0,
                R: e
            } : null);
            return f && f.K && "on" != b.google_adtest ? ("slot" == f.R && (null !== Ja(a.getAttribute("width")) && a.setAttribute("width", 0), null !== Ja(a.getAttribute("height")) && a.setAttribute("height", 0), a.style.width = "0px", a.style.height = "0px"), !0) : !(f = Ca(a, c)) || "none" != f.display || "on" == b.google_adtest || 0 < b.google_reactive_ad_format || d ? !1 : (c.document.createComment && a.appendChild(c.document.createComment("No ad requested because of display:none on the adsbygoogle tag")), !0)
        },
        jd = function(a, b, c) {
            for (var d = a.attributes,
                    f = d.length, e = 0; e < f; e++) {
                var g = d[e];
                if (/data-/.test(g.name)) {
                    var h = g.name.replace("data", "google").replace(/-/g, "_");
                    if (!b.hasOwnProperty(h)) {
                        var k;
                        k = h;
                        var g = g.value,
                            p = {
                                google_reactive_ad_format: ha,
                                google_allow_expandable_ads: ia
                            };
                        k = p.hasOwnProperty(k) ? p[k](g, null) : g;
                        null === k || (b[h] = k)
                    }
                }
            }
            if (b.google_enable_content_recommendations && 1 == b.google_reactive_ad_format) b.google_ad_width = R(c).clientWidth, b.google_ad_height = 50, a.style.display = "none";
            else if (1 == b.google_reactive_ad_format) b.google_ad_width = 320,
                b.google_ad_height = 50, a.style.display = "none";
            else if (8 == b.google_reactive_ad_format) d = ec(c) ? c.screen.width || 0 : R(c).clientWidth || 0, b.google_ad_width = d, c = ec(c) ? c.screen.height || 0 : R(c).clientHeight || 0, b.google_ad_height = c, a.style.display = "none";
            else if (9 == b.google_reactive_ad_format) b.google_ad_width = R(c).clientWidth || 0, b.google_ad_height = R(c).clientHeight || 0, a.style.display = "none";
            else {
                a: if (d = b.google_ad_format, "autorelaxed" == d) hc(b, c, Nb.l, Nb.j), f = M(ac(c), Nb.j) ? 3 : 2;
                    elseif ("auto" == d || /^((^|,) *(horizontal|vertical|rectangle) *)+$/.test(d)) {
                    if (ic() &&
                        (d = N.A, hc(b, c, d.l, d.j), M(O(c), N.A.j))) {
                        f = 5;
                        break a
                    }
                    f = 1
                } else f = "link" == d ? 4 : void 0;
                if (f) {
                    b.google_auto_format = b.google_ad_format;
                    d = a.offsetWidth;
                    a: switch (e = b.google_ad_format, f) {
                        default:
                            case 1:
                            f = "auto" == e ? .25 >= d / Math.min(1200, R(c).clientWidth) ? 4 : 3 : Kc(e);b && (b.google_responsive_formats = f);c = Tc(d, f, c, a);
                        break a;
                        case 2:
                                c = Wc(Uc, d);
                            break a;
                        case 3:
                                c = Wc(Vc, d);
                            break a;
                        case 5:
                                "auto" == e ? (f = d / Math.min(1200, R(c).clientWidth), f = .6 < f && !(488 > R(c).clientWidth) ? 2 : .25 >= f ? 4 : 3) : f = Kc(e);b.google_responsive_formats = f;
                            var l;
                            b: {
                                e = a;do
                                    if ((h = Ca(e, c)) && "fixed" == h.position) {
                                        e = !1;
                                        break b
                                    }
                                while (e = e.parentElement);
                                e = !0
                            }
                            if (e) {
                                b: {
                                    l = Jc(a, c);l ? (l = l.y, l = 285 > l ? 0 : 1396 > l ? 1 : 2) : l = 0;l = [l];
                                    for (e = 0; e < Xc.length; ++e) {
                                        h = Xc[e];
                                        c: if (k = h.C, l && "number" === typeof l.length && k && "number" === typeof k.length && l.length === k.length) {
                                            g = l.length;
                                            for (p = 0; p < g; p++)
                                                if (l[p] !== k[p]) {
                                                    k = !1;
                                                    break c
                                                }
                                            k = !0
                                        } else k = !1;
                                        if (k) {
                                            e = h.D;
                                            break b
                                        }
                                    }
                                    throw Error("No format for " + l);
                                }
                                l = [];
                                for (h = 0; h < e.length; ++h) l.push(Hc[e[h]]);e = 488 > R(c).clientWidth;e = [Yc(c, a), Lc(d), Nc(e, c, a), Gc(f)];
                                l = Rc(l, Qc(e))
                            }
                            c = l || Tc(d, f, c, a);
                            break a;
                        case 4:
                                if (c = Rc(Zc, Lc(d)), !c) throw new V("adsbygoogle.push() error: No link unit size for width=" + d + "px");
                    }
                    b.google_ad_width = c.u(d);
                    f = b.google_ad_height = c.height();
                    a.style.height = f + "px";
                    b.google_ad_resizable = !0;
                    b.google_ad_format = c.F(d);
                    b.google_override_format = 1;
                    b.google_loader_features_used = 128
                } else !Ia.test(b.google_ad_width) && !Ha.test(a.style.width) || !Ia.test(b.google_ad_height) && !Ha.test(a.style.height) ? (c = Ca(a, c), a.style.width = c.width, a.style.height = c.height,
                    Ic(c, b), b.google_ad_width || (b.google_ad_width = a.offsetWidth), b.google_ad_height || (b.google_ad_height = a.offsetHeight), b.google_loader_features_used = 256) : (Ic(a.style, b), b.google_ad_output && "html" != b.google_ad_output || 300 != b.google_ad_width || 250 != b.google_ad_height || (c = a.style.width, a.style.width = "100%", d = a.offsetWidth, a.style.width = c, b.google_available_width = d))
            }
        },
        nd = function(a) {
            for (var b = document.getElementsByTagName("ins"), c = 0, d = b[c]; c < b.length; d = b[++c]) {
                var f = d;
                if (gd(f) && "reserved" != f.getAttribute("data-adsbygoogle-status") &&
                    (!a || d.id == a)) return d
            }
            return null
        },
        ld = !1,
        md = !1,
        pd = function(a) {
            if (ic()) {
                var b;
                a: {
                    try {
                        b = m.JSON.parse(m.localStorage.getItem("google_ama_config") || "");
                        break a
                    } catch (h) {}
                    b = null
                }
                if (b) {
                    var c;
                    if (c = b.exp > ea()) a: {
                        c = b;b = Jb(m);c = c.mods;
                        if (!isNaN(b) && c)
                            for (var d = 0; d < c.length; d++) {
                                var f = c[d],
                                    e = f.max,
                                    g = f.eids;
                                if (b >= f.min && b <= e) {
                                    if (g)
                                        for (b = g, c = m.google_ad_modifications = m.google_ad_modifications || {}, c = c.loeids = c.loeids || [], d = 0; d < b.length; d++) c.push(b[d]);
                                    c = !0;
                                    break a
                                }
                            }
                        c = !1
                    }
                    if (c) b = od(), m.document.documentElement.appendChild(b),
                        id(b, {
                            google_ama: !0,
                            google_ad_client: a
                        });
                    else try {
                        m.localStorage.removeItem("google_ama_config")
                    } catch (h) {
                        a = {
                            lserr: 1
                        }, m.location.href && m.location.href.substring && (a.url = m.location.href.substring(0, 200)), Hb("ama", a, .01)
                    }
                }
            }
        },
        ad = function(a) {
            a.google_q.tags = void 0
        },
        fd = function(a) {
            if (a && a.shift) try {
                for (var b, c = 20; 0 < a.length && (b = a.shift()) && 0 < c;) qd(b), --c
            } catch (d) {
                throw window.setTimeout(rd, 0), d;
            }
        },
        od = function() {
            var a = document.createElement("ins");
            a.className = "adsbygoogle";
            a.style.display = "none";
            return a
        },
        sd = function(a, b) {
            var c = {};
            H(cc, function(b, d) {
                a.hasOwnProperty(d) && (c[d] = a[d])
            });
            aa(a.enable_page_level_ads) && (c.page_level_pubvars = a.enable_page_level_ads);
            var d = od();
            b ? F.body.appendChild(d) : F.documentElement.appendChild(d);
            id(d, {
                google_reactive_ads_config: c,
                google_ad_client: a.google_ad_client
            })
        },
        td = function(a) {
            if (!Bb(window)) throw new V("adsbygoogle.push() error: Page-level tag does not work inside iframes.");
            var b = M(O(G), N.I.j),
                c = !b;
            F.body || b ? sd(a, c) : La(F, "DOMContentLoaded", x("aa:reactiveTag", function() {
                sd(a,
                    c)
            }, void 0))
        },
        ud = function(a, b, c, d) {
            if (0 == b.message.indexOf("TagError")) {
                var f = {};
                Ta(w, f, d);
                f.context = a;
                f.msg = b.message.substring(0, 512);
                a = m.document;
                f.url = a.URL.substring(0, 512);
                f.ref = (a.referrer || "").substring(0, 512);
                Oa(Cb, "puberror", f, !0, c || .01);
                return !1
            }
            return w.s(a, b, c, d)
        },
        vd = function(a, b, c, d) {
            return 0 == b.message.indexOf("TagError") ? !1 : w.s(a, b, c, d)
        },
        qd = function(a) {
            var b = {};
            Fb("aa::hqe", ud, function() {
                wd(a, b)
            }, function(c) {
                c.client = c.client || b.google_ad_client || a.google_ad_client;
                c.slotname = c.slotname ||
                    b.google_ad_slot;
                c.tag_origin = c.tag_origin || b.google_tag_origin
            })
        },
        wd = function(a, b) {
            ga = (new Date).getTime();
            if (m.google_q && m.google_q.tags) dd(a), m.google_q.tags.push(a);
            else {
                var c;
                a: {
                    if (a.enable_page_level_ads) {
                        if ("string" == typeof a.google_ad_client) {
                            c = !0;
                            break a
                        }
                        throw new V("adsbygoogle.push() error: 'google_ad_client' is missing from the tag config.");
                    }
                    c = !1
                }
                if (c) pd(a.google_ad_client), td(a);
                else {
                    m.google_q ? ed(a) : (cd(), dd(a));
                    c = a.element;
                    var d = a.params;
                    d && H(d, function(a, c) {
                        b[c] = a
                    });
                    if (c) {
                        if (!gd(c) &&
                            (c = c.id ? nd(c.id) : null, !c)) throw new V("adsbygoogle.push() error: 'element' has already been filled.");
                        if (!("innerHTML" in c)) throw new V("adsbygoogle.push() error: 'element' is not a good DOM element.");
                    } else if (c = nd(), !c) throw new V("adsbygoogle.push() error: All ins elements in the DOM with class=adsbygoogle already have ads in them.");
                    id(c, b)
                }
            }
        },
        rd = function() {
            Eb();
            Fb("aa::main", vd, xd)
        },
        xd = function() {
            var a = G.google_ad_modifications = G.google_ad_modifications || {};
            if (!a.plle) {
                a.plle = !0;
                var b = a.eids =
                    a.eids || [],
                    a = a.loeids = a.loeids || [],
                    c, d;
                c = N.A;
                d = c.j;
                if (G.location && G.location.hash == "#google_plle_" + d) c = d;
                else {
                    c = [c.l, d];
                    d = new Ib(la, la + ma - 1);
                    var f;
                    (f = 0 >= ma || ma % c.length) || (f = fc.ba, f = !(f.start <= d.start && f.end >= d.end));
                    f ? c = null : (f = Jb(G), c = null !== f && d.start <= f && d.end >= f ? c[(f - la) % c.length] : null)
                }
                c && a.push(c);
                c = Nb;
                (c = gc(na, c.l, c.j)) && b.push(c);
                c = N.J;
                (d = gc(oa, c.l, c.j)) && a.push(d);
                (c = d == c.l ? Mb.l : d == c.j ? Mb.j : "") && b.push(c);
                c = N.H;
                (b = gc(ua, c.l, c.j)) && a.push(b);
                F.body || (c = N.I, (b = gc(va, c.l, c.j)) && a.push(b))
            }
            a =
                window.adsbygoogle;
            fd(a);
            if (!a || !a.loaded) {
                window.adsbygoogle = {
                    push: qd,
                    loaded: !0
                };
                a && yd(a.onload);
                try {
                    Object.defineProperty(window.adsbygoogle, "onload", {
                        set: yd
                    })
                } catch (e) {}
            }
        },
        yd = function(a) {
            I(a) && window.setTimeout(a, 0)
        };
    rd();
}).call(this);
