var current_editor='';
var current_range='';
var current_node='';
var inside_editor=false;
var can_justify=false;
var justify_node='';
var im_on_link=false;
var link_node='';
var moz_dirty=false;
var bold_type='';
var cursive_type='';

//function start() {
parent.jQuery(document).ready( function () {
//parent.jQuery(window).load(function() {

	parent.jQuery('.droppable_empty', document)
		.live('mouseover', function() {
			//alert("ooo");
			parent.droppable_over=this;
			parent.jQuery(this).addClass('droppable_empty_hover');
		})
		.live('mouseout', function() {
			parent.droppable_over=null;
			parent.jQuery(this).removeClass('droppable_empty_hover');
		});
	parent.jQuery('img.editable', document).live('mouseover', function(e) {
		parent.jQuery(this).prev().css('display','block');
	});
	parent.jQuery('img.editable', document).live('mouseout', function(e) {
		parent.jQuery(this).prev().fadeOut();
	});
	parent.jQuery('span.img_handler', document).live('mouseover', function() {
		parent.jQuery(this).stop().show();
	});
	parent.jQuery('span.img_handler a.change_image', document).live('click', function(e) {
		parent.referer_image=parent.jQuery(this).parent().next();
		parent.tb_show('', 'media-upload.php?type=image&amp;post_id=' + parent.one_post_id + '&amp;TB_iframe=true&amp;width=640&amp;height=' + (parseInt(parent.jQuery(parent.window).height(), 10)-100));
		return false;
	});
	parent.jQuery('span.img_handler a.rredraw_image', document).live('click', function(e) {

		parent.referer_image = parent.jQuery(this).parent().next();

		clean_resize_handlers();

		parent.callback_img('<img src="' + parent.jQuery(parent.referer_image).attr('src')+ '" />');		
		return false;
	});
	parent.jQuery('span.img_handler a.uundo_image', document).live('click', function(e) {

		parent.jQuery(parent.referer_image_size).attr('height', parent.resizing_image_h_undo);
		parent.jQuery(parent.referer_image_size).attr('width', parent.resizing_image_w_undo);

		clean_resize_handlers();
		return false;
	});
	parent.jQuery('img.editable', document).live('click', function(e) {
		
		if (parent.referer_image_size != this && parent.referer_image_size != '') {
			alert(parent.must_apply_undo);
			return false;
		}
		
		if (parent.referer_image_size != this) {
		
			clean_resize_handlers();
	
			parent.jQuery(this).prev().append('<a href="#" class="rredraw_image" title="' + parent.sharp_image + '"></a>');
			parent.jQuery(this).prev().append('<a href="#" class="uundo_image" title="' + parent.undo_image + '"></a>');
	
			ww = parseInt(parent.jQuery(this).attr('width'), 10);
			hh = parseInt(parent.jQuery(this).attr('height'), 10);
			tt = parseInt(parent.jQuery(this).offset().top, 10);
			ll = parseInt(parent.jQuery(this).offset().left, 10);

			parent.resizing_image_w_undo=ww;
			parent.resizing_image_h_undo=hh;

			parent.referer_image_size = this;
			
			parent.resizing_image_style=parent.jQuery(this).attr('style');
			
			parent.jQuery(this)
				.css('outline', 'dashed #000 1px')
				.css('position','absolute')
				.css('left',ll)
				.css('top',tt)
				.after('<div style="width:' + ww + 'px; height:' + hh + 'px;" class="image_spacer">&nbsp;</div>');
	
			parent.jQuery(this).after('<span class="img_resizer img_resizer_s" handlertype="s">');
			parent.jQuery(this).after('<span class="img_resizer img_resizer_se" handlertype="se">');
			parent.jQuery(this).after('<span class="img_resizer img_resizer_e" handlertype="e">');
			parent.jQuery(this).after('<span class="img_resizer img_resizer_ne" handlertype="ne">');
			parent.jQuery(this).after('<span class="img_resizer img_resizer_n" handlertype="n">');
			parent.jQuery(this).after('<span class="img_resizer img_resizer_nw" handlertype="nw">');
			parent.jQuery(this).after('<span class="img_resizer img_resizer_w" handlertype="w">');
			parent.jQuery(this).after('<span class="img_resizer img_resizer_sw" handlertype="sw">');
			
			move_resize_handlers(ww, hh, tt, ll);
		}
		return false;
	});
	parent.jQuery('span.img_resizer', document)
		.live('mousedown', function(e) {

			ww = parseInt(parent.jQuery(parent.referer_image_size).attr('width'), 10);
			hh = parseInt(parent.jQuery(parent.referer_image_size).attr('height'), 10);
			tt = parseInt(parent.jQuery(parent.referer_image_size).offset().top, 10);
			ll = parseInt(parent.jQuery(parent.referer_image_size).offset().left, 10);

			parent.resizing_image_t=tt;
			parent.resizing_image_l=ll;
			parent.resizing_image_w=ww;
			parent.resizing_image_h=hh;

			parent.resizing_image=parent.jQuery(this).attr('handlertype');
			
			parent.resizing_image_x=parent.ratoliX;
			parent.resizing_image_y=parent.ratoliY;
			
			parent.resizing_image_handler_x=parent.jQuery(this).offset().left;
			parent.resizing_image_handler_y=parent.jQuery(this).offset().top;

			return false;
		})
		.live('mouseup', function(e) {
			parent.resizing_image='';
			return false;
		})
		.live('click', function(e) {
			return false;
		});

	parent.jQuery(document).bind('keypress keydown click', function() {
		update_editor();
	});

	/*parent.jQuery('.content_editable')
		.focus( function(e) {
			current = this;
			e.preventDefault();
		});*/

//}
});

