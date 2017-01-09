
// Admin config
function knews_conf(w) {
	if (w=='gmail') {
		user='youremail@gmail.com';
		host='smtp.gmail.com';
		port='465';
		secure='ssl';
		comnn='0';
	} else if (w=='1and1') {
		user='';
		host='';
		port='';
		secure='';
		comnn='1';
	} else if (w=='godaddy') {
		user='your@email.com';
		host='relay-hosting.secureserver.net';
		port='25';
		secure='';
		comnn='0';
	} else if (w=='yahoo') {
		user='youryahooname';
		host='smtp.mail.yahoo.com';
		port='465';
		secure='ssl';
		comnn='0';
	}
	jQuery('#smtp_host_knews').val(host);
	jQuery('#smtp_port_knews').val(port);
	jQuery('#smtp_user_knews').val(user);
	jQuery('#smtp_secure_knews').val(secure);
	jQuery('#is_sendmail_knews').val(comnn);			
}

function view_lang(n_custom, n_lang) {
	jQuery('div.pestanyes_'+n_custom+' a').removeClass('on');
	jQuery('a.link_'+n_custom+'_'+n_lang).addClass('on');

	target='div.pregunta_'+n_custom+' textarea.on';
	save_height=jQuery(target).innerHeight() + parseInt(jQuery(target).css('marginTop'), 10) + parseInt(jQuery(target).css('marginBottom'), 10);
	
	save_width=jQuery(target).innerWidth() + parseInt(jQuery(target).css('marginLeft'), 10) + parseInt(jQuery(target).css('marginRight'), 10);
		
	jQuery('div.pregunta_'+n_custom+' textarea').css('display','none').removeClass('on');
	jQuery('textarea.custom_lang_'+n_custom+'_'+n_lang).css({display:'block', height:save_height, width:save_width}).addClass('on');
}	

// Cooltabs
jQuery(document).ready(function() {
	jQuery('div.knews_cooltabs a').click(function() {
		jQuery('div.knews_cooltabs a').removeClass('active');
		jQuery(this).addClass('active');
		n = jQuery('div.knews_cooltabs a').index(this);
		jQuery('div.tabbed_content').hide();
		jQuery('div.tabbed_content').eq(n).show();
		jQuery('#subtab').val(n+1);
		return false;
	});
});

// Custom checkboxes
jQuery.fn.moveBackgroundX = function( pixelsX, duration ) {
	pixelsY = jQuery(this).css('backgroundPosition');
	pixelsY = parseInt( pixelsY.split(' ')[1], 10);
	return this.animate( { pixelsX: pixelsX }, { step: function(now,fx) {
		jQuery(this).css({ backgroundPosition: now + 'px ' + pixelsY + 'px' });
	}, duration: duration, complete: function() {} }, 'swing');
};

jQuery(document).ready(function() {
	jQuery('input.knews_on_off, input.knews_open_close').each(function () {
		current_class='knews_on_off'; offsetX=50;
		if (jQuery(this).hasClass('knews_open_close')) { current_class='knews_open_close'; offsetX=30; }
		extraclass=''; if (jQuery(this).hasClass('align_left')) extraclass=current_class + '_left';
		jQuery(this).before('<span class="' + current_class + ' ' + extraclass + '">&nbsp;</span>');
		jQuery(this).addClass('knews_processed');
		next_label = jQuery(this).next().addClass('knews_processed ' + extraclass);
		if (!jQuery(this).is(':checked')) jQuery(this).prev().moveBackgroundX(-1 * offsetX, 0);
		jQuery(this).prev().click(function() {
			offsetX=50; if (jQuery(this).hasClass('knews_open_close')) offsetX=30;
			state = !jQuery(this).next().prop('checked');
			jQuery(this).next().prop('checked', state);
			if (state) {
				jQuery(this).stop().moveBackgroundX(0, 500);
			} else {
				jQuery(this).stop().moveBackgroundX(-1 * offsetX, 500);
			}
		});
	});
	jQuery('select[name=emails_at_once]')
		.change(function() {
			jQuery('span.at_once_preview').html(jQuery(this).val() * 6);
		})
		.each(function () {
			jQuery('span.at_once_preview').html(jQuery(this).val() * 6);
		});
});

