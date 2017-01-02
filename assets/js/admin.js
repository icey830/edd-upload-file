/*global jQuery, document, edd_upload_file_vars, confirm, console*/
jQuery(document).ready(function ($) {
    'use strict';

    var EDD_Upload_File_Download, EDD_Upload_File_Settings, EDD_Upload_File_Payment;

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

    /**
     * Payment page
     */
    EDD_Upload_File_Payment = {
        init : function () {
            this.general();
        },

        general : function () {
            $( document.body ).on('click', '.edd-upload-file-delete-file', function(e) {
                e.preventDefault();

                var file_name = $(this).data('file-name');
                var confirm_message = edd_upload_file_vars.delete_file.replace( '{filename}', file_name );

                if( confirm( confirm_message ) ) {
                    var postData = {
                        action : 'edd_upload_file_delete_file',
                        file_path : $(this).data('file-path'),
                        payment_id : $(this).data('payment-id')
                    };

                    $.ajax({
                        type: 'POST',
                        data: postData,
                        url: edd_upload_file_vars.ajaxurl,
                        success: function (response) {
                            window.location.href=window.location.href;
                            return false;
                        }
                    }).fail(function (data) {
                        if ( window.console && window.console.log ) {
                            console.log( data );
                        }
                    });
                    return true;
                }
            });
        }
    };
    EDD_Upload_File_Payment.init();
});
