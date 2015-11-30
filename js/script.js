/* ==========================================================
 * ICLOUD
 * SEARCH VALIDATION
 * LINKEDIN.COM/IN/LUCIANOWORK
 * CREATE BY LUCIANO OLIVEIRA
 * ========================================================== */

//FORM LOGIN
$(function() {

	var playback,startTime,endTime,timelineData,timeline;
	var arrayData = [];
    var jsonData = false;
    var colorCount = 0;

    //Load Devices
    loadDevices();

	$('#datetime').modal('show');

    var mbUrl = 'https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6IjZjNmRjNzk3ZmE2MTcwOTEwMGY0MzU3YjUzOWFmNWZhIn0.Y8bhBaUMqFiPrDRW9hieoQ';

    var grayscale   = L.tileLayer(mbUrl, {id: 'mapbox.light'}),
        streets  = L.tileLayer(mbUrl, {id: 'mapbox.streets'}),
        satellite  = L.tileLayer(mbUrl, {id: 'mapbox.streets-satellite'});


    var map = L.map('map', {
        center: [-23.0316,-46.9856],
        zoom: 7,
        layers: [grayscale,]
    });

    var baseLayers = {
        "Grayscale": grayscale,
        "Streets": streets,
        "Satellite": satellite
    };

    // Colors for AwesomeMarkers
    colors = ['#2ecc71','#2196f3','#1abc9c','#9b59b6','#e67e22','#e74c3c','#16a085','#27ae60','#2980b9','#8e44ad'];

    L.control.layers(baseLayers).addTo(map);

    // Set timeline options
	var timelineOptions = {
		width:  "100%",
		height: "200px",
		style: "box",
		axisOnTop: true,
		showCustomTime:true,
		locales: {
          en: {
              current: 'current',
              time: 'time',
            },
          pt_br: {
              current: 'current',
              time: 'time',
            }
      	}
	};

    // A callback so timeline is set after changing playback time
    function onPlaybackTimeChange (ms) {
        timeline.setCustomTime(new Date(ms));
    };

    function onCustomTimeChange(properties) {
        if (!playback.isPlaying()) {
            playback.setCursor(properties.time.getTime());
        }
    }

    // Playback options
    var playbackOptions = {

        playControl: true,
        dateControl: true,

        // layer and marker options
        layer: {
            pointToLayer : function(featureData, latlng){

                if (featureData && featureData.properties && featureData.properties.color) {
                    var selecColor = featureData.properties.color;
                }

                var geojsonMarkerOptions = {
				    radius: 4,
				    fillColor: selecColor,
				    color: selecColor,
				    weight: 1,
				    opacity: 1,
				    fillOpacity: 0.8
				};
                return new L.CircleMarker(latlng, geojsonMarkerOptions);
            }
        },

        marker: function (featureData) {

            return {
                icon: L.AwesomeMarkers.icon({
                    icon: 'user',
                    iprefix: 'fa',
                    markerColor: colors
                }),

                getPopup: function(featureData) {
                    var result = '';
                    if (featureData && featureData.properties) {
                        result = "Nome: " + featureData.properties.name + "<br/>Status: " +
                        featureData.properties.status + "<br/>Modelo: " +
                        featureData.properties.model + "<br/>Bateria: " +
                        featureData.properties.battery_level + "<br/>Status Bateria: " +
                        featureData.properties.battery_status;
                    }
                    return result;
                }
            };
        }
    };

    timelineData = new vis.DataSet(timelineOptions);
    timelineData.add(arrayData);
    // Setup timeline
    timeline = new vis.Timeline(document.getElementById('timeline'), timelineData, timelineOptions);
    playback = new L.Playback(map, null, onPlaybackTimeChange, playbackOptions);
    timeline.setOptions({
      locale: 'pt_br'
    });

    timeline.on('timechange', onCustomTimeChange);

    $("#rastrear").submit(function(e) {
        e.preventDefault();
    }).validate({

        rules: {
            dateStart: {
                required: true,
            },
            dateFinish: {
                required: true,
            },
            devices: {
                required: true,
            }
        },

        ignore: ':hidden:not(".multiselect")',

        messages: {
            dateStart: {
                required: "Escolha a data inicial",
            },
            dateFinish: {
                required: "Escolha a data final",
            },
            devices: {
                required: "Escolha um device",
            }
        },

        submitHandler: function(){
            var form = $("#rastrear").serialize();
            $('#loader').show();

            $.ajax({
                url: "php/geoJson.php",
                dataType: "json",
                method: "POST",
                data: form
            }).done(function(data){



                if(data.length > 0){

                    $('#datetime').modal('hide');
                    $('#loader').hide();

                    var countData = timelineData.length;
                    for (var i = 0; i <= countData -1; i++) {
                        timelineData.remove(i);
                    };

                    // Get start
                    startTime = new Date(data[0].properties.time[0]);

                    for (var i = 0; i <= data.length -1; i++) {

                        start = new Date(data[i].properties.time[0]);
                        end = new Date(data[i].properties.time[data[i].properties.time.length - 1]);
                        name = data[i].properties.name;

                        if (colorCount > colors.length -1) {
                            colorCount = 0;
                        }

                        arrayData.push({
                            id: i,
                            start: start ,
                            end: end,
                            content: name , 
                            group: 1,
                            className: 'iten'+colorCount+''
                        });

                        colorCount ++;

                    };

                    timelineData.add(arrayData);
                    

                    // Set custom time marker (blue)
                    timeline.setCustomTime(startTime);
                    playback.setData(data);

                    arrayData = [];

                }else{
                    $('#loader').hide();
                    swal({
                        title: "Ops!",
                        text: "Infelizmente não conseguimos encontrar o que você estava procurando :(",
                        confirmButtonText: "Ok",
                        confirmButtonColor: "#2196f3"
                    });
                };

                colorCount = 0;
                countColor = 0;
            });
        }
    });

    function loadDevices() {
        //GET DEVICES
        $.ajax({
            url: "php/getDevices.php",
            dataType: "json",
            method: "POST",
        }).done(function(data){
            if(data) {
                data.forEach(function(device) {
                    $("#devices").append('<option value="'+device.device_id+'">'+device.device_name+'</option>');
                });

                $('.multiselect').multiselect({
                    includeSelectAllOption: true,
                    dropUp: false,
                    checkboxName: 'devices[]'
                });
            }
        });
    }

});