function test_browser() {
	//alert("tb");
	parent.jQuery('div.wysiwyg_editor', document)
		.append('<p id="testbrowser" contenteditable="true">test</p>');
		
	selecttag(parent.jQuery('#testbrowser', document)[0]);
	var returnValue=document.execCommand('Bold',false,null);
	var returnValue=document.execCommand('Italic',false,null);

	if (parent.jQuery('#testbrowser strong', document).length == 1) {
		bold_type='strong';
	} else if (parent.jQuery('#testbrowser b', document).length == 1) {
		bold_type='b';
	} else {
		moz_dirty=true;
	}

	if (parent.jQuery('#testbrowser em', document).length == 1) {
		cursive_type='em';
	} else if (parent.jQuery('#testbrowser i', document).length == 1) {
		cursive_type='i';
	} else {
		moz_dirty=true;
	}
	
	parent.jQuery('#testbrowser', document).remove();
	
	//alert (bold_type);
	//alert (cursive_type);
	browserize_html(document);
}

function listen_module (module) {
		
	parent.jQuery('span.handler span.move', module).mousedown( function(e) {

			if (parent.referer_image_size != this && parent.referer_image_size != '') {
				alert(parent.must_apply_undo);
				e.preventDefault();
				return false;
			}
			parent.not_saved();
			parent.move_item=parent.jQuery(this).closest('.draggable');

			parent.jQuery(parent.move_item).closest('.droppable').prev().addClass('droppable_empty_hidden');
			parent.jQuery(parent.move_item).closest('.droppable').next().addClass('droppable_empty_hidden');
//LLLL
			parent.move_item.clone().appendTo(parent.jQuery('div.drag_preview'));

			parent.move_preview=true; parent.update_preview();

			parent.zone=parent.look_zone(parent.move_item);
			if (parent.zone != 0) {
				parent.jQuery('body', document).addClass('doing_drag');
				parent.jQuery('.droppable_empty', document).hide();
				parent.jQuery('.container_zone_' + parent.zone + ' .droppable_empty', document).show();
			} else {
				parent.jQuery('body', document).addClass('doing_drag');
			}

			e.preventDefault();
		});
	
	parent.jQuery('span.handler span.delete', module).click( function(e) {
			if (parent.referer_image_size != this && parent.referer_image_size != '') {
				alert(parent.must_apply_undo);
				return false;
			}

			parent.referer_delete = this;
			parent.tb_dialog('Knews', parent.confirm_delete, parent.button_yes, parent.button_no, 'deleteModule');
		});
	parent.jQuery(module).mouseover( function(e) {
			parent.jQuery('span.handler', this)
				.css('left', parseInt(parent.jQuery(this).offset().left, 10))
				.css('top', parseInt(parent.jQuery(this).offset().top, 10)-30);
			//;
		});

	/*parent.jQuery('.content_editable', module)
		.focus( function(e) {
			current = this;
			e.preventDefault();
		});*/
	
}
function look_scroll() {
	ratoliX_tb = parseInt(parent.jQuery(window).scrollLeft(), 10);
	ratoliY_tb = parseInt(parent.jQuery(document).scrollTop(), 10);
	return Array(ratoliX_tb, ratoliY_tb);
}

