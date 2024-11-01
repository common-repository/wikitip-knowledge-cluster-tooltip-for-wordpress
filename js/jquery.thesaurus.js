/*
* Thesaurus
*
* @package thesaurus
* @author $Author: sheiko $, modified by Richard Vencu
* @version $Id: jquery.thesaurus.js, v 3.1 $ / last modified February 06, 2014
* @license GNU
* @copyright (c) Dmitry Sheiko http://www.cmsdevelopment.com
*/

(function ($) {

    var VERSION = "3.0",
        TPL_TAG_OPEN = '~~',
        TPL_TAG_CLOSE = '~~',
        ESCAPERS = '[\\s!;?\…\.,%\"\'\\(\\)\\{\\}]',
        UNAPPROPRIATE_TAGS = ['SCRIPT', 'BASE', 'LINK', 'META', 'STYLE', 'TITLE', 'APPLET', 'OBJECT', 'H1', 'H2', 'H3', 'H4', 'H5'];

    var Collection = function () {
    }
    /**
     * Collection of tooltip widgets
     */
    Collection.prototype = {
        _data: [],
        length: function () {
            return this._data.length;
        },
        append: function (id, instance) {
            this._data[id] = instance;
        },
        findById: function (id) {
            return this._data[id] === undefined ? null : this._data[id];
        },
        remove: function (id) {
            if (this._data[id] !== undefined) {
                delete this._data[id];
            }
        }
    };
    /**
     * Tooltip component
     */
    var Tooltip = function (options) {
        $.extend(this.options, options);
        this.init();
        this.renderUI();
        this.bindUI();
    };

    Tooltip.collection = new Collection();
    Tooltip.ids = []; // Helps with unique ids

    /**
     * Used when Mouse leaves the term
     * @param event e
     */
    Tooltip.hide = function (e) {
        var instance = Tooltip.collection.findById(e.currentTarget.id);
        if (null !== instance) {
            instance.delayedDestruction();
        }
    };
    /**
     * Tooltip self-destruction
     * @param string id
     */
    Tooltip.remove = function (id) {
        Tooltip.collection.remove(id);
    };
    /**
     * Makes Id for the tooltip
     * @param HTMLNode node
     */
    Tooltip.normalize = function (node) {
        if (!node.id) {
            Tooltip.ids.push(node.id);
            node.id = 'dfn' + (Tooltip.ids.length);
        }
    };
    /**
     * Modifies contet of the requested tooltip
     * @param event e
     * @param string text
     */
    Tooltip.text = function (e, text) {
        if (undefined !== e.currentTarget.id) {
            var instance = Tooltip.collection.findById(e.currentTarget.id);
            if (null !== instance) {
                instance.text(text);
            }
        }
    };
    /**
     * Shows requested tooltip from the collection
     * @param event e
     * @returns Tooltip instance
     */
    Tooltip.show = function (e) {
        Tooltip.normalize(e.currentTarget);
        var instance = Tooltip.collection.findById(e.currentTarget.id);
        if (null === instance) {
            instance = new Tooltip({
                event: e,
                delay: Thesaurus.options.delay,
                effect: Thesaurus.options.effect
            });
            Tooltip.collection.append(e.currentTarget.id, instance);
        } else {
            // The same term is hovered before it's tooltip self-destruction
            instance.cancelDelayedDestruction();
        }
        return instance;
    };

    Tooltip.prototype = {
        options: {},
        boundingBox: null,
        contentBox: null,
        currentTarget: null,
        _parentDelayed: false,
        timer: null,
        init: function () {
            this.currentTarget = this.options.event.currentTarget;
        },
        /**
         * Renders tooltip
         */
        renderUI: function () {
            $('body').append('<div id="thesaurus-'
                + this.currentTarget.id + '" class="thesaurus hidden"><!-- --></div>');
            this.boundingBox = $('#thesaurus-' + this.currentTarget.id);
            this.adjust();
            this.boundingBox.append(TOOLTIP_BODY_TPL);
            this.boundingBox.find('a.term').html($(this.currentTarget).text());
            this.contentBox = this.boundingBox.find('div.thesaurus-body');
            this.contentBox.html(TOOLTIP_LOADING_TPL);
            if ($.fn.bgiframe) {
                this.boundingBox.bgiframe();
            }
            this.boundingBox.css('z-index', zetind);
            if (this.options.effect) {
                this.applyEffect(this.options.effect)
            } else {
                this.boundingBox.removeClass("hidden");
            }
        },
        /**
         * Applies effect when the tooltip is appearing
         * @param string effect
         */
        applyEffect: function (effect) {
            switch (effect) {
                case "fade":
                    this.boundingBox.fadeIn('slow');
                    break;
                case "slide":
                    this.boundingBox.slideDown('slow');
                    break;
            }
        },
        bindUI: function () {
            this.boundingBox.unbind('mouseover')
                .bind('mouseover', $.proxy(this.cancelDelayedDestruction, this));
            this.boundingBox.unbind('mouseleave')
                .bind('mouseleave', $.proxy(this.delayedDestruction, this));
        },

        /**
         * Implemets cascade tooltips
         * @param string action - either cancelDelayedDestruction or delayedDestruction
         */
        applyOnParent: function (action) {
            var parentId = $(this.currentTarget).attr('rel');
            if (parentId) {
                var instance = Tooltip.collection.findById(parentId);
                if (null !== instance) {
                    switch (action) {
                        case 'cancelDelayedDestruction':
                            instance._parentDelayed = true;
                            instance.cancelDelayedDestruction();
                            break;
                        case 'delayedDestruction':
                            if (instance._parentDelayed) {
                                instance.delayedDestruction();
                                instance._parentDelayed = false;
                            }
                            break;
                    }
                }
            }
        },

        /**
         * Changes content of tooltip
         * @param string text
         */
        text: function (text) {
            this.contentBox.html(text);
        },
        /**
         * Cancels request to destory the tooltip
         * @see delayedDestruction
         */
        cancelDelayedDestruction: function () {
            this.applyOnParent('cancelDelayedDestruction');
            if (this.timer) {
                window.clearTimeout(this.timer);
                this.timer = null;
            }
        },
        /**
         * Mouse left term and tooltip area, so destuction of tooltip requested
         * Though if the mouse pointer returns to the areas before delay expired,
         * the request will be canceled
         */
        delayedDestruction: function () {
            this.applyOnParent('delayedDestruction');
            this.timer = window.setTimeout($.proxy(this.destroy, this), this.options.delay);
        },
        /**
         * Removes Tooltip HTML container and instance ofthe class from the collection
         */
        destroy: function () {
            this.boundingBox.remove();
            Tooltip.remove(this.currentTarget.id);
        },
        /**
         * Adjust tooltip position. It tries to show always within portview
         */
        adjust: function () {
            var e = this.options.event, left, top;

            var rCornerW = $(window).width() - e.clientX;
            var bCornerH = $(window).height() - e.clientY;

            // Compliance with HTML 4/XHTML
            if (document.documentElement && document.documentElement.scrollTop)
                scrollTop = document.documentElement.scrollTop;
            else
                scrollTop = document.body.scrollTop;

            // Compliance with HTML 4/XHTML
            if (document.documentElement && document.documentElement.scrollLeft)
                scrollLeft = document.documentElement.scrollLeft;
            else
                scrollLeft = document.body.scrollLeft;


            if (rCornerW < this.boundingBox.offsetWidth)
                left = scrollLeft + e.clientX - this.boundingBox.offsetWidth;
            else
                left = scrollLeft + e.clientX;

            if (bCornerH < this.boundingBox.offsetHeight)
                top = scrollTop + e.clientY - this.boundingBox.offsetHeight;
            else
                top = scrollTop + e.clientY;

            this.boundingBox.css("top", (top) + "px");
            this.boundingBox.css("left", (left) + "px");
        }
    };

    /**
     * Configurable singleton
     */
    var Thesaurus = function (options) {
        if (null === Thesaurus.instance) {
            $.extend(this.options, options);
            Thesaurus.instance = this;
            this.init();
        } else {
            $.extend(Thesaurus.instance.options, options);
            return Thesaurus.instance;
        }
    };

    Thesaurus.instance = null;

    Thesaurus.prototype = {
        terms: [],
        options: {}, // Configuration
        cache: {}, // Caches requestd term definitions
        init: function () {
            this.cssLoad(this.options.css);
            if (!this.options.containers.length) {
                this.options.containers = ['body'];
            }
            this.bootstrap();
        },
        /**
         * Binds event handlers to found terms
         * @param HTMLNode node
         */
        bindUI: function (node) {
            $(node).find('dfn.thesaurus').each($.proxy(function (i, node) {
                $(node).bind('mouseenter', $.proxy(this._onMouseOver, this));
                $(node).bind('mouseleave', $.proxy(this._onMouseOut, this));
            }, this));
        },
        /**
         * Used when tooltip over tolltip
         * @param HTMLNode tooltipNode
         * @param HTMLNode parentTooltipNode
         */
        _processOverlayTooltip: function (tooltipNode, parentTooltipNode) {
            var term = $(parentTooltipNode).text();
            if (tooltipNode) {
                tooltipNode.off('click').on('click', this, function (e) {
                });
                this._searchTermsInDOM(tooltipNode);
                this._innerMarkup(tooltipNode, $(parentTooltipNode).attr('id'));
                this.bindUI(tooltipNode);
                //paginate tooltip
                $(tooltipNode).find('.defholder').sweetPages({perPage: this.options.show_count});
                var controls = $(tooltipNode).find('.swControls').detach();
                $(tooltipNode).find('span.controls').append(controls);
            }
        },
        /**
         * on MouseOver event handler
         * @param event e
         */
        _onMouseOver: function (e) {
            var instance = Tooltip.show(e);
            var term = $(e.currentTarget).text();
            if (undefined !== this.cache[term]) {
                Tooltip.text(e, this.cache[term]);
                this._processOverlayTooltip(instance.contentBox, e.currentTarget);
            } else {
                $.getJSON(this.options.JSON_DEF_URI + term + "&filter=" + filter.join() + "&callback=?", $.proxy(function (data) {
                    this.cache[term] = this._processResponseDef(data);
                    Tooltip.text(e, this.cache[term]);
                    this._processOverlayTooltip(instance.contentBox, e.currentTarget);
                }, this));
            }
        },
        /**
         * on MouseOut event handler
         * @param event e
         */
        _onMouseOut: function (e) {
            Tooltip.hide(e);
        },
        /**
         * Load given CSS file
         * @param string file
         */
        cssLoad: function (file) {
            //$('body').append('<style>' + DEFAULTCSS_TPL + '</style>');
        },
        /**
         * Indicates message when an error occured retrieving data from server side
         * @param object data
         */
        _processResponseTerm: function (data) {
            var errorMsg = null;
            if (undefined === data || undefined === data.status) {
                errorMsg = this.options.MESSAGE1;
            } else if ('ok' != data.status) {
                errorMsg = data.errorMsg;
            }
            if (null !== errorMsg) {
                return null;
            }
            return data.tags;
        },
        /**
         * Indicates message when an error occured retrieving data from seerver side
         * @param object data
         */
        _processResponseDef: function (data) {
            var errorMsg = null;
            if (undefined === data || undefined === data.status) {
                errorMsg = this.options.MESSAGE1;
            } else if ('ok' != data.status) {
                errorMsg = data.errorMsg;
            }
            if (null !== errorMsg) {
                return null;
            }
            var payload;
            if (data.count == 1) {
                payload = '<h4>' + data.posts[0].title + '</h4><p>' + data.posts[0].excerpt + '</p>';
                payload += '<div class="legend">' + this.options.MESSAGE2 + ' <h6><a href="' + data.posts[0].blog_url + '" target="_blank">' + data.posts[0].blog_name + '</a></h6> ' + this.options.MESSAGE3 + ' <h6>';
                for (q = 0; q < data.posts[0].categories_count; q++) {
                    if (q > 0) {
                        payload += ', ';
                    }
                    payload += '<a href="' + data.posts[0].blog_url + '/category/' + data.posts[0].categories[q].slug + '" target="_blank">' + data.posts[0].categories[q].title + '</a>';
                }
                payload += '</h6></div>';
                if (data.posts[0].comment_count == 1)
                    payload += '<div class="thesaurus-footer">' + data.posts[0].comment_count + ' ' + this.options.MESSAGE6;
                if (data.posts[0].comment_count > 1)
                    payload += '<div class="thesaurus-footer">' + data.posts[0].comment_count + ' ' + this.options.MESSAGE7;
                if (data.posts[0].comment_count == 0)
                    payload += '<div class="thesaurus-footer">' + this.options.MESSAGE4;
                if (data.posts[0].comment_status == "open")
                    payload += '<a href="' + data.posts[0].url + '#comments" target="_blank">' + this.options.MESSAGE5 + '</a></div>';
                else {
                    payload += '</div>';
                }
            } else if (data.count > 1) {
                var q;
                payload = '<h4>' + data.count + ' ' + this.options.MESSAGE10 + '</h4> <span class="controls"></span><br /><br />';

                payload += '<ol class="defholder">';
                for (q = 0; q < data.count; q++) {
                    payload += '<li><h5>' + data.posts[q].title + '</h5><p>' + data.posts[q].excerpt + '</p>';
                    payload += '<div class="legend">' + this.options.MESSAGE2 + ' <h6><a href="' + data.posts[q].blog_url + '" target="_blank">' + data.posts[q].blog_name + '</a></h6> ' + this.options.MESSAGE3 + ' <h6>';
                    for (r = 0; r < data.posts[q].categories_count; r++) {
                        if (r > 0) {
                            payload += ', ';
                        }
                        payload += '<a href="' + data.posts[q].blog_url + '/category/' + data.posts[q].categories[r].slug + '" target="_blank">' + data.posts[q].categories[r].title + '</a>';
                        ;
                    }
                    payload += '</h6></div>';
                    if (data.posts[q].comment_count == 1)
                        payload += '<div class="thesaurus-footer">' + data.posts[q].comment_count + ' ' + this.options.MESSAGE6;
                    if (data.posts[q].comment_count > 1)
                        payload += '<div class="thesaurus-footer">' + data.posts[q].comment_count + ' ' + this.options.MESSAGE7;
                    if (data.posts[q].comment_count == 0)
                        payload += '<div class="thesaurus-footer">' + this.options.MESSAGE4;

                    if (data.posts[q].comment_status == "open")
                        payload += '<a href="' + data.posts[q].url + '#comments" target="_blank">' + this.options.MESSAGE5 + '</a></div>';
                    else
                        payload += '</div></li>';
                }
                payload += '</ol>';

            } else {
                payload = '<h4>' + this.options.MESSAGE8 + '</h4><p>' + this.options.MESSAGE9 + '</p>';
            }
            return payload;
        },
        /**
         * 1) Loads list of terms from server
         * 2) Searches terms in DOM
         * 3) Marks up found terms
         * 4) Binds eventhandlers to them
         */
        bootstrap: function () {
            var words = $(Thesaurus.options.containers.join(",")).text().replace(/[\d!;:<>.=\-_`~@*?,%"'\\(\\)\\{\\}]/g, ' ').replace(/\s+/g, ' ');

            $.post(this.options.JSON_LOCAL_POST_URI + "?url=" + this.options.JSON_REMOTE_POST_URI + '&mode=native&callback=?', {data: words}, $.proxy(function (data) {
                this.terms = this._processResponseTerm(data);
                if (this.terms != null) {
                    $.each(this.options.containers, $.proxy(function (i, node) {
                        this._searchTermsInDOM(node);
                        this._markup(node);
                    }, this));
                    this.bindUI('body');
                }
            }, this), 'json');
        },
        /**
         * Now parse already marked terms wrapping them with DFN tag
         * @param HTMLNode node
         * @see _markTerm
         */
        _markup: function (node) {
            var re = new RegExp(TPL_TAG_OPEN + "(.*?)" + TPL_TAG_CLOSE, 'g');
            /**$(node).html($(node).html().replace(re, '<dfn class=\"thesaurus\">$1</dfn>'));*/
            $.each($(node), $.proxy(function (inx, el) {
                $(el).html($(el).html().replace(re, '<dfn class=\"thesaurus\">$1</dfn>'));
            }, this));
        },
        /**
         * Parse especiall for tooltip over tooltip, to point parent tooltip id
         * @param HTMLNode node
         * @param string parentId
         * @see _processOverlayTooltip
         */
        _innerMarkup: function (node, parentId) {
            var re = new RegExp(TPL_TAG_OPEN + "(.*?)" + TPL_TAG_CLOSE, 'g');
            $(node).html($(node).html().replace(re, '<dfn rel=\"' + parentId
                + '\" class=\"thesaurus\">$1</dfn>'));
        },
        /**
         * Since I can't apply any HTML working with textNodes, just mark them to be able then
         * parse them in document HTML
         * @param string term
         * @param string line
         * @see _markup
         */
        _markTerm: function (term, line) {
            var modifier = this.options.caseSensitive == "on" ? "g" : "gi";
            var re = '';
            // Only term in nodeValue
            re = new RegExp("^(" + term + ")$", modifier);
            line = line.replace(re, TPL_TAG_OPEN + "$1" + TPL_TAG_CLOSE);
            //term" ....
            re = new RegExp("^(" + term + ")(" + ESCAPERS + ")", modifier);
            line = line.replace(re, TPL_TAG_OPEN + "$1" + TPL_TAG_CLOSE + "$2");
            //... "term
            re = new RegExp("(" + ESCAPERS + ")(" + term + ")$", modifier);
            line = line.replace(re, "$1" + TPL_TAG_OPEN + "$2" + TPL_TAG_CLOSE);
            // .. "term" ..
            re = new RegExp("(" + ESCAPERS + ")(" + term + ")(" + ESCAPERS + ")", modifier);
            line = line.replace(re, "$1" + TPL_TAG_OPEN + "$2" + TPL_TAG_CLOSE + "$3");
            return line;
        },
        /**
         * Check the node value against terms list
         * @param HTMLNode node
         */
        _checkNodeValue: function (node) {
            $.each(this.terms, $.proxy(function (inx, term) {
                if (term.length <= node.nodeValue.length) {
                    node.nodeValue = this._markTerm(term.toString(), node.nodeValue.toString());
                }
            }, this));
        },
        /**
         * Traverses configured nodes for all their children textNodes
         * @param HTMLNode node
         */
        _searchTermsInDOM: function (node) {
            $.each($(node).get(), $.proxy(function (inx, el) {
                $.each(el.childNodes, $.proxy(function (i, child) {
                    if (child.childNodes.length && -1 == $.inArray(child.tagName, UNAPPROPRIATE_TAGS)) {
                        this._searchTermsInDOM(child);
                    }
                    // Is it a non-empty text node?
                    if (undefined === child.tagName && child.nodeValue.length) {
                        this._checkNodeValue(child);
                    }
                }, this));
            }, this));


        }
    };

    /**
     * Default configuration
     */
    Thesaurus.options = {
        caseSentitive: false, // Used when matching found terms againstloaded ones
        delay: 250, // Delay before tooltip self-destruction
        containers: [], // Put here list of selectors for the DOM element you want to analyze for terms
        effect: null, // Can be also fade or slide
        //controller: 'controller.csv.php' // Path to the controller,
        JSON_TERMS_URI: 'https://wikitip.info/api/controller/get_network_thesaurus/?callback=?',
        JSON_DEF_URI: 'https://wikitip.info/api/controller/get_network_posts_by_tag/?tag=',
        JSON_STATS_URI: 'https://wikitip.info/api/controller/update_stats_tag/?tag=',
        JSON_REMOTE_POST_URI: 'https://wikitip.info/api/controller/post_data/?nonce=',
        JSON_LOCAL_POST_URI: '/ba-simple-proxy.php/',
        zetind: 'auto',
        MESSAGE1: 'Corrupted response format. Contact the webmaster.',
        MESSAGE2: 'Filed in',
        MESSAGE3: 'under',
        MESSAGE4: 'No comments yet',
        MESSAGE5: 'Add comment',
        MESSAGE6: 'comment',
        MESSAGE7: 'comments',
        MESSAGE8: 'Error',
        MESSAGE9: 'There is no definition for this term. Please contact the website admin.',
        MESSAGE10: 'wikis found',
        MESSAGE11: 'A WikiTip Thesaurus',
        MESSAGE12: 'displayed',
        show_count: -1
    };
// Alternative way to specify nodes you want analyze for terms occurances
// <code>
//  $('div.some').applyThesaurus();
// </code>
    $.fn.applyThesaurus = function () {
        Thesaurus.options.containers.push(this);
    }
// Thesaurus configurator
    $.Thesaurus = function (options) {
        $.extend(Thesaurus.options, options);
    };
// Authomaticaly applied when DOM is ready
    $(document).ready(function () {
        if ($(Thesaurus.options.containers).length > 0 && ACTIVE) {
            thes = new Thesaurus(Thesaurus.options);
        }
    });

    function removeDuplicates(inputArray) {
        var i;
        var len = inputArray.length;
        var outputArray = [];
        var temp = {};

        for (i = 0; i < len; i++) {
            temp[inputArray[i]] = 0;
        }
        for (i in temp) {
            outputArray.push(i);
        }
        return outputArray;
    }

})(jQuery);