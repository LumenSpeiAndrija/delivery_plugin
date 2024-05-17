(function( $ ) {

	$(document).ready(function(){
        $('#deliveryfrom_delete_service').click(function(){
            $('#deliveryfrom_services .current').remove();
        })
    })

    $(document).ready(function(){
        $('.edit-php.post-type-shop_order #doaction, .edit-php.post-type-shop_order #doaction2').click(function(e){

            if($('.bulkactions [name="action"]').val().includes('deliveryfrom_bulk_')){

                e.preventDefault();

                var posts = [];
                var method = $('.bulkactions [name="action"]').val();

                $('[name="post[]"]:checked').each(function(){
                    posts.push($(this).val());
                });
                
                deliveryfrom_print_bulk(posts, method);
                
            }
        });

        $('.woocommerce_page_wc-orders #doaction, .woocommerce_page_wc-orders #doaction2').click(function(e){

            if($('.bulkactions [name="action"]').val().includes('deliveryfrom_bulk_')){

                e.preventDefault();

                var posts = [];
                var method = $('.bulkactions [name="action"]').val();

                $('[name="id[]"]:checked').each(function(){
                    posts.push($(this).val());
                });
                
                deliveryfrom_print_bulk(posts, method);
                
            }
        });
    })

    $(document).ready(function(){
        $('#deliveryfrom_add_service').click(function(){
            $.blockUI({
                baseZ: 99999,
                message: null,
                overlayCSS:  { 
                    backgroundColor: '#fff', 
                    opacity:         0.6, 
                    cursor:          'wait' 
                },
            })   

            let data = {
                'action': 'deliveryfrom_add_method',
            };

            $.post(ajaxurl, data, function(response) {

                let newid = parseInt($('#deliveryfrom_services > tr:last-child input[data-attribute="deliveryfrom_service"]').data('id')) + 1;

                $('#deliveryfrom_services').append(response.replaceAll('{{newid}}', newid));
                $('#deliveryfrom_add_service').prop('disabled', true);
                $.unblockUI();
            });
        })
    })

    $(document).ready(function(){
        $('.deliveryfrom_print').click(
            function(){
                deliveryfrom_print_single(this);
            }
        )
    })

    $(document).ready(function(){
        $('.deliveryfrom_cancel_label').click(
            function(){
                deliveryfrom_cancel_label(this);
            }
        );
    })

    function deliveryfrom_cancel_label(el){
        $.blockUI({
            baseZ: 99999,
            message: null,
            overlayCSS:  { 
                backgroundColor: '#fff', 
                opacity:         0.6, 
                cursor:          'wait' 
            },
        })

        var order_id = $(el).data('order-id');
        var method = $(el).data('method');
        var instance = $(el).data('instance');

        var data = {
            'action': 'deliveryfrom_cancel_label',
            'post': order_id,
            'method': method,
            'instance': instance
        };

        $.post(ajaxurl, data, function(response) {

            var format_response = JSON.parse(response);
            if(format_response.status == 'ok'){

                $('.blockOverlay').addClass('deliveryfrom_success');
                $('.blockOverlay').append('<div class="deliveryfrom_tick_wrap"><svg version="1.1" id="delivery_tick" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 37 37" style="enable-background:new 0 0 37 37;" xml:space="preserve"><path class="circ path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" d="M30.5,6.5L30.5,6.5c6.6,6.6,6.6,17.4,0,24l0,0c-6.6,6.6-17.4,6.6-24,0l0,0c-6.6-6.6-6.6-17.4,0-24l0,0C13.1-0.2,23.9-0.2,30.5,6.5z"/><polyline class="tick path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" points="11.6,20 15.9,24.2 26.4,13.8 "/></svg></div>');
                $('#delivery_tick').addClass('drawn');

                $('#post-' + format_response.removed + ' .deliveryfrom_wc_action, #order-' + format_response.removed + ' .deliveryfrom_wc_action').remove();
                $('#post-' + format_response.removed + ' .wc_actions > p, #order-' + format_response.removed + ' .wc_actions > p').append(format_response.buttons.replaceAll('{{order_id}}', format_response.removed));

                $('.deliveryfrom_actions.single-order .deliveryfrom_wc_action').remove();
                $('.deliveryfrom_actions.single-order').append(format_response.buttons.replaceAll('{{order_id}}', format_response.removed));

                $('.deliveryfrom_print').off('click');
                $('.deliveryfrom_print').click(
                    function(){
                        deliveryfrom_print_single(this);
                    }
                )

                $.unblockUI();

            }else{
                if(format_response.error.length > 0){

                    $.blockUI({
                        baseZ: 99999,
                        blockMsgClass: 'deliveryfrom_blockui_message',
                        message: '<div id="deliveryfrom_error"><div>' + format_response.error + '</div><a class="button df-blockui-close">' + format_response.close + '</a></div>',
                        overlayCSS:  { 
                            backgroundColor: '#fff', 
                            opacity:         0.6,
                            cursor: 'default'
                        },
                    });

                    $('.blockOverlay').addClass('deliveryfrom_hide_spinner');

                    $('.df-blockui-close').off('click');
                    $('.df-blockui-close').click(function(){
                        $.unblockUI();
                    });

                }
            }
        });
    }

    function deliveryfrom_form_print(el){

        var order_id = $(el).data('order-id');
        var method = $(el).data('method');
        var instance = $(el).data('instance');

        var object = {};
        var formData = new FormData(document.getElementById('df_form_print'));

        formData.forEach((value, key) => {
            // Reflect.has in favor of: object.hasOwnProperty(key)
            if(!Reflect.has(object, key)){
                object[key] = value;
                return;
            }
            if(!Array.isArray(object[key])){
                object[key] = [object[key]];    
            }
            object[key].push(value);
        });
        var form = JSON.stringify(object);

        $.blockUI({
            baseZ: 99999,
            message: null,
            overlayCSS:  { 
                backgroundColor: '#fff', 
                opacity:         0.6, 
                cursor:          'wait' 
            },
        })

        var data = {
            'action': 'deliveryfrom_form_print',
            'post': order_id,
            'method': method,
            'instance': instance,
            'form': form,
        };


        $.post(ajaxurl, data, function(response) {
            var format_response = JSON.parse(response);
            if(format_response.status == 'ok'){

                $('.blockOverlay').addClass('deliveryfrom_success');
                $('.blockOverlay').append('<div class="deliveryfrom_tick_wrap"><svg version="1.1" id="delivery_tick" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 37 37" style="enable-background:new 0 0 37 37;" xml:space="preserve"><path class="circ path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" d="M30.5,6.5L30.5,6.5c6.6,6.6,6.6,17.4,0,24l0,0c-6.6,6.6-17.4,6.6-24,0l0,0c-6.6-6.6-6.6-17.4,0-24l0,0C13.1-0.2,23.9-0.2,30.5,6.5z"/><polyline class="tick path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" points="11.6,20 15.9,24.2 26.4,13.8 "/></svg></div>');
                $('#delivery_tick').addClass('drawn');
                
                if(format_response.single){
                    setTimeout(() => {
                        window.open(format_response.url, "_blank");
                    }, 500);
                }
                $.unblockUI();

                format_response.printed.forEach(post => {
                    $('#post-' + post + ' .deliveryfrom_wc_action, #order-' + post + ' .deliveryfrom_wc_action').remove();
                    $('#post-' + post + ' .wc_actions > p, #order-' + post + ' .wc_actions > p').append(format_response.buttons.replaceAll('{{order_id}}', post));

                    $('.deliveryfrom_actions.single-order .deliveryfrom_wc_action').remove();
                    $('.deliveryfrom_actions.single-order').append(format_response.buttons.replaceAll('{{order_id}}', post));
                });

                $('.deliveryfrom_cancel_label').off('click');
                $('.deliveryfrom_cancel_label').click(
                    function(){
                        deliveryfrom_cancel_label(this);
                    }
                )

            }else{
                if(format_response.error.length > 0){

                    $.blockUI({
                        baseZ: 99999,
                        blockMsgClass: 'deliveryfrom_blockui_message',
                        message: '<div id="deliveryfrom_error"><div>' + format_response.error + '</div><a class="button df-blockui-close">' + format_response.close + '</a></div>',
                        overlayCSS:  { 
                            backgroundColor: '#fff', 
                            opacity:         0.6,
                            cursor: 'default'
                        },
                    });

                    $('.blockOverlay').addClass('deliveryfrom_hide_spinner');

                    $('.df-blockui-close').off('click');
                    $('.df-blockui-close').click(function(){
                        $.unblockUI();
                    });

                }
            }
        });
    }

    function deliveryfrom_print_bulk(orders, method){

        $.blockUI({
            baseZ: 99999,
            message: null,
            overlayCSS:  { 
                backgroundColor: '#fff', 
                opacity:         0.6, 
                cursor:          'wait' 
            },
        })

        var data = {
            'action': 'deliveryfrom_bulk_print',
            'orders': orders,
            'method': method,
        };

        $.post(ajaxurl, data, function(response) {
            var format_response = JSON.parse(response);
            if(format_response.status == 'ok'){

                $('.blockOverlay').addClass('deliveryfrom_success');
                $('.blockOverlay').append('<div class="deliveryfrom_tick_wrap"><svg version="1.1" id="delivery_tick" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 37 37" style="enable-background:new 0 0 37 37;" xml:space="preserve"><path class="circ path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" d="M30.5,6.5L30.5,6.5c6.6,6.6,6.6,17.4,0,24l0,0c-6.6,6.6-17.4,6.6-24,0l0,0c-6.6-6.6-6.6-17.4,0-24l0,0C13.1-0.2,23.9-0.2,30.5,6.5z"/><polyline class="tick path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" points="11.6,20 15.9,24.2 26.4,13.8 "/></svg></div>');
                $('#delivery_tick').addClass('drawn');
                
                if(format_response.single){
                    setTimeout(() => {
                        window.open(format_response.url, "_blank");
                    }, 500);
                }
                $.unblockUI();

                format_response.printed.forEach(post => {
                    $('#post-' + post + ' .deliveryfrom_wc_action, #order-' + post + ' .deliveryfrom_wc_action').remove();
                    $('#post-' + post + ' .wc_actions > p, #order-' + post + ' .wc_actions > p').append(format_response.buttons.replaceAll('{{order_id}}', post));
                });

                $('.deliveryfrom_cancel_label').off('click');
                $('.deliveryfrom_cancel_label').click(
                    function(){
                        deliveryfrom_cancel_label(this);
                    }
                )

            }else{
                if(format_response.error.length > 0){

                    $.blockUI({
                        baseZ: 99999,
                        blockMsgClass: 'deliveryfrom_blockui_message',
                        message: '<div id="deliveryfrom_error"><div>' + format_response.error + '</div><a class="button df-blockui-close">' + format_response.close + '</a></div>',
                        overlayCSS:  { 
                            backgroundColor: '#fff',
                            opacity:         0.6,
                            cursor: 'default'
                        },
                    });

                    $('.blockOverlay').addClass('deliveryfrom_hide_spinner');

                    $('.df-blockui-close').off('click');
                    $('.df-blockui-close').click(function(){
                        $.unblockUI();
                    });

                }
            }
        });
    }

    function deliveryfrom_print_single(el){
        $.blockUI({
            baseZ: 99999,
            message: null,
            overlayCSS:  { 
                backgroundColor: '#fff', 
                opacity:         0.6, 
                cursor:          'wait' 
            },
        })

        var order_id = $(el).data('order-id');
        var method = $(el).data('method');
        var instance = $(el).data('instance');

        var data = {
            'action': 'deliveryfrom_print',
            'post': order_id,
            'method': method,
            'instance': instance
        };

        $.post(ajaxurl, data, function(response) {
            //(response);
            var format_response = JSON.parse(response);
            if(format_response.status == 'ok'){

                $('.blockOverlay').addClass('deliveryfrom_success');
                $('.blockOverlay').append('<div class="deliveryfrom_tick_wrap"><svg version="1.1" id="delivery_tick" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 37 37" style="enable-background:new 0 0 37 37;" xml:space="preserve"><path class="circ path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" d="M30.5,6.5L30.5,6.5c6.6,6.6,6.6,17.4,0,24l0,0c-6.6,6.6-17.4,6.6-24,0l0,0c-6.6-6.6-6.6-17.4,0-24l0,0C13.1-0.2,23.9-0.2,30.5,6.5z"/><polyline class="tick path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" points="11.6,20 15.9,24.2 26.4,13.8 "/></svg></div>');
                $('#delivery_tick').addClass('drawn');
                
                if(format_response.single){
                    setTimeout(() => {
                        window.open(format_response.url, "_blank");
                    }, 500);
                }
                $.unblockUI();
                
                format_response.printed.forEach(post => {
                    $('#post-' + post + ' .deliveryfrom_wc_action, #order-' + post + ' .deliveryfrom_wc_action').remove();
                    $('#post-' + post + ' .wc_actions > p, #order-' + post + ' .wc_actions > p').append(format_response.buttons.replaceAll('{{order_id}}', post));
                    
                    $('.deliveryfrom_actions.single-order .deliveryfrom_wc_action').remove();
                    $('.deliveryfrom_actions.single-order').append(format_response.buttons.replaceAll('{{order_id}}', post));
                });

                $('.deliveryfrom_cancel_label').off('click');
                $('.deliveryfrom_cancel_label').click(
                    function(){
                        deliveryfrom_cancel_label(this);
                    }
                )

            }else if(format_response.status == 'form'){

                $.blockUI({
                    baseZ: 99999,
                    blockMsgClass: 'deliveryfrom_blockui_form',
                    message: format_response.html,
                    overlayCSS:  { 
                        backgroundColor: '#fff', 
                        opacity:         0.6,
                        cursor: 'default'
                    },
                    focusInput: false,
                    
                });

                $('.blockOverlay').addClass('deliveryfrom_hide_spinner');

                $('.datepicker').datepicker({dateFormat:'yy-mm-dd',minDate: 0});

                $('.deliveryfrom_form_print').off('click');
                $('.deliveryfrom_form_print').click(function(){
                    deliveryfrom_form_print(el);
                });

                $('.deliveryfrom_form_save').off('click');
                $('.deliveryfrom_form_save').click(function(){
                    deliveryfrom_form_save(el);
                });

                $('.df-blockui-close').off('click');
                $('.df-blockui-close').click(function(){
                    $.unblockUI();
                });
            }else{
                if(format_response.error.length > 0){

                    $.blockUI({
                        baseZ: 99999,
                        blockMsgClass: 'deliveryfrom_blockui_message',
                        message: '<div id="deliveryfrom_error"><div>' + format_response.error + '</div><a class="button df-blockui-close">' + format_response.close + '</a></div>',
                        overlayCSS:  { 
                            backgroundColor: '#fff', 
                            opacity: 0.6,
                            cursor: 'default'
                        },
                    });

                    $('.blockOverlay').addClass('deliveryfrom_hide_spinner');

                    $('.df-blockui-close').off('click');
                    $('.df-blockui-close').click(function(){
                        $.unblockUI();
                    });

                }
                
                //alert(response);
            }
            
        });
    }

    function deliveryfrom_form_save(el){

        var order_id = $(el).data('order-id');

        var object = {};
        var formData = new FormData(document.getElementById('df_form_print'));

        formData.forEach((value, key) => {
            // Reflect.has in favor of: object.hasOwnProperty(key)
            if(!Reflect.has(object, key)){
                object[key] = value;
                return;
            }
            if(!Array.isArray(object[key])){
                object[key] = [object[key]];    
            }
            object[key].push(value);
        });
        var form = JSON.stringify(object);

        $.blockUI({
            baseZ: 99999,
            message: null,
            overlayCSS:  { 
                backgroundColor: '#fff', 
                opacity:         0.6, 
                cursor:          'wait' 
            },
        })

        var data = {
            'action': 'deliveryfrom_form_save',
            'post': order_id,
            'form': form,
        };

        $.post(ajaxurl, data, function(response) {  
            var format_response = JSON.parse(response);
            if(format_response.status == 'ok'){

                $('.blockOverlay').addClass('deliveryfrom_success');
                $('.blockOverlay').append('<div class="deliveryfrom_tick_wrap"><svg version="1.1" id="delivery_tick" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 37 37" style="enable-background:new 0 0 37 37;" xml:space="preserve"><path class="circ path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" d="M30.5,6.5L30.5,6.5c6.6,6.6,6.6,17.4,0,24l0,0c-6.6,6.6-17.4,6.6-24,0l0,0c-6.6-6.6-6.6-17.4,0-24l0,0C13.1-0.2,23.9-0.2,30.5,6.5z"/><polyline class="tick path" style="fill:none;stroke-width:3;stroke-linejoin:round;stroke-miterlimit:10;" points="11.6,20 15.9,24.2 26.4,13.8 "/></svg></div>');
                $('#delivery_tick').addClass('drawn');               

            }
            setTimeout(() => {
                $.unblockUI();
            }, 500);
        });
    }

})( jQuery );