var tagsnav=Array();

function restore_focus() {
	if (inside_editor) {
		parent.jQuery(current_editor).focus();
	}
}
function saveSelection() {
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            return sel.getRangeAt(0);
        }
    } else if (document.selection && document.selection.createRange) {
        return document.selection.createRange();
    }
    return null;
}

function restoreSelection(range) {
    if (range) {
        if (window.getSelection) {
            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (document.selection && range.select) {
            range.select();
        }
    }
}

function browserize_html(context) {
	if (moz_dirty) {
		replace_tag('strong','span',context,'fontWeight','bold');
		replace_tag('b','span',context,'fontWeight','bold');
		replace_tag('em','span',context,'fontStyle','italic');
		replace_tag('i','span',context,'fontStyle','italic');
	} else {
		if (bold_type=='b') {
			replace_tag('strong','b','','');
		} else {
			replace_tag('b','strong','','');
		}
		if (cursive_type=='i') {
			replace_tag('em','i','','');
		} else {
			replace_tag('i','em','','');
		}
	}
}

function normalize_html() {
	if (moz_dirty) {
		var changes=1;
		while (changes != 0) {
			changes=0;
			parent.jQuery(".content_editable span", document).each(function() {
				if (parent.look_for_css_property(parent.jQuery(this).attr('style'), 'font-weight')) {
					if (parent.jQuery(this).css('fontWeight') == 'bold' || parseInt(parent.jQuery(this).css('fontWeight'), 10) > 400) {
						parent.jQuery(this).replaceWith('<b>' + parent.jQuery(this).html() + '</b>');
						changes++;
						return false;
					}
				}
				if (parent.look_for_css_property(parent.jQuery(this).attr('style'), 'font-style')) {
					if (parent.jQuery(this).css('fontStyle') == 'italic') {
						parent.jQuery(this).replaceWith('<i>' + parent.jQuery(this).html() + '</i>');
						changes++;
						return false;
					}
				}
				if (parent.look_for_css_property(parent.jQuery(this).attr('style'), 'text-decoration')) {
					if (parent.jQuery(this).css('textDecoration') == 'line-through') {
						parent.jQuery(this).replaceWith('<i>' + parent.jQuery(this).html() + '</i>');
						changes++;
						return false;
					}
				}
			});
		}
	}
	replace_tag('strong','b',document,'','');
	replace_tag('em','i',document,'','');
}

function replace_tag(findtag, replacetag, context, mdname, mdvalue) {
	parent.jQuery('.content_editable ' + findtag , context).each(function() {

		tag_content=parent.jQuery(this).html();
		tag_class=parent.jQuery(this).attr('class');
		tag_style=parent.jQuery(this).attr('style');

		parent.jQuery(this).replaceWith('<' + replacetag + ' class="knews_replacing">' + tag_content + '</' + replacetag + '>');
		if (tag_style != '') parent.jQuery(replacetag + '.knews_replacing', context).attr('style',tag_style);
		if (replacetag=='span') parent.jQuery(replacetag + '.knews_replacing', context).css(mdname,mdvalue);
			
		if (tag_class !='') {
			parent.jQuery('span.knews_replacing', context)
				.removeClass('knews_replacing')
				.addClass(tag_class);
		} else {
			parent.jQuery('span.knews_replacing', context).removeAttr('class');
		}
			
	});
}

function firefox_separate(container) {
	if (moz_dirty) {
		parent.jQuery('span', container).each(function() {
			attribs = parent.jQuery(this).attr('style');
			attribs = attribs.split(' ').join('');
			attribs=attribs.split(';');
	
			for (var x=0; x<attribs.length; x++) {
				if (attribs[x]=='' || attribs[x]==';') {
					attribs.splice(x, 1);
					x=x-1;
				}
			}
	
			if (attribs.length > 1) {
				code=parent.jQuery(this).html();
				//alert(parent.jQuery(this).parent().html());
				for (var x=1; x<attribs.length; x++) {
					code='<span style="' + attribs[x] + ';">' + code + '</span>';
				}
				parent.jQuery(this).attr('style',attribs[0]).html(code);
			}
		});
	}
}
function find_tag(node) {
	nocrash=0;
	while (node.tagName==undefined && nocrash<20) {
		nocrash++;
		node=node.parentNode;
	}
	return node;
}
/*parent.jQuery(document).ready(function() {
	//parent.jQuery('body > table').bind('click', function() {
		//alert("mouse");
	//});

})*/
function update_editor() {
	inside_editor=false;
	can_justify=false;
	im_on_link=false;
	var tags='';
	var ntag=0;
	var continue_loop=true;
	
//if (document.selection) { alert ('ds'); }
//if (window.getSelection) { alert ('gs'); }

	if (document.selection) {
		var selection = document.selection.createRange();
		current_node = selection.parentElement();
//alert(current_node);
//alert(current_node.innerHTML);
	} else if (window.getSelection) {
		
		var selection = window.getSelection(); //what the user has selected
		current_range = selection.getRangeAt(0); //the first range of the selection

		if (current_range.startContainer == current_range.endContainer) {
			current_node = current_range.startContainer;
		} else {
			current_node = current_range.commonAncestorContainer;
		}

	} else {
		return;
	}

	
	var in_node = current_node;

	while (continue_loop) {
		if (in_node.tagName!=undefined) {
			tag_name = in_node.tagName;

			if (tag_name=='SPAN' && moz_dirty) {
				spanstyle=parent.jQuery(in_node).attr('style');
				if (typeof spanstyle !== "undefined") {
					spanstyle=spanstyle.split(' ').join('');
					spanstyle=spanstyle.split(';').join('');
	
					if (spanstyle=='font-weight:bold') tag_name='b';
					if (spanstyle=='font-style:italic') tag_name='i';
					if (spanstyle=='text-decoration:line-through') tag_name='stroke';
				}
			}

			if (tag_name=='STRONG') tag_name='b';				
			if (tag_name=='EM') tag_name='i';				
			
			if (in_node.className == 'content_editable') {

				inside_editor=true;
				current_editor=in_node;
				continue_loop=false;
			
			} else {

				tags = '<a href="#" onclick="selecttag(' + ntag + '); return false;">&lt;' + tag_name.toLowerCase() + '&gt;</a> ' + tags;
				tagsnav[ntag]=in_node;
				ntag++;
	
				if (in_node.tagName == 'TD' || in_node.tagName == 'P' ) {
					can_justify=true;
					justify_node=in_node;
				}
				
				if (in_node.tagName == 'A' ) {
					im_on_link=true;
					link_node=in_node;
				}
				
				if (in_node.tagName == 'BODY') continue_loop=false;
			}
		}
		in_node = in_node.parentNode;
	}
	
	if (inside_editor) {

		parent.jQuery('#tagsnav').html(tags);
		parent.jQuery('#botonera a.color').css('backgroundColor', '#' + parent.rgb2hex(parent.jQuery(find_tag(current_node)).css('color')));
		parent.jQuery('#botonera div.standard_buttons').removeClass('desactivada');

		if (can_justify) {
			parent.jQuery('#botonera div.justify_buttons').removeClass('desactivada');
		} else {
			parent.jQuery('#botonera div.justify_buttons').addClass('desactivada');
		}

	} else {

		parent.jQuery('#tagsnav').html('');
		parent.jQuery('#botonera a.color').css('backgroundColor', '#888888');
		parent.jQuery('#botonera div.standard_buttons').addClass('desactivada');
		parent.jQuery('#botonera div.justify_buttons').addClass('desactivada');

	}
}

function selecttag_n(n) { selecttag(tagsnav[n]); }

function selecttag(obj) {
	if (document.selection) {
        var textRange = document.body.createTextRange();
        textRange.moveToElementText(obj);
        textRange.select();
    } else if (window.getSelection) {
        var sel = window.getSelection();
        sel.removeAllRanges();
        var range = document.createRange();
        range.selectNodeContents(obj);
        sel.addRange(range);
    }

	restore_focus();
}

function b_color(hex) {
	if (inside_editor) {
		if (im_on_link) {
			parent.jQuery(link_node).css('color',hex);
		} else {
			document.execCommand('ForeColor',false, hex);
		}
		restore_focus();
	}
}
function b_simple(action) {
	if (inside_editor) {
		//container = parent.jQuery(find_tag(info_node().commonAncestorContainer)).closest('span.content_editable');
		var returnValue = document.execCommand(action,false,null);
		firefox_separate(current_editor);
		restore_focus();
		update_editor();
	}
}
function b_link() {
	if (inside_editor) {

		url = '';
		if (im_on_link) url = parent.jQuery(link_node).attr('href');

		if (url = prompt('Link URL:', url)) {

			if (im_on_link) {
				if (url!='') parent.jQuery(link_node).attr('href',url);
			} else {
				document.execCommand('createlink',false,url);
			}
		}
		restore_focus();
		update_editor();
	}
}
function b_del_link() {
	if (inside_editor) {

		if (im_on_link) document.execCommand('unlink',false,url);

		restore_focus();
		update_editor();
	}
}
function b_justify(justify) {
	if (inside_editor) {
		
		if (can_justify) parent.jQuery(justify_node).attr('align',justify);

		restore_focus();
		update_editor();
	}
}

function move_resize_handlers(ww, hh, tt, ll) {
	parent.jQuery('span.img_resizer_s', document).css('left', ll + Math.floor(ww/2) - 4).css('top', tt + hh - 4);
	parent.jQuery('span.img_resizer_se', document).css('left', ll + ww - 4).css('top', tt + hh - 4);
	parent.jQuery('span.img_resizer_e', document).css('left', ll + ww - 4).css('top', tt + Math.floor(hh/2) - 4);
	parent.jQuery('span.img_resizer_ne', document).css('left', ll + ww - 4).css('top', tt - 4);
	parent.jQuery('span.img_resizer_n', document).css('left', ll + Math.floor(ww/2) - 4).css('top', tt - 4);
	parent.jQuery('span.img_resizer_nw', document).css('left', ll - 4).css('top', tt - 4);
	parent.jQuery('span.img_resizer_w', document).css('left', ll - 4).css('top', tt + Math.floor(hh/2) - 4);
	parent.jQuery('span.img_resizer_sw', document).css('left', ll - 4).css('top', tt + hh - 4);
	
	parent.jQuery(parent.referer_image_size).prev().css('left', ll).css('top', tt);
}

function clean_resize_handlers() {

	parent.jQuery('a.rredraw_image, a.uundo_image', document).remove();

	parent.jQuery('span.img_resizer',document).remove();
	parent.jQuery('div.image_spacer', document).remove();

	if (parent.referer_image_size!='') {
		parent.jQuery(parent.referer_image_size).attr('style', parent.resizing_image_style);

		tt = parseInt(parent.jQuery(parent.referer_image_size).offset().top, 10);
		ll = parseInt(parent.jQuery(parent.referer_image_size).offset().left, 10);
		parent.jQuery(parent.referer_image_size).prev().css('left', ll).css('top', tt);
	}

	parent.referer_image_size='';
}