jQuery(document).ready(function() {
	jQuery('form.new_newsletter').submit(function () {
		//alert(jQuery('
	});
});

function enfocar_knews(que) {
	setTimeout("jQuery('" + que + "').focus();", 100);
}
/* new newsletter */
jQuery(document).ready(function() {
	jQuery('form.new_newsletter').submit(function() {
		if (jQuery('#new_news').val()=='') {
			jQuery('#new_news').focus();
			jQuery('html, body').animate({scrollTop: parseInt(jQuery('#new_news').offset().top, 10)-100}, 1000);
			return false;
		}
	});
	jQuery('form.new_newsletter div.template a').click( function() {
		jQuery('html, body').animate({scrollTop: parseInt(jQuery('input[name="newstype"]').offset().top, 10)-200}, 1000, function() {
			jQuery('div.skin_opts').animate({'opacity': '1'});
		});
		update_skins();
	});

	jQuery('form.new_newsletter input[name="template"]').change( function() {
		//alert(jQuery('input[name="newstype"]')[0].offset().top);
		jQuery('html, body').animate({scrollTop: parseInt(jQuery('input[name="newstype"]').offset().top, 10)-200}, 1000, function() {
			jQuery('div.skin_opts').animate({'opacity': '1'});
		});
		update_skins();
	});
	
});

function update_skins() {
	template = jQuery('input[name="template"]:checked').val();
	jQuery('div.skin_opts').html(jQuery('div.skin_opts_' + template).html() ).css('opacity', '0.01');
}

/* manual news submit */
jQuery(document).ready(function() {
	jQuery('form#knewsFormSendManually').submit(function() {
		if (!knews_checkmail ( jQuery('input[name="email"]', this).val() ) ) {
			jQuery('input[name="email"]', this).focus();
			return false;
		}
	});
});

function knews_checkmail(email) {
	return /^[A-Za-z][A-Za-z0-9_\.-]*@[A-Za-z0-9_\.-]+\.[A-Za-z0-9_\.-]+[A-za-z]$/.test(email);
}
/* automation creation */
jQuery(document).ready(function() {
	jQuery('input[name="knews_get_cpt"]').click(function () {
		if(jQuery(this).is(":checked")) {
			jQuery('div.knews_radios_cat, div.knews_radios_tags').fadeOut();
			jQuery('div.knews_hidden_cpt').fadeIn();
		} else {
			jQuery('div.knews_radios_cat, div.knews_radios_tags').fadeIn();
			jQuery('div.knews_hidden_cpt').fadeOut();
		}
	});
	jQuery('input[name="knews_get_categories"]').click(function () {
		if (jQuery(this).val()=='all') {
			jQuery('div.knews_hidden_cat').fadeOut();
		} else {
			jQuery('div.knews_hidden_cat').fadeIn();			
		}
	});
	jQuery('input[name="knews_get_tags"]').click(function () {
		if (jQuery(this).val()=='all') {
			jQuery('div.knews_hidden_tags').fadeOut();
		} else {
			jQuery('div.knews_hidden_tags').fadeIn();			
		}
	});
	jQuery('#auto_lang').change(function() {
		lang = jQuery(this).val();
		
		jQuery('div.knews_hidden_cat select').empty();
		for (var x=0; x<knews_cats_tags['cats'][lang].length; x++) {
			option = jQuery('<option></option>').attr("value", knews_cats_tags['cats'][lang][x][0]).text(knews_cats_tags['cats'][lang][x][1]);
			jQuery('div.knews_hidden_cat select').append(option);
		}
		
		jQuery('div.knews_hidden_tags select').empty();
		for (var x=0; x<knews_cats_tags['tags'][lang].length; x++) {
			option = jQuery('<option></option>').attr("value", knews_cats_tags['tags'][lang][x][0]).text(knews_cats_tags['tags'][lang][x][1]);
			jQuery('div.knews_hidden_tags select').append(option);
		}
		
	});
	jQuery('a.knews_alert_click').click(function() {
		alert(jQuery(this).attr('title'));
		return false;
	});
	jQuery('a.knews_blacklist').click(function() {
		jQuery('div.knews_blacklist').toggle();
		return false;
	});
});
