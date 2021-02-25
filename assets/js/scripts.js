jQuery(document).ready(function($){

    $("#submitLoginFormKB").attr( "disabled", "disabled" );

    $( "#login_pass_kbplugin" ).blur(function() {
        var password = $( "#login_pass_kbplugin" ).val();
        if (!password){
            $("#submitLoginFormKB").attr( "disabled", "disabled" );
        } else {
            $("#submitLoginFormKB").removeAttr( "disabled" ); 
        }
    });
    
    $( "#submitLoginFormKB" ).click(function() {

        var emailaddress = $( "#login_email_kbplugin" ).val();
        var password = $( "#login_pass_kbplugin" ).val();
        var honey = $( "#checkerkb" ).val();
        var ipAddress = $( "#ipAddress" ).val();

        if (!honey){
            jQuery.ajax( {
                'type': "GET",
                'url': '/wp-json/ip/block/endpoint/',
                'data': {
                    'ipAddress': ipAddress,
                    'emailaddress': emailaddress,
                    'ressetcounter' : 'false',
                },
                success: function(data){
                    if (data == '1'){
                        $( "#locked" ).removeClass('d-none');
                    } else {
                        jQuery.ajax({
                            'type': "GET",
                            'url': '/wp-json/login/api/endpoint/',
                            'data': {
                                'emailaddress': emailaddress,
                                'password': btoa(password),
                            },
                            success: function(data){
                                if (data.token){
                                    $( "#passworng" ).addClass('d-none');
                                    $( "#success" ).removeClass('d-none');
                                    jQuery.ajax({
                                        'type': "GET",
                                        'url': '/wp-json/ip/block/endpoint/',
                                        'data': {
                                            'ipAddress': ipAddress,
                                            'emailaddress': emailaddress,
                                            'ressetcounter' : 'true',
                                        },
                                        success: function(data){
                                            if (data == '1'){
                                                $( "#locked" ).removeClass('d-none');
                                            } else {
                                                $( "#locked" ).addClass('d-none');
                                            }
                                            return data;
                                        },
                                        error: function(data){
                                            console.log("error - ", arguments);
                                            console.log(data);
                                        },
                                    });
                                } else if (data.error){
                                    $( "#passworng" ).removeClass('d-none');
                                    $( "#success" ).addClass('d-none');
                                    $( "#locked" ).addClass('d-none');
                                    //ip block database fill
                                    jQuery.ajax({
                                        'type': "GET",
                                        'url': '/wp-json/ip/block/endpoint/',
                                        'data': {
                                            'ipAddress': ipAddress,
                                            'emailaddress': emailaddress,
                                            'ressetcounter' : 'false',
                                        },
                                        success: function(data){
                                            if (data == '1'){
                                                $( "#locked" ).removeClass('d-none');
                                            } else { 
                                                $( "#locked" ).addClass('d-none');
                                            }
                                            return data;
                                        },
                                        error: function(data){
                                            console.log("error - ", arguments);
                                            console.log(data);
                                        },
                                    }); 
                                }
                                return data;
                            },
                            error: function(data) {
                                console.log("error - ", arguments);
                                console.log(data);
                            },
                        });
                    }
                    return data;
                },
                error: function(data) {
                    console.log("error - ", arguments);
                    console.log(data);
                },
            }); 
        } else {
            $( "#notHuman" ).removeClass('d-none');
        }
    });
});
