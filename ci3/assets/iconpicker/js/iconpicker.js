(function($){
    var ICON_LIST_PROMISE = null;
    var ACTIVE_PICKER = null;
    var defaults = {
        source: 'assets/iconpicker/icons.json'
    };

    function fetchIcons(source){
        if(!ICON_LIST_PROMISE){
            ICON_LIST_PROMISE = $.getJSON(source).fail(function(){
                ICON_LIST_PROMISE = null;
            });
        }
        return ICON_LIST_PROMISE;
    }

    function buildPicker(options){
        options = options || {};
        var $picker = $('<div class="iconpicker-popover" />');
        var $searchWrapper = $('<div class="iconpicker-search" />').appendTo($picker);
        var placeholder = options.searchPlaceholder || 'Search icons';
        var $search = $('<input type="search" class="form-control" aria-label="' + placeholder + '" />');
        $search.attr('placeholder', placeholder);
        $searchWrapper.append($search);
        var $grid = $('<div class="iconpicker-grid" />');
        $picker.append($grid);
        var emptyText = options.emptyText || 'No icons match your search.';
        var $empty = $('<div class="iconpicker-empty" style="display:none;" />').text(emptyText);
        $picker.append($empty);
        return {
            container: $picker,
            search: $search,
            grid: $grid,
            empty: $empty
        };
    }

    function positionPicker($trigger, $picker){
        var offset = $trigger.offset();
        var height = $trigger.outerHeight();
        $picker.css({
            top: offset.top + height + 4,
            left: offset.left
        });
    }

    function hidePicker(){
        if(ACTIVE_PICKER){
            ACTIVE_PICKER.container.remove();
            $(document).off('.iconpicker');
            ACTIVE_PICKER = null;
        }
    }

    function filterIcons(state){
        var term = state.search.val().toLowerCase();
        var anyVisible = false;
        state.grid.children().each(function(){
            var $item = $(this);
            var icon = $item.data('icon');
            var match = !term || icon.indexOf(term) !== -1;
            $item.toggle(match);
            if(match){
                anyVisible = true;
            }
        });
        state.empty.toggle(!anyVisible);
    }

    function bindGlobalHandlers(state){
        $(document).on('mousedown.iconpicker', function(evt){
            if(!$(evt.target).closest('.iconpicker-popover, .js-iconpicker-toggle').length){
                hidePicker();
            }
        });
        $(document).on('keydown.iconpicker', function(evt){
            if(evt.key === 'Escape'){
                hidePicker();
            }
        });
        state.search.on('input', function(){
            filterIcons(state);
        });
    }

    function renderIcons(state, icons, $input, $preview){
        state.grid.empty();
        $.each(icons, function(_, icon){
            var iconClass = 'fa-' + icon;
            var $item = $('<button type="button" class="iconpicker-item" />');
            $item.attr('title', iconClass);
            $item.data('icon', iconClass);
            $item.append($('<i />').addClass('fa ' + iconClass));
            $item.on('click', function(){
                $input.val(iconClass).trigger('change');
                if($preview && $preview.length){
                    $preview.removeClass (function (index, className) {
                        return (className.match (/(^|\s)fa-[^\s]+/g) || []).join(' ');
                    });
                    if(iconClass){
                        $preview.addClass(iconClass);
                    }
                }
                hidePicker();
            });
            state.grid.append($item);
        });
        filterIcons(state);
    }

    function preparePreview($input, $preview){
        if(!$preview || !$preview.length){
            return;
        }
        var current = $input.val();
        $preview.removeClass(function(index, className){
            return (className.match (/(^|\s)fa-[^\s]+/g) || []).join(' ');
        });
        if(current){
            $preview.addClass(current);
        }
    }

    $.fn.iconPicker = function(options){
        var settings = $.extend({}, defaults, options || {});
        return this.each(function(){
            var $trigger = $(this);
            var targetSelector = $trigger.data('target');
            if(!targetSelector){
                return;
            }
            var $input = $(targetSelector);
            if(!$input.length){
                return;
            }
            var $preview = null;
            var previewSelector = $trigger.data('preview');
            if(previewSelector){
                $preview = $(previewSelector);
                preparePreview($input, $preview);
            }
            $input.on('change.iconpicker input.iconpicker', function(){
                preparePreview($input, $preview);
            });
            $trigger.on('click', function(evt){
                evt.preventDefault();
                var source = $trigger.data('iconSource') || settings.source;
                hidePicker();
                fetchIcons(source).done(function(icons){
                    if(!$.isArray(icons)){
                        return;
                    }
                    var pickerText = {
                        searchPlaceholder: $trigger.data('iconSearchPlaceholder'),
                        emptyText: $trigger.data('iconEmptyText')
                    };
                    var state = buildPicker(pickerText);
                    ACTIVE_PICKER = state;
                    $('body').append(state.container);
                    positionPicker($trigger, state.container);
                    renderIcons(state, icons, $input, $preview);
                    bindGlobalHandlers(state);
                    state.search.val('');
                    state.search.trigger('focus');
                });
            });
            $trigger.closest('.iconpicker-wrapper').find('.js-iconpicker-clear').on('click', function(evt){
                evt.preventDefault();
                $input.val('').trigger('change');
                if($preview && $preview.length){
                    $preview.removeClass(function(index, className){
                        return (className.match (/(^|\s)fa-[^\s]+/g) || []).join(' ');
                    });
                }
            });
        });
    };

    $(function(){
        $('.js-iconpicker-toggle').iconPicker();
    });
})(jQuery);
