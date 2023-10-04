var googleUser = {};
var auth2 = '';
var loader_html = '<div class="wt-preloader-section"><div class="wt-preloader-holder"><div class="wt-loader"></div></div></div>';
var workreap_gconnect_app = function() {
    function decodeJwtResponse(token) {
        var base64Url = token.split('.')[1];
        var base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
        var jsonPayload = decodeURIComponent(atob(base64).split('').map(function (c) {
            return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
        }).join(''));
    
        return JSON.parse(jsonPayload);
    }
    /* Google sigin callback */
    function handleCredentialResponse(response) {
            const responsePayload = decodeJwtResponse(response.credential);
            jQuery('body').append(loader_html);
            let login_type = 'google';
            let picture = responsePayload.picture;
            let email = responsePayload.email;
            let id = responsePayload.sub;
            let name = responsePayload.name;
    
            jQuery('body').append(loader_html);
    
            var dataString = 'security='+scripts_vars.ajax_nonce+'&login_type=google&'+login_type + picture+'&email=' + email +'&id=' + id + '&name=' + name + '&action=workreap_js_social_login';
    
            jQuery.ajax({
                type: "POST",
                url: scripts_vars.ajaxurl,
                data: dataString,
                dataType: "json",
                success: function (response) {
                    jQuery('body').find('.wt-preloader-section').remove();
                    if (response.type === 'success') {  
                        jQuery('#loginpopup').modal('hide');
                        
                        jQuery.sticky(response.message, {classList: 'success', speed: 200, autoclose: 5000 });
                        window.location.replace(response.redirect);
                    } else {
                        jQuery.sticky(response.message, {classList: 'important', speed: 200, autoclose: 5000});
                    }
                }
            });
    
    }
	/* Google sigin button load */
    window.onload = function () {
        google.accounts.id.initialize({
            client_id: scripts_vars.gclient_id,
            ux_mode: 'popup',
            cancel_on_tap_outside: false,
            callback: handleCredentialResponse
        });
        google.accounts.id.renderButton(
            document.getElementById("wt-gconnect"),
            {
                type: 'icon',
                theme: "outline",
                size: "large",
                logo_alignment: 'left',
                shape: 'rectangular',
            },
        );
        google.accounts.id.renderButton(
            document.getElementById("wt-gconnect-reg"),
            {
                type: 'icon',
                theme: "outline",
                size: "large",
                logo_alignment: 'left',
                shape: 'rectangular',
            },
        );
        google.accounts.id.prompt();
    };
  };
