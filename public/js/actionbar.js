(function() {

    var getRecordSelection = function ($target) {
        let recordIdList = [];
        let ssel = null;

        if ($target !== undefined) {
            if ($target.hasClass('results_window')) {
                if (window.p4.Results) {
                    recordIdList = window.p4.Results.Selection.get();
                }

                else {
                    // for Phraseanet v4.1 and up
                    recordIdList = window.searchResult.selection.serialize();
                }
            }
            else if ($target.hasClass('basket_window')) {
                if (window.p4.WorkZone) {
                    recordIdList = window.p4.WorkZone.Selection.get();
                }
                else {
                    // for Phraseanet v4.1 and up
                    recordIdList = window.workzoneOptions.selection.serialize();
                }
                if(recordIdList.length === 0) {
                    ssel = $('.SSTT.active').attr('id').split('_').slice(1, 2).pop();
                }
            }
        }
        else {
            // assume that we want the results selection:
            if (window.p4.Results) {
                recordIdList = window.p4.Results.Selection.get();
            }
            else {
                // for Phraseanet v4.1 and up
                recordIdList = window.searchResult.selection.serialize();
            }
        }

        return {
            'lst': recordIdList,
            'ssel' : ssel
        };
    };


    /**
     * option 1 of actionbar "push" dropdown
     *
     * will simply say hello
     */
    $(document).on('click', '.TOOL_skeleton_btn_1', function () {
        var $dialog;
        try {
            //  phraseanet 4.0
            $dialog = p4.Dialog.Create({
                size:    'Medium',
                title:   'Skeleton',
                buttons: {
                    'Cancel': function () {
                        p4.Dialog.Close(1);
                    }
                }
            });
        }
        catch (e) {
            // phraseanet 4.1
            $dialog = dialog.create({}, {
                size: 'Medium',
                title: 'Skeleton'
            }, 2);
        }

        $.ajax({
            type: "POST",
            url: "../skeleton_1/",      // will simply return "hello"
            dataType: 'html',
            data: { },
            success: function (data) {
                $dialog.setContent(data);
            }
        });
    });



    /**
     * option 2 of actionbar "push" dropdown
     */
    $(document).on('click', '.TOOL_skeleton_btn_2', function () {

        var sel = getRecordSelection($(this));
        var $dialog;
        var dialog_level = -1;      // changed to 2 if phraseanet 4.1

        try {
            //  phraseanet 4.0
            $dialog = p4.Dialog.Create({
                size:    'Medium',
                title:   'Skeleton',
                buttons: {
                    'Cancel': function () {
                        p4.Dialog.Close(1);
                    }
                }
            });
        }
        catch (e) {
            // phraseanet 4.1
            dialog_level = 2;
            $dialog = dialog.create({}, {
                size: 'Medium',
                title: 'Skeleton'
            }, dialog_level);
        }

        $.ajax({
            type: "POST",
            url: "../skeleton_2/",
            dataType: 'html',
            data: {
                'dialog_level': dialog_level,
                lst: sel.lst.join(';'),
                ssel: sel.ssel
            },
            success: function (data) {
                $dialog.setContent(data);
            }
        });
    });

})();
