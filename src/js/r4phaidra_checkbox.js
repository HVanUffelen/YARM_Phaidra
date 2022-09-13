;(function($) {
    var onready = function () {
        $(document).on('change', '.file-license', function(event) {
            event.preventDefault();
            var checkbox = $(this).parent('.form-row').find('.r4phaidra_cbx');
            var dropdown = $(this).find('.file-license-custom');

            if (dropdown.val() == 1) {
                checkbox.prop("checked", false)
                checkbox.prop("disabled", true)
            } else {
                checkbox.prop("disabled", false)
            }

        });

        $(window).on( "YarmFileListResetIndex", function(_event, files) {

            var index = 1;
            var r4poffset = 0;
            files.find('div.file-r4phaidra').each(function(){
                var files4phaidra = $(this);
                files4phaidra.find('input').attr('id', 'r4p_' + index);
                files4phaidra.find('input').attr('name', 'r4phaidra[' + r4poffset + ']');
                files4phaidra.find('label').attr('for', 'r4p_' + index);
                index += 1;
                r4poffset += 1;
            });

            index = 1;

            files.find('div.file-license').each(function(){

                var fileLicense = $(this);
                fileLicense.find('select').attr('id', 'fl_' + index);
                fileLicense.find('label').attr('for', 'fl_' + index);

                if (fileLicense.find('select').attr('id', 'fl_' + index).val() == 1) {
                    var checkbox = fileLicense.parents('.form-row').find('.r4phaidra_cbx');
                    checkbox.prop("checked", false);
                    checkbox.prop("disabled", true);
                }

                index += 1;
            });
        } );

        $(window).on( "YarmFileListFileClone", function(_event, clone) {
            clone.find('.file-license').val('1');
            clone.find('.r4phaidra_cbx').prop('checked',false);

        } );

    };
    $(document).ready(onready);
}(jQuery));
