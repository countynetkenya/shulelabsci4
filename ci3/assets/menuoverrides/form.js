(function($){
    function parseBoolean(value){
        return value === true || value === 'true' || value === 1 || value === '1';
    }

    function initSelect2($element){
        if(!$element.length || !$element.select2){
            return;
        }
        var allowNew = parseBoolean($element.data('allow-new'));
        var placeholder = $element.data('placeholder') || '';
        var allowClear = parseBoolean($element.data('allow-clear'));
        $element.select2({
            theme: 'bootstrap',
            width: '100%',
            tags: allowNew,
            placeholder: placeholder,
            allowClear: allowClear
        });
    }

    function toggleCreateIfMissing(){
        var enabled = $('#create_if_missing_enabled').is(':checked');
        var $fields = $('.js-create-if-missing-fields');
        $fields.toggleClass('hidden', !enabled);
        $fields.find(':input').prop('disabled', !enabled);
    }

    function toggleOverrideType(){
        var type = $('#override_type').val();
        var isRelocation = type === 'relocation';
        var $customGroups = $('.js-custom-only');
        $customGroups.toggleClass('hidden', isRelocation);
        $customGroups.find(':input').prop('disabled', isRelocation);
        $('.js-relocation-hint').toggleClass('hidden', !isRelocation);
        $('#icon').trigger('change');
    }

    function updateTranslationPreview(){
        var $menuName = $('#menuName');
        var translations = $menuName.data('menuTranslations') || {};
        var missing = $menuName.data('translationMissing') || '';
        var label = $menuName.data('translationLabel') || '';
        var targetSelector = $menuName.data('translationTarget');
        if(!targetSelector){
            return;
        }
        var $target = $(targetSelector);
        if(!$target.length){
            return;
        }
        var value = $menuName.val();
        if(!value){
            $target.addClass('hidden').empty();
            return;
        }
        var translation = translations[value];
        if(translation){
            $target.removeClass('hidden').html(label + ': ' + $('<div/>').text(translation).html());
        } else {
            $target.removeClass('hidden').html(label + ': <span class="text-warning">' + $('<div/>').text(missing).html() + '</span>');
        }
    }

    $(function(){
        $('.js-select2').each(function(){
            initSelect2($(this));
        });

        toggleCreateIfMissing();
        $('#create_if_missing_enabled').on('change', toggleCreateIfMissing);

        toggleOverrideType();
        $('#override_type').on('change select2:select', toggleOverrideType);

        $('#menuName').on('change', updateTranslationPreview);
        $('#menuName').on('select2:select select2:clear', updateTranslationPreview);
        updateTranslationPreview();
    });
})(jQuery);
