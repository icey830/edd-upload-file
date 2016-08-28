/*global jQuery, document, console, qq, edd_upload_file_vars*/
jQuery(document).ready(function ($) {
    'use strict';

    var EDD_Upload_File_Uploader;

    /**
     * Uploader JS
     */
    EDD_Upload_File_Uploader = {
        init : function () {
            this.general();
        },

        general : function () {
            var debugging = false;

            if( edd_upload_file_vars.debug === '1') {
                debugging = true;
            }

            $('.edd-upload-file-uploader').each(function () {
                var extensions = $(this).data('extensions');
                var item_id = $(this).data('item-id');

                if( extensions === false ) {
                    extensions = [];
                } else {
                    extensions = extensions.split(',');
                }

                new qq.FineUploader({
                    debug: debugging,
                    element: document.getElementById($(this).prop('id')),
                    request: {
                        endpoint: edd_upload_file_vars.endpoint + 'process'
                    },
                    deleteFile: {
                        enabled: true,
                        endpoint: edd_upload_file_vars.endpoint + 'process'
                    },
                    chunking: {
                        enabled: true,
                        concurrent: {
                            enabled: true
                        },
                        success: {
                            endpoint: edd_upload_file_vars.endpoint + 'done'
                        }
                    },
                    resume: {
                        enabled: true
                    },
                    retry: {
                        enableAuto: true,
                        showButton: true
                    },
                    thumbnails: {
                        placeholders: {
                            notAvailablePath: edd_upload_file_vars.placeholder_url + '/not_available-generic.png',
                            waitingPath: edd_upload_file_vars.placeholder_url + '/waiting-generic.png'
                        }
                    },
                    validation: {
                        itemLimit: $(this).data('limit'),
                        allowedExtensions: extensions
                    },
                    callbacks: {
                        onComplete: function (id, name, response, xhr) {
                            if($('input[name="edd-gateway"]').length) {
                                $('input[name="edd-gateway"]').after('<input type="hidden" name="edd-upload-file[]" value="{' + item_id + '}{' + response.uuid + '}{' + name + '}" class="edd-upload-file-' + id + '" />');
                            } else {
                                $('#edd-upload-file-form').append('<input type="hidden" name="edd-upload-file[]" value="{' + item_id + '}{' + response.uuid + '}{' + name + '}" class="edd-upload-file-' + id + '" />');
                                $('#edd-upload-file-form').append('<input type="hidden" name="edd-upload-file-uuid[' + id + ']" value="' + response.uuid + '" />');

                                var postData = {
                                    action: 'edd_upload_file_process',
                                    download_data: item_id,
                                    uuid: response.uuid,
                                    filename: name,
                                    payment_id: $('#edd-upload-file-payment-id').val()
                                };

                                $.ajax({
                                    type: 'POST',
                                    data: postData,
                                    dataType: 'json',
                                    url: edd_upload_file_vars.ajaxurl,
                                    xhrFields: {
                                        withCredentials: true
                                    },
                                    success: function (response) {
                                        console.log(response);
                                    }
                                }).fail(function (data) {
                                    if ( window.console && window.console.log ) {
                                        console.log( data );
                                    }
                                });
                            }
                        },
                        onDeleteComplete: function (id, xhr, iserror) {
                            if($('input[name="edd-gateway"]').length) {
                                $('.edd-upload-file-' + id).remove();
                            } else {
                                var postData = {
                                    action: 'edd_upload_file_delete',
                                    download_data: $('.edd-upload-file-' + id).val(),
                                    payment_id: $('#edd-upload-file-payment-id').val()
                                };

                                $.ajax({
                                    type: 'POST',
                                    data: postData,
                                    dataType: 'json',
                                    url: edd_upload_file_vars.ajaxurl,
                                    xhrFields: {
                                        withCredentials: true
                                    },
                                    success: function (response) {
                                        console.log(response);
                                    }
                                }).fail(function (data) {
                                    if ( window.console && window.console.log ) {
                                        console.log( data );
                                    }
                                });
                            }
                        }
                    }
                });
            });

            $('.edd-upload-file-uploader-show').on('click', function (e) {
                e.preventDefault();

                $(this).parent().next('.edd-upload-file-uploader').fadeIn('fast').css('display', 'block');

                $(this).fadeOut('fast', function () {
                    $(this).css('display', 'none');
                    $(this).next('.edd-upload-file-uploader-hide').fadeIn('fast').css('display', 'inline-block');
                });
            });

            $('.edd-upload-file-uploader-hide').on('click', function (e) {
                e.preventDefault();

                $(this).parent().next('.edd-upload-file-uploader').fadeOut('fast', function () {
                    $(this).css('display', 'none');
                });

                $(this).fadeOut('fast', function () {
                    $(this).css('display', 'none');
                    $(this).prev('.edd-upload-file-uploader-show').fadeIn('fast').css('display', 'inline-block');
                });
            });

        }
    };
    EDD_Upload_File_Uploader.init();
});
