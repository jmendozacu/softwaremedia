var Injector = (function () {
    'use strict';

    var jQuerySrc = '//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js',
        minjQueryVersion = '1.9.0',
        jqSave,
        allowInstalledVersion = false,
        arrayMap = function(a,f) {

            if (Array.prototype.hasOwnProperty('map')) {
                return a.map(f);
            } else {
                var a2 = [];
                for (var i = 0, e = a.length; i<e; ++i) {
                    if (i in this) {
                        a2[i]= f(a[i],i,a);
                    }
                }
                return a2;
            }
        },

        arrayContains = function (a,v) {
            for(var i=0,n=a.length;i<n;++i) {
                if(v === a[i]) {
                    return true;
                }
            }
            return false;
        },
        arrayIndexOf = function (a,v) {
            for(var i=0,n=a.length;i<n;++i) {
                if(v === a[i]) {
                    return i;
                }
            }
            return -1;
        },

        now = function() { return new Date().getTime(); },
        debounce = function(func, wait) {
          var timeout, args, context, timestamp, result;

          var later = function() {
            var last = now() - timestamp;
            if (last < wait) {
              timeout = setTimeout(later, wait - last);
            } else {
              timeout = null;
              result = func.apply(context, args);
              context = args = null;
            }
          };

          return function() {
            context = this;
            args = arguments;
            timestamp = now();
            if (!timeout) {
              timeout = setTimeout(later, wait);
            }
            if (!timeout) {
              result = func.apply(context, args);
              context = args = null;
            }

            return result;
          };
        },

        continueInitialization = function () {
            var jQuery = jqSave,
                jQuery = jqSave;


            // create Object.create for old browsers
            if (typeof Object.create !== 'function') {
                Object.create = function (o) {
                    var F = function () {};
                    F.prototype = o;
                    return new F();
                };
            }

            // JS Include: Raty
/*!
 * jQuery Raty - A Star Rating Plugin
 *
 * The MIT License
 *
 * @author  : Washington Botelho
 * @doc     : http://wbotelhos.com/raty
 * @version : 2.7.0
 *
 */

;
(function(jQuery) {
  'use strict';

  var methods = {
    init: function(options) {
      return this.each(function() {
        this.self = jQuery(this);

        methods.destroy.call(this.self);

        this.opt = jQuery.extend(true, {}, jQuery.fn.raty.defaults, options);

        methods._adjustCallback.call(this);
        methods._adjustNumber.call(this);
        methods._adjustHints.call(this);

        this.opt.score = methods._adjustedScore.call(this, this.opt.score);

        if (this.opt.starType !== 'img') {
          methods._adjustStarType.call(this);
        }

        methods._adjustPath.call(this);
        methods._createStars.call(this);

        if (this.opt.cancel) {
          methods._createCancel.call(this);
        }

        if (this.opt.precision) {
          methods._adjustPrecision.call(this);
        }

        methods._createScore.call(this);
        methods._apply.call(this, this.opt.score);
        methods._setTitle.call(this, this.opt.score);
        methods._target.call(this, this.opt.score);

        if (this.opt.readOnly) {
          methods._lock.call(this);
        } else {
          this.style.cursor = 'pointer';

          methods._binds.call(this);
        }
      });
    },

    _adjustCallback: function() {
      var options = ['number', 'readOnly', 'score', 'scoreName', 'target'];

      for (var i = 0; i < options.length; i++) {
        if (typeof this.opt[options[i]] === 'function') {
          this.opt[options[i]] = this.opt[options[i]].call(this);
        }
      }
    },

    _adjustedScore: function(score) {
      if (!score) {
        return score;
      }

      return methods._between(score, 0, this.opt.number);
    },

    _adjustHints: function() {
      if (!this.opt.hints) {
        this.opt.hints = [];
      }

      if (!this.opt.halfShow && !this.opt.half) {
        return;
      }

      var steps = this.opt.precision ? 10 : 2;

      for (var i = 0; i < this.opt.number; i++) {
        var group = this.opt.hints[i];

        if (Object.prototype.toString.call(group) !== '[object Array]') {
          group = [group];
        }

        this.opt.hints[i] = [];

        for (var j = 0; j < steps; j++) {
          var
            hint = group[j],
            last = group[group.length - 1];

          if (last === undefined) {
            last = null;
          }

          this.opt.hints[i][j] = hint === undefined ? last : hint;
        }
      }
    },

    _adjustNumber: function() {
      this.opt.number = methods._between(this.opt.number, 1, this.opt.numberMax);
    },

    _adjustPath: function() {
      this.opt.path = this.opt.path || '';

      if (this.opt.path && this.opt.path.charAt(this.opt.path.length - 1) !== '/') {
        this.opt.path += '/';
      }
    },

    _adjustPrecision: function() {
      this.opt.half = true;
    },

    _adjustStarType: function() {
      var replaces = ['cancelOff', 'cancelOn', 'starHalf', 'starOff', 'starOn'];

      this.opt.path = '';

      for (var i = 0; i < replaces.length; i++) {
        this.opt[replaces[i]] = this.opt[replaces[i]].replace('.', '-');
      }
    },

    _apply: function(score) {
      methods._fill.call(this, score);

      if (score) {
        if (score > 0) {
          this.score.val(score);
        }

        methods._roundStars.call(this, score);
      }
    },

    _between: function(value, min, max) {
      return Math.min(Math.max(parseFloat(value), min), max);
    },

    _binds: function() {
      if (this.cancel) {
        methods._bindOverCancel.call(this);
        methods._bindClickCancel.call(this);
        methods._bindOutCancel.call(this);
      }

      methods._bindOver.call(this);
      methods._bindClick.call(this);
      methods._bindOut.call(this);
    },

    _bindClick: function() {
      var that = this;

      that.stars.on('click.raty', function(evt) {
        var
          execute = true,
          score   = (that.opt.half || that.opt.precision) ? that.self.data('score') : (this.alt || jQuery(this).data('alt'));

        if (that.opt.click) {
          execute = that.opt.click.call(that, +score, evt);
        }

        if (execute || execute === undefined) {
          if (that.opt.half && !that.opt.precision) {
            score = methods._roundHalfScore.call(that, score);
          }

          methods._apply.call(that, score);
        }
      });
    },

    _bindClickCancel: function() {
      var that = this;

      that.cancel.on('click.raty', function(evt) {
        that.score.removeAttr('value');

        if (that.opt.click) {
          that.opt.click.call(that, null, evt);
        }
      });
    },

    _bindOut: function() {
      var that = this;

      that.self.on('mouseleave.raty', function(evt) {
        var score = +that.score.val() || undefined;

        methods._apply.call(that, score);
        methods._target.call(that, score, evt);
        methods._resetTitle.call(that);

        if (that.opt.mouseout) {
          that.opt.mouseout.call(that, score, evt);
        }
      });
    },

    _bindOutCancel: function() {
      var that = this;

      that.cancel.on('mouseleave.raty', function(evt) {
        var icon = that.opt.cancelOff;

        if (that.opt.starType !== 'img') {
          icon = that.opt.cancelClass + ' ' + icon;
        }

        methods._setIcon.call(that, this, icon);

        if (that.opt.mouseout) {
          var score = +that.score.val() || undefined;

          that.opt.mouseout.call(that, score, evt);
        }
      });
    },

    _bindOver: function() {
      var that   = this,
          action = that.opt.half ? 'mousemove.raty' : 'mouseover.raty';

      that.stars.on(action, function(evt) {
        var score = methods._getScoreByPosition.call(that, evt, this);

        methods._fill.call(that, score);

        if (that.opt.half) {
          methods._roundStars.call(that, score, evt);
          methods._setTitle.call(that, score, evt);

          that.self.data('score', score);
        }

        methods._target.call(that, score, evt);

        if (that.opt.mouseover) {
          that.opt.mouseover.call(that, score, evt);
        }
      });
    },

    _bindOverCancel: function() {
      var that = this;

      that.cancel.on('mouseover.raty', function(evt) {
        var
          starOff = that.opt.path + that.opt.starOff,
          icon    = that.opt.cancelOn;

        if (that.opt.starType === 'img') {
          that.stars.attr('src', starOff);
        } else {
          icon = that.opt.cancelClass + ' ' + icon;

          that.stars.attr('class', starOff);
        }

        methods._setIcon.call(that, this, icon);
        methods._target.call(that, null, evt);

        if (that.opt.mouseover) {
          that.opt.mouseover.call(that, null);
        }
      });
    },

    _buildScoreField: function() {
      return jQuery('<input />', { name: this.opt.scoreName, type: 'hidden' }).appendTo(this);
    },

    _createCancel: function() {
      var icon   = this.opt.path + this.opt.cancelOff,
          cancel = jQuery('<' + this.opt.starType + ' />', { title: this.opt.cancelHint, 'class': this.opt.cancelClass });

      if (this.opt.starType === 'img') {
        cancel.attr({ src: icon, alt: 'x' });
      } else {
        // TODO: use jQuery.data
        cancel.attr('data-alt', 'x').addClass(icon);
      }

      if (this.opt.cancelPlace === 'left') {
        this.self.prepend('&#160;').prepend(cancel);
      } else {
        this.self.append('&#160;').append(cancel);
      }

      this.cancel = cancel;
    },

    _createScore: function() {
      var score = jQuery(this.opt.targetScore);

      this.score = score.length ? score : methods._buildScoreField.call(this);
    },

    _createStars: function() {
      for (var i = 1; i <= this.opt.number; i++) {
        var
          name  = methods._nameForIndex.call(this, i),
          attrs = { alt: i, src: this.opt.path + this.opt[name] };

        if (this.opt.starType !== 'img') {
          attrs = { 'data-alt': i, 'class': attrs.src }; // TODO: use jQuery.data.
        }

        attrs.title = methods._getHint.call(this, i);

        jQuery('<' + this.opt.starType + ' />', attrs).appendTo(this);

        if (this.opt.space) {
          this.self.append(i < this.opt.number ? '&#160;' : '');
        }
      }

      this.stars = this.self.children(this.opt.starType);
    },

    _error: function(message) {
      jQuery(this).text(message);

      jQuery.error(message);
    },

    _fill: function(score) {
      var hash = 0;

      for (var i = 1; i <= this.stars.length; i++) {
        var
          icon,
          star   = this.stars[i - 1],
          turnOn = methods._turnOn.call(this, i, score);

        if (this.opt.iconRange && this.opt.iconRange.length > hash) {
          var irange = this.opt.iconRange[hash];

          icon = methods._getRangeIcon.call(this, irange, turnOn);

          if (i <= irange.range) {
            methods._setIcon.call(this, star, icon);
          }

          if (i === irange.range) {
            hash++;
          }
        } else {
          icon = this.opt[turnOn ? 'starOn' : 'starOff'];

          methods._setIcon.call(this, star, icon);
        }
      }
    },

    _getFirstDecimal: function(number) {
      var
        decimal = number.toString().split('.')[1],
        result  = 0;

      if (decimal) {
        result = parseInt(decimal.charAt(0), 10);

        if (decimal.slice(1, 5) === '9999') {
          result++;
        }
      }

      return result;
    },

    _getRangeIcon: function(irange, turnOn) {
      return turnOn ? irange.on || this.opt.starOn : irange.off || this.opt.starOff;
    },

    _getScoreByPosition: function(evt, icon) {
      var score = parseInt(icon.alt || icon.getAttribute('data-alt'), 10);

      if (this.opt.half) {
        var
          size    = methods._getWidth.call(this),
          percent = parseFloat((evt.pageX - jQuery(icon).offset().left) / size);

        score = score - 1 + percent;
      }

      return score;
    },

    _getHint: function(score, evt) {
      if (score !== 0 && !score) {
        return this.opt.noRatedMsg;
      }

      var
        decimal = methods._getFirstDecimal.call(this, score),
        integer = Math.ceil(score),
        group   = this.opt.hints[(integer || 1) - 1],
        hint    = group,
        set     = !evt || this.move;

      if (this.opt.precision) {
        if (set) {
          decimal = decimal === 0 ? 9 : decimal - 1;
        }

        hint = group[decimal];
      } else if (this.opt.halfShow || this.opt.half) {
        decimal = set && decimal === 0 ? 1 : decimal > 5 ? 1 : 0;

        hint = group[decimal];
      }

      return hint === '' ? '' : hint || score;
    },

    _getWidth: function() {
      var width = this.stars[0].width || parseFloat(this.stars.eq(0).css('font-size'));

      if (!width) {
        methods._error.call(this, 'Could not get the icon width!');
      }

      return width;
    },

    _lock: function() {
      var hint = methods._getHint.call(this, this.score.val());

      this.style.cursor = '';
      this.title        = hint;

      this.score.prop('readonly', true);
      this.stars.prop('title', hint);

      if (this.cancel) {
        this.cancel.hide();
      }

      this.self.data('readonly', true);
    },

    _nameForIndex: function(i) {
      return this.opt.score && this.opt.score >= i ? 'starOn' : 'starOff';
    },

    _resetTitle: function(star) {
      for (var i = 0; i < this.opt.number; i++) {
        this.stars[i].title = methods._getHint.call(this, i + 1);
      }
    },

     _roundHalfScore: function(score) {
      var integer = parseInt(score, 10),
          decimal = methods._getFirstDecimal.call(this, score);

      if (decimal !== 0) {
        decimal = decimal > 5 ? 1 : 0.5;
      }

      return integer + decimal;
    },

    _roundStars: function(score, evt) {
      var
        decimal = (score % 1).toFixed(2),
        name    ;

      if (evt || this.move) {
        name = decimal > 0.5 ? 'starOn' : 'starHalf';
      } else if (decimal > this.opt.round.down) {               // Up:   [x.76 .. x.99]
        name = 'starOn';

        if (this.opt.halfShow && decimal < this.opt.round.up) { // Half: [x.26 .. x.75]
          name = 'starHalf';
        } else if (decimal < this.opt.round.full) {             // Down: [x.00 .. x.5]
          name = 'starOff';
        }
      }

      if (name) {
        var
          icon = this.opt[name],
          star = this.stars[Math.ceil(score) - 1];

        methods._setIcon.call(this, star, icon);
      }                                                         // Full down: [x.00 .. x.25]
    },

    _setIcon: function(star, icon) {
      star[this.opt.starType === 'img' ? 'src' : 'className'] = this.opt.path + icon;
    },

    _setTarget: function(target, score) {
      if (score) {
        score = this.opt.targetFormat.toString().replace('{score}', score);
      }

      if (target.is(':input')) {
        target.val(score);
      } else {
        target.html(score);
      }
    },

    _setTitle: function(score, evt) {
      if (score) {
        var
          integer = parseInt(Math.ceil(score), 10),
          star    = this.stars[integer - 1];

        star.title = methods._getHint.call(this, score, evt);
      }
    },

    _target: function(score, evt) {
      if (this.opt.target) {
        var target = jQuery(this.opt.target);

        if (!target.length) {
          methods._error.call(this, 'Target selector invalid or missing!');
        }

        var mouseover = evt && evt.type === 'mouseover';

        if (score === undefined) {
          score = this.opt.targetText;
        } else if (score === null) {
          score = mouseover ? this.opt.cancelHint : this.opt.targetText;
        } else {
          if (this.opt.targetType === 'hint') {
            score = methods._getHint.call(this, score, evt);
          } else if (this.opt.precision) {
            score = parseFloat(score).toFixed(1);
          }

          var mousemove = evt && evt.type === 'mousemove';

          if (!mouseover && !mousemove && !this.opt.targetKeep) {
            score = this.opt.targetText;
          }
        }

        methods._setTarget.call(this, target, score);
      }
    },

    _turnOn: function(i, score) {
      return this.opt.single ? (i === score) : (i <= score);
    },

    _unlock: function() {
      this.style.cursor = 'pointer';
      this.removeAttribute('title');

      this.score.removeAttr('readonly');

      this.self.data('readonly', false);

      for (var i = 0; i < this.opt.number; i++) {
        this.stars[i].title = methods._getHint.call(this, i + 1);
      }

      if (this.cancel) {
        this.cancel.css('display', '');
      }
    },

    cancel: function(click) {
      return this.each(function() {
        var self = jQuery(this);

        if (self.data('readonly') !== true) {
          methods[click ? 'click' : 'score'].call(self, null);

          this.score.removeAttr('value');
        }
      });
    },

    click: function(score) {
      return this.each(function() {
        if (jQuery(this).data('readonly') !== true) {
          score = methods._adjustedScore.call(this, score);

          methods._apply.call(this, score);

          if (this.opt.click) {
            this.opt.click.call(this, score, jQuery.Event('click'));
          }

          methods._target.call(this, score);
        }
      });
    },

    destroy: function() {
      return this.each(function() {
        var self = jQuery(this),
            raw  = self.data('raw');

        if (raw) {
          self.off('.raty').empty().css({ cursor: raw.style.cursor }).removeData('readonly');
        } else {
          self.data('raw', self.clone()[0]);
        }
      });
    },

    getScore: function() {
      var score = [],
          value ;

      this.each(function() {
        value = this.score.val();

        score.push(value ? +value : undefined);
      });

      return (score.length > 1) ? score : score[0];
    },

    move: function(score) {
      return this.each(function() {
        var
          integer  = parseInt(score, 10),
          decimal  = methods._getFirstDecimal.call(this, score);

        if (integer >= this.opt.number) {
          integer = this.opt.number - 1;
          decimal = 10;
        }

        var
          width   = methods._getWidth.call(this),
          steps   = width / 10,
          star    = jQuery(this.stars[integer]),
          percent = star.offset().left + steps * decimal,
          evt     = jQuery.Event('mousemove', { pageX: percent });

        this.move = true;

        star.trigger(evt);

        this.move = false;
      });
    },

    readOnly: function(readonly) {
      return this.each(function() {
        var self = jQuery(this);

        if (self.data('readonly') !== readonly) {
          if (readonly) {
            self.off('.raty').children('img').off('.raty');

            methods._lock.call(this);
          } else {
            methods._binds.call(this);
            methods._unlock.call(this);
          }

          self.data('readonly', readonly);
        }
      });
    },

    reload: function() {
      return methods.set.call(this, {});
    },

    score: function() {
      var self = jQuery(this);

      return arguments.length ? methods.setScore.apply(self, arguments) : methods.getScore.call(self);
    },

    set: function(options) {
      return this.each(function() {
        jQuery(this).raty(jQuery.extend({}, this.opt, options));
      });
    },

    setScore: function(score) {
      return this.each(function() {
        if (jQuery(this).data('readonly') !== true) {
          score = methods._adjustedScore.call(this, score);

          methods._apply.call(this, score);
          methods._target.call(this, score);
        }
      });
    }
  };

  jQuery.fn.raty = function(method) {
    if (methods[method]) {
      return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
    } else if (typeof method === 'object' || !method) {
      return methods.init.apply(this, arguments);
    } else {
      jQuery.error('Method ' + method + ' does not exist!');
    }
  };

  jQuery.fn.raty.defaults = {
    cancel       : false,
    cancelClass  : 'raty-cancel',
    cancelHint   : 'Cancel this rating!',
    cancelOff    : 'cancel-off.png',
    cancelOn     : 'cancel-on.png',
    cancelPlace  : 'left',
    click        : undefined,
    half         : false,
    halfShow     : true,
    hints        : ['bad', 'poor', 'regular', 'good', 'gorgeous'],
    iconRange    : undefined,
    mouseout     : undefined,
    mouseover    : undefined,
    noRatedMsg   : 'Not rated yet!',
    number       : 5,
    numberMax    : 20,
    path         : undefined,
    precision    : false,
    readOnly     : false,
    round        : { down: 0.25, full: 0.6, up: 0.76 },
    score        : undefined,
    scoreName    : 'score',
    single       : false,
    space        : true,
    starHalf     : 'star-half.png',
    starOff      : 'star-off.png',
    starOn       : 'star-on.png',
    starType     : 'img',
    target       : undefined,
    targetFormat : '{score}',
    targetKeep   : false,
    targetScore  : undefined,
    targetText   : '',
    targetType   : 'hint'
  };

})(jQuery);

            (function () {
                var width = 500,
                    reviewLengthMin = 25,
                    reviewLengthMax = 5000,
                    reviewSendTimeout = 500,
                    heartbeatInterval,
                    rrInstantSurveyContainer,
                    EmailExtra = '',
                    i,
                    postbackurl = 'http://www.resellerratings.com/instant/',
                    hashkey = '8ee0bdb9f15e865ce84c5ba0d2b9ae16',
                    questions
                    // import Questions 
=[{"id":"2","type":"5Star","text":"Rate your overall satisfaction with <strong>Softwaremedia<\/strong>.","answers":["Very Dissatisfied","Somewhat Dissatisfied","Neither Satisfied Nor Dissatisfied","Somewhat Satisfied","Very Satisfied"],"required":true},{"id":"3","type":"5Star","text":"Rate the cost of <strong>Softwaremedia<\/strong>'s products and services.","answers":["Very High Priced","High Priced","Moderately Priced","Low Priced","Very Low Priced"],"required":false},{"id":"10","type":"5Star","text":"How likely are you to shop at <strong>Softwaremedia<\/strong> in the future?","answers":["Not At All Likely","Not Very Likely","Somewhat Likely","Very Likely","Extremely Likely"],"required":false},{"id":"33","type":"5Star","text":"Rate <strong>Softwaremedia<\/strong>'s Customer Service overall, or choose n\/a if you did not interact with their customer service.","answers":["Poor","Fair","Good","Very Good","Excellent"],"required":false}]                        ,
                    QuestionManager = (function () {
                        var defaultRatyProperties = {
                                path: 'http://cdn3.resellerratings.com/CDN-1418194946/static/js/images',
                                targetKeep: true,
                                starOff : 'rr-brownedge-star-off.png',
                                starOn  : 'rr-brownedge-star-on.png',
                                single: false
                            },

                            state = {
                                answered: [],
                                required: [],
                                revealed: [],
                                hidden: [],
                                finalblockVisible: false,
                                validation: {},
                                id: ''
                            },

                            finalizeCommands = [],
                            rrID = function (qid) {
                                return '__rrid_instant_survey_question-' + qid;
                            },

                            sendDsply = function (action) {
                                var tag = document.createElement('script');
                                tag.src = 'https://www.dsply.com/index.php?pid=r39ax9Aazb';
                                tag.src += '&ctag=instant,seller:3677,'+action;
                                tag.async = true;
                                tag.type = 'text/javascript';

                                var first_script = document.getElementsByTagName('script')[0];
                                first_script.parentNode.insertBefore(tag, first_script);
                            },

                            handleReveal = function () {
                                var baseHeightAdjust = 0,
                                    hid,
                                    base,
                                    finalBlock,
                                    questionBox;

                                // final block
                                if (
                                    !state.finalblockVisible
                                    && (function(){
                                            var allRequired=true;
                                            for (var i = 0, n=state.required.length; i<n; ++i) {
                                                allRequired = allRequired && ( arrayContains(state.answered, state.required[i]));
                                            }
                                            return allRequired;
                                    }())
                                ) {
                                    finalBlock = finalBlock || jQuery('#__rr_instant_survey_final_block');
                                    base = base || finalBlock.parent();
                                    baseHeightAdjust += finalBlock.outerHeight(true);

                                    state.finalblockVisible = true;
                                }

                                if(state.hidden.length && state.answered.length === state.revealed.length ) {
                                    hid = state.hidden.shift();
                                    questionBox = jQuery('#' + rrID(hid) + '_box');
                                    base = base || questionBox.parent();
                                    baseHeightAdjust += questionBox.outerHeight(true);
                                    state.revealed.push(hid);
                                }
                                if(base) {
                                    base.animate({height: base.outerHeight(true) + baseHeightAdjust + 'px'},'fast');
                                }
                                if(questionBox) {
                                    questionBox.slideDown();
                                }
                                if(finalBlock) {
                                    finalBlock.slideDown();
                                }
                            },
                            postback = function (operations) {
                                jQuery.ajax(postbackurl+'record/',{
                                    type: 'POST',
                                    username: 'rrdev',
                                    password: 'panda95',
                                    headers: {
                                        'X-Requested-With': 'XMLHttpRequest'
                                    },
                                    data: {
                                        hash: hashkey,
                                        operations: operations
                                    }
                                });
                            },
                            sendInvoice = function (invoice) {
                                postback([{
                                    type: 'invoiceNumber',
                                    id: 0,
                                    value: invoice
                                }]);
                            },

                            handleAnswerQuestion = function (q, score) {
                                // update answered array
                                if ( !score && arrayContains(state.answered,q.id) ) {
                                    state.answered.splice(arrayIndexOf(state.answered,q.id),1);    
                                } else {
                                    state.answered.push(q.id);
                                }
                                postback([
                                    {
                                        type:'question',
                                        id: q.id,
                                        value: score
                                    }
                                ]);

                                handleReveal(q);
                            },


                            createQuestion = function (q) {
                                return jQuery('<div id="' + rrID(q.id) + '_q" class="Question">' + q.text + '</div>');
                            },
                            buildProperties = function (q) {
                                return jQuery.extend(true, {}, defaultRatyProperties,
                                         (q.required
                                            ? { cancel: false, noRatedMsg: "Rating Required", targetText: "Rating Required" }
                                            : { cancel: true, cancelValue: "0", cancelHint: "Don't Know", number: 5 }
                                         ),
                                        {
                                            hints: arrayMap(q.answers, function(c,i){return '<span class="Star' + i + '">' + c + '</span>';}),
                                            target: '#' + rrID(q.id) + '_star',
                                            click: function(score /*, event */ ) {
                                                handleAnswerQuestion(q,score);
                                            }
                                        }
                                    );
                            },

                            createRaty = function (q) {
                                var props = buildProperties(q);
                                finalizeCommands.push((function (p,q) {
                                    return function() {
                                        jQuery('#' + rrID(q.id)).raty(p);
                                    };
                                }(jQuery.extend({},props),q)));
                                return jQuery('<div id="' + rrID(q.id) + '" class="Raty" />');
                            },

                            createStar = function (q) {
                                return jQuery('<div id="' + rrID(q.id) + '_star" class="Star" />');
                            },

                            render = function (q) {
                                var retr =
                                    jQuery('<div id="' + rrID(q.id) + '_box" class="QuestionBox ' + (q.required ? 'Required' : '') + '" />')
                                        .append(createQuestion(q))
                                        .append(createRaty(q))
                                        .append(createStar(q))
                                        .append(jQuery('<div class="Clear" />'))
                                    ;

                                if( q.required ) {
                                    state.required.push(q.id);
                                    state.revealed.push(q.id);
                                } else if( state.revealed.length < 3 ) {
                                    state.revealed.push(q.id);
                                } else {
                                    state.hidden.push(q.id);
                                    retr.hide();
                                }
                                return retr;
                            },

                            finalize = function () {
                                for(i = 0; i < finalizeCommands.length; ++i) {
                                    finalizeCommands[i]();
                                }
                            },
                            setValidState = function (part, valid) {
                                state.validation[part]=valid;

                                var button=jQuery('#__rr_instant_survey_send_feedback');
                               if( (state.validation.review) && (undefined === state.validation.email || state.validation.email) ) {
                                   button.removeAttr('disabled');
                               } else {
                                   button.attr("disabled","disabled");
                               }
                            },
                            heartbeat = function() {
                                postback([{
                                    type: 'heartbeat',
                                    id: 0,
                                    value: 1
                                }]);
                            },
                            updateReview = debounce(function(reviewText) {
                                postback([{
                                    type: 'review',
                                    id: 0,
                                    value: reviewText
                                }]);
                            }, reviewSendTimeout),
                            checkAndSubmit = function () {
                                var isSendEmail=jQuery('#__rr_instant_survey_send_email_yes').is(':checked'),
                                    sendEmail=(isSendEmail
                                               ? '<div class="SendingEmail">We&#39;ll send you an email soon to follow up on your experience.</div>'
                                               : ''),
                                    review=jQuery('#__rr_instant_survey_review').val(),
                                    emailAddr=jQuery('#__rr_instant_survey_email').val(),

                                    postbackOperations = [
                                        {
                                            type:'review',
                                            id: 0,
                                            value: review
                                        },
                                        {
                                            type:'sendEmail',
                                            id: 0,
                                            value: (isSendEmail ? 1 : 0)
                                        }
                                    ];
                                if(isSendEmail && emailAddr) {
                                    postbackOperations.push(
                                        {
                                            type:'emailAddr',
                                            id: 0,
                                            value: emailAddr
                                        }
                                    );
                                }
                                postbackOperations.push(
                                    {
                                        type:'finalize',
                                        id: 0,
                                        value: ''
                                    }
                                );
                                postback(postbackOperations);
                                clearInterval(heartbeatInterval);
                                sendDsply('success');

                                jQuery('<div/>',{
                                    id: "__rrThanksBanner",
                                    style: 'position: absolute;'
                                        + 'width: ' + width + 'px;'
                                        + 'height: auto;'
                                        + 'top: -100%;'
                                        + 'left:50%;'
                                        + 'margin-left:' + (-(width / 2)) + 'px;'
                                })
                                    .click(function(){
                                        var banner=jQuery('#__rrThanksBanner');
                                        banner && banner.fadeOut('fast',function(){ banner.remove();});
                                    })
                                    .append('<img class="RRLogo" src="http://cdn2.resellerratings.com/CDN-1418194946/static/images/rr_logo.png" />')
                                    .append('<div>'
                                            + '<div class="Thanks subheader">Thanks for your feedback!</div>'
                                            + sendEmail
                                            + '</div>')
                                    .appendTo('body')
                                    .animate({top: '0px'}, 'slow',function(){
                                        setTimeout(function(){
                                            var banner=jQuery('#__rrThanksBanner');
                                            banner && banner.fadeOut('fast',function(){ banner.remove();});
                                        },3000);
                                    });

                                rrInstantSurveyContainer && rrInstantSurveyContainer.fadeOut('fast',function(){ rrInstantSurveyContainer.remove();});
                            },
                            close = function() {
                                clearInterval(heartbeatInterval);
                                rrInstantSurveyContainer && rrInstantSurveyContainer.fadeOut('fast',function(){ rrInstantSurveyContainer.remove();});
                                sendDsply('failure');
                            }
                            ;

                            sendDsply('presented');
                            // start heartbeating every second
                            heartbeatInterval = setInterval(heartbeat,1000);

                        return {
                            Render: render,
                            Finalize: finalize,
                            CheckAndSubmit: checkAndSubmit,
                            UpdateReview: updateReview,
                            Close: close,
                            SetValidState: setValidState,
                            SendInvoice: sendInvoice
                        };
                    }())
                    ;


                // import style sheet
                jQuery("<link type='text/css' href='http://cdn4.resellerratings.com/CDN-1418194946/static/css/popup/instant.css?reload=208760811' rel='stylesheet' />")
                    .appendTo('head');

                // create basic popup div
                rrInstantSurveyContainer=jQuery('<div/>', {
                    id: '__rr_instant_survey_container',
                    style: 'position:absolute;'
                         + 'width: ' + width + 'px;'
                         + 'height: auto;'
                         + 'top: -100%;'
                         + 'left: 50%;'
                         + 'margin-left:' + (-(width / 2)) + 'px;'
                         + 'opacity: 0.1;'
                });

                // add a bunch of hidden and non hidden questions
                rrInstantSurveyContainer
                    .append(jQuery('<div class="CloseButton" />')
                                .click(function () {
                                    QuestionManager.Close();
                                })
                                .append(jQuery('<img style="opacity: 0.8;" src="http://cdn5.resellerratings.com/CDN-1418194946/static/images/close.png" />')
                                    .hover(function() { jQuery(this).animate({opacity: '1'}, 'fast');},
                                        function() { jQuery(this).animate({opacity: '0.8'}, 'fast');})
                                    )
                           )
                    .append('<div class="Greeting">How was your experience with Softwaremedia?</div>')
                    .append('<div class="SubText"> Share your shopping experience, have your issues resolved as this merchant participates in our Merchant Member Care Program.</div><div class="separator"></div><hr/>');

                for(i=0; i<questions.length; ++i) {
                    rrInstantSurveyContainer
                        .append(QuestionManager.Render(questions[i]));
                }

                // check for email address
                if( "object" === typeof _rrES ) {
                    if( ! (_rrES.hasOwnProperty('email')
                        && _rrES.email.match(/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9._%+\-]+\.[a-zA-Z]{2,4}jQuery/)) ){
                        EmailExtra = jQuery('<div class="Label"><br/>'
                                           + 'Your Email Address <span style="font-size:8px;color:red"> (Required) </span>'
                                           + '<div id="__rr_instant_survey_email_feedback" class="Invalid"></div>'
                                       + '</div>')
                                      .append(jQuery('<div class="Email"><input type="text" id="__rr_instant_survey_email"></div>')
                                            .on('input',function(){
                                                var feedback=jQuery('#__rr_instant_survey_email_feedback');
                                                if(jQuery('#__rr_instant_survey_email').val().match(/[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9._%+\-]+\.[a-zA-Z]{2,4}jQuery/)) {
                                                    feedback
                                                        .addClass('Valid')
                                                        .removeClass('Invalid')
                                                        .text('Valid');
                                                    QuestionManager.SetValidState('email',true);
                                                } else {
                                                    feedback
                                                        .removeClass('Valid')
                                                        .addClass('Invalid')
                                                        .text('Invalid');
                                                    QuestionManager.SetValidState('email',false);
                                                }
                                            })

                                         );

                        QuestionManager.SetValidState('email',false);
                    } else if(_rrES.hasOwnProperty('email')) {
                        EmailExtra = jQuery('<input type="hidden" id="__rr_instant_survey_email" value="'+_rrES.email+'">');
                        QuestionManager.SetValidState('email',true);
                    }                 
                }

                QuestionManager.SetValidState('review',false);
                rrInstantSurveyContainer
                    .append(jQuery('<div id="__rr_instant_survey_final_block" class="FinalBlock" />')
                            .append(jQuery('<div class="Label">Tell us more about your experience <span style="font-size:8px;color:red"> (Required) </span>'
                                      + '<div id="__rr_instant_survey_review_feedback" class="Invalid">Minimum characters: ' + reviewLengthMin + '</div></div>'))
                            .append(jQuery('<textarea id="__rr_instant_survey_review"/>')
                                    .on('keyup',function(){
                                        var feedback=jQuery('#__rr_instant_survey_review_feedback'),
                                            reviewarea=jQuery('#__rr_instant_survey_review'),
                                            len=reviewarea.val().length
                                            ;

                                        // send partial
                                        QuestionManager.UpdateReview(reviewarea.val());

                                        if(len === 0) {
                                            if(feedback) {
                                                feedback
                                                    .addClass('Invalid')
                                                    .removeClass('Valid')
                                                    .text('Minimum characters: ' + reviewLengthMin);
                                            }
                                            QuestionManager.SetValidState('review',false);
                                        } else if( len < reviewLengthMin ) {
                                            if(feedback) {
                                                feedback
                                                    .addClass('Invalid')
                                                    .removeClass('Valid')
                                                    .text( (reviewLengthMin-len).toString() + ' to go...');
                                            }
                                            QuestionManager.SetValidState('review',false);
                                        } else if( len > reviewLengthMax ) {
                                            if(feedback) {
                                                feedback
                                                    .addClass('Invalid')
                                                    .removeClass('Valid')
                                                    .text( (len-reviewLengthMax).toString() + ' too many!');
                                            }
                                            QuestionManager.SetValidState('review',false);
                                        } else {
                                            if(feedback) {
                                                feedback
                                                    .removeClass('Invalid')
                                                    .addClass('Valid')
                                                    .text( len + ' chars');
                                            }
                                            QuestionManager.SetValidState('review',true);
                                        }
                                    })
                                   )
                            .append(jQuery('<div class="LabelNote">Would you like us to follow up with your experience with an email once you&apos;ve received your order?</div>'))
                            .append(jQuery('<div class="Choice">')
                                    .append(jQuery('<input type="radio" id="__rr_instant_survey_send_email_yes" name="follow_up_email" checked/>')
                                            .click(function(){
                                                QuestionManager.SetValidState('followup',true);
                                                jQuery('#__rr_instant_survey_container .FinalBlock input[type="radio"]:not(:checked) + label span')
                                                    .removeClass('is_checked');
                                                jQuery('#__rr_instant_survey_container .FinalBlock input[type="radio"]:checked + label span')
                                                    .addClass('is_checked');
                                            })
                                           )
                                    .append(jQuery('<label for="__rr_instant_survey_send_email_yes"><span class="is_checked" />Yes!</label>'))
                                    .append(jQuery('<input type="radio" id="__rr_instant_survey_send_email_no" name="follow_up_email"/>')
                                            .click(function(){
                                                QuestionManager.SetValidState('followup',false);
                                                jQuery('#__rr_instant_survey_container .FinalBlock input[type="radio"]:not(:checked) + label span')
                                                    .removeClass('is_checked');
                                                jQuery('#__rr_instant_survey_container .FinalBlock input[type="radio"]:checked + label span')
                                                    .addClass('is_checked');
                                            })
                                           )
                                    .append(jQuery('<label for="__rr_instant_survey_send_email_no"><span />No</label>'))
                                    )
                            .append(EmailExtra)
                            .append(jQuery('<p class="Terms" >By clicking the &#39;Send Feedback&#39; button, you agree to ResellerRatings <a target="_blank" href="/terms">Terms</a> and <a target="_blank" href="/privacy-policy">Privacy</a> Policy.</p>'))
                            .append(jQuery('<button id="__rr_instant_survey_send_feedback" class="Button" disabled>Send Feedback</button>')
                                    .click(QuestionManager.CheckAndSubmit)
                                   )
                                   .append('<div class="Clear" />')
                            .hide()
                        )

                    // Add Footer
                    .append(jQuery('<div class="Footer" />')
                        .append('<img class="RRLogo" src="http://cdn5.resellerratings.com/CDN-1418194946/static/images/rr_logo.png" />')
                        .append('<img class="SellerLogo" src="" />')
                        .append('<div class="Clear" />')
                    )

                    // Animate div in
                    .animate({top: '10px', opacity: '1'}, 'slow')
                    .appendTo('body');


                if( "object" === typeof _rrES && _rrES.hasOwnProperty('invoice') ) {
                    QuestionManager.SendInvoice(_rrES.invoice);
                }

                // must be after contents are appended to the body
                QuestionManager.Finalize();

            }());

            return;
        },

        versionCompare = function (v1, v2) {
            var v1parts = v1.split('.'),
                v2parts = v2.split('.'),
                v1seg,
                v2seg,
                vdiff,

                isValidPart = function (x) {
                    return (/^\d+jQuery/).test(x);
                };

            if (!v1parts.every(isValidPart) || !v2parts.every(isValidPart)) {
                return NaN;
            }

            v1parts = arrayMap(v1parts,Number);
            v2parts = arrayMap(v2parts,Number);

            while (v1parts.length || v2parts.length) {
                v1seg = v1parts.shift() || 0;
                v2seg = v2parts.shift() || 0;
                vdiff = v1seg - v2seg;
                if (0 !== vdiff) {
                    return vdiff;
                }
            }
            return 0;
        },

        checkLoaded = function (saveOriginal) {
            return window.setTimeout((function () {
                return function () {
                    var temp;
                    if (typeof jQuery === "undefined" || jQuery === null) {
                        return checkLoaded(saveOriginal);
                    }
                    if (saveOriginal) {
                        temp = jqSave;
                        jqSave = jQuery.noConflict(true);
                        //window.jQuery = temp;
                        window.jQuery = jQuery;
                    } else {
                        jqSave = jQuery;
                    }
                    return continueInitialization();
                };
            }(this)), 500);
        },

        injectjQuery = function (saveOriginal) {
            var head, script;
            script = document.createElement('script');
            script.src = jQuerySrc;
            head = document.getElementsByTagName('head')[0];
            if (saveOriginal) {
                jqSave = jQuery.noConflict(true);
            }
            head.appendChild(script);
            return checkLoaded(saveOriginal);
        },

        checkAndInjectjQuery = function () {
            if (allowInstalledVersion) {
                if (typeof jQuery !== "undefined" && jQuery !== null) {
                    if (allowInstalledVersion || versionCompare(jQuery.fn.jquery, minjQueryVersion) < 0) {
                        return injectjQuery(true);
                    }
                    jqSave = window.jQuery;
                    return true;
                }
                return injectjQuery(false);
            }
            return injectjQuery(true);
        },

        init = function () {
            return checkAndInjectjQuery();
        };


    return {
        init: init
    };
}());

Injector.init();
