(function( $ ) {
	'use strict';

	$(document).ready(function(){

        $('#deliveryfrom-pickuppoint').selectWoo();

        $('#deliveryfrom-pickuppoint').change(function(){
            match_select_to_map();
        });

        let is_parcelshop = is_parcelshop_shipping();
        if(is_parcelshop){
            parcelshop_display();
        }
        $('body').on('updated_checkout', function(){
            is_parcelshop = is_parcelshop_shipping();
            if(is_parcelshop){
                parcelshop_display();
            }
        });

    });
    
    function is_parcelshop_shipping(){
        let selected = $('#shipping_method li input:checked').val() || $('#shipping_method li input').first().val();

        if(selected === undefined ){
            $('#ship-to-different-address').show();
            $('#deliveryfrom-pickuppoint_field').hide();
            return false;
        }

        var pickup = '';
        for (pickup of deliveryfrompickup) {

            if(selected.indexOf(pickup) >= 0 ){
                $('#ship-to-different-address').hide();
                $('#deliveryfrom-pickuppoint_field').show();
                return true;
            }
        }

        $('#ship-to-different-address').show();
        $('#deliveryfrom-pickuppoint_field').hide();
        return false;
        
    }

    function parcelshop_display(){

        $('#ship-to-different-address-checkbox').prop('checked', false);
        $('.shipping_address').hide();

        var country = $('#billing_country').val() || '';
        var shipping = $('#shipping_method li input:checked').val() || $('#shipping_method li input').first().val();

        var data = {
            'action': 'deliveryfrom_update_pickuppoints_checkout',
            'country': country,
            'shipping': shipping
        };

        $.post(wc_checkout_params.ajax_url , data, function(response) {
            var lockers = response.options;

            var $el = $("#deliveryfrom-pickuppoint");
            $el.empty();
            $.each(lockers, function(key,value) {
            $el.append(
                $("<option></option>").attr("value", key).text(value));
            });

            $el.val('');

            $el.selectWoo();

            if(typeof google == 'undefined'){
                return;
            }

            $('#deliveryfrom_map').remove();
            $('#deliveryfrom-pickuppoint_field > label').after('<div id="deliveryfrom_map"></div>');

            initMap(response.zoom, response.lat, response.lng, response.markers, response.icon, response.clusterer, response.textColor);

        });
    }

    function match_select_to_map(){
        if(window.infowindow != '' && typeof infowindow != 'undefined'){
            infowindow.close();

            var id = $('#deliveryfrom-pickuppoint').val();
            var pin = markers[id];

            var lng = parseFloat(pin.lng);
            var lat = parseFloat(pin.lat);
            var position = new google.maps.LatLng(lat, lng);
            window.map.setCenter(position);

            var zoom = window.map.getZoom();
            window.map.setZoom(zoom);

            var marker = new google.maps.Marker({
                position: position                                
            });

            var offset = new google.maps.Size(0, -32);
            
            infowindow.setContent(pin.html);
            infowindow.setOptions({pixelOffset: offset});
            infowindow.setPosition(position);
            infowindow.open(window.map);
        }    
    }
    

    function initMap(zoom, lat, lng, LocationsForMap, icon, clusterer, textColor) {
        var myLatLng = { lat: lat, lng: lng };
        const map = new google.maps.Map(document.getElementById("deliveryfrom_map"), {
            zoom: zoom,
            center: myLatLng,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        });

        window.infowindow = new google.maps.InfoWindow();

        var marker;

        const markers = new Array();

        for (const i in LocationsForMap) {
            
            marker = new google.maps.Marker({
                position: new google.maps.LatLng(parseFloat(LocationsForMap[i]['lat']), parseFloat(LocationsForMap[i]['lng']) ),
                icon: icon,
                map: map               
            });

            google.maps.event.addListener(marker, 'click', (function(marker, i) {
                return function() {
                    infowindow.close();
                    infowindow.setOptions({pixelOffset: null});
                    
                    infowindow.setContent(
                        LocationsForMap[i]['html']
                    );
                    infowindow.open(map, marker);
                    
                    $('#deliveryfrom-pickuppoint').val(i).trigger('change');
                }
            })(marker, i));

            markers.push(marker);
        }

        const renderer = {
            render({ count, position }, stats) {

                return new google.maps.Marker({
                    position,
                    icon: {
                        url: clusterer,
                        scaledSize: new google.maps.Size(45, 45),
                    },
                    label: {
                        text: String(count),
                        color: textColor,
                        fontSize: "16px",
                    },
                    // adjust zIndex to be above other markers
                    zIndex: Number(google.maps.Marker.MAX_ZINDEX) + count,
                    noClustererRedraw: true,
                    maxZoom: 16,
                });
            }
        };

        new markerClusterer.MarkerClusterer({
            map: map,
            markers: markers,
            renderer: renderer
        });

        google.maps.event.addListener(map, "click", function(event) {
            infowindow.close();
        });

        window.map = map;
        window.markers = LocationsForMap;
    }    

})( jQuery );