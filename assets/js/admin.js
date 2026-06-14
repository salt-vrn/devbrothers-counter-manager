(function($) {
    'use strict';

    var cmInstances = [];

    function initCounterCards() {
        $('.dbcm-counter-card').each(function() {
            var $card = $(this);
            var $toggle = $card.find('.dbcm-toggle input[type="checkbox"]');
            var $status = $card.find('.dbcm-counter-status');

            function updateState() {
                var active = $toggle.is(':checked');
                $card.toggleClass('dbcm-active', active);
                $status
                    .text(active ? dbcmConfig.strings.enabled : dbcmConfig.strings.disabled)
                    .toggleClass('dbcm-status-active', active);

                if (active) {
                    $card.find('.dbcm-code-editor').each(function() {
                        var cm = $(this).data('cmInstance');
                        if (cm) {
                            setTimeout(function() { cm.refresh(); }, 50);
                        }
                    });
                }
            }

            $toggle.on('change', updateState);
            updateState();
        });
    }

    function initCodeEditors() {
        if (typeof wp === 'undefined' || !wp.codeEditor || !dbcmConfig.codeEditor) {
            return;
        }

        $('.dbcm-code-editor').each(function() {
            var $textarea = $(this);
            var editor = wp.codeEditor.initialize($textarea, dbcmConfig.codeEditor);
            $textarea.data('cmInstance', editor.codemirror);
            cmInstances.push(editor.codemirror);
        });
    }

    function syncEditorsOnSubmit() {
        $('form').on('submit', function() {
            cmInstances.forEach(function(cm) {
                cm.save();
            });
        });
    }

    $(function() {
        initCounterCards();
        initCodeEditors();
        syncEditorsOnSubmit();
    });

})(jQuery);
