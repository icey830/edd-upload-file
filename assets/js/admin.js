/*global jQuery, document, edd_upload_file_vars*/
jQuery(document).ready(function ($) {
    'use strict';

    var EDD_Upload_File_Download, EDD_Upload_File_Settings;

    /**
     * Download post type
     */
    EDD_Upload_File_Download = {
        init : function () {
            this.general();
        },

        general : function () {
            $('input[name="_edd_upload_file_enabled"]').change(function () {
                if ($(this).is(':checked')) {
                    $('#edd_upload_file_limit_wrap').css('display', 'block');
                    $('#edd_upload_file_extensions_wrap').css('display', 'block');
                } else {
                    $('#edd_upload_file_limit_wrap').css('display', 'none');
                    $('#edd_upload_file_extensions_wrap').css('display', 'none');
                }
            }).change();
        }
    };
    EDD_Upload_File_Download.init();

    /**
     * Settings page
     */
    EDD_Upload_File_Settings = {
        init : function () {
            this.general();
        },

        general : function () {
            $('.edd-upload-file-show-file-types').on('click', function (e) {
                e.preventDefault();

                if($('.edd-upload-file-ext-list').is(':visible')) {
                    $('.edd-upload-file-ext-list').fadeOut('fast', function () {
                        $(this).css('display', 'none');
                    });
                    $(this).html(edd_upload_file_vars.show_file_types);
                } else {
                    $('.edd-upload-file-ext-list').fadeIn('fast').css('display', 'inline-block');
                    $(this).html(edd_upload_file_vars.hide_file_types);
                }
            });
        }
    };
    EDD_Upload_File_Settings.init();
});
