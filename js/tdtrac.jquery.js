function infobox(text, head) { // CONTROL INFOBOX CONTENT
	var first = $('.ui-page-active').children('.ui-content'),
		header = ( typeof(head) === 'undefined' ) ? 'Information' : head;
			
		first.simpledialog({
			'mode': 'blank',
			'prompt': 'Notice',
			'useDialogForceFalse': true,
			'cleanOnClose': true,
			'fullHTML': 
				'<ul data-role="listview" data-theme="c" data-dividertheme="a">'+
					'<li data-role="list-divider"><h3>'+header+'</h3></li>'+
					'<li>'+text+'</li></ul>'
		});
		
		setTimeout("$('.ui-page-active').children('.ui-content').data('simpledialog').close();", 2000);
	} // END INFOBOX CONTENT

jQuery.extend(jQuery.mobile.simpledialog.prototype.options, {
		cleanOnClose: true,
		useDialogForceFalse: true
});
	
(function($) {
	$('html').live('pageinit', function() {
		testMode = ( $('[data-role=header]:first').find('h1').text().search('TEST_MODE') > -1 ) ? true : false;
	});
	
	$('html').ajaxComplete(function(e,xhr,settings) {
		/* DEBUG ALL JSON BASED AJAX */
		if ( testMode === true && settings.url.search("json") > -1 ) {
			console.log(xhr.responseText);
		}
	});
	
	$('form').live('submit', function(e) { // FORM HANDLEING
		$.mobile.showPageLoadingMsg();
		e.preventDefault();
		
		var formdata = $(this).serialize(),
			formurl = $(this).attr('action'),
			ready = false,
			needed = [];
		
		$('[data-require=1]').each(function () {
			if ( $(this).val() == '' ) { 
				needed.push( $('[for='+$(this).attr('id')+']').text() );
			}
		});
		
		if ( needed.length > 0 ) {
			$.mobile.hidePageLoadingMsg();
			infobox("These fields are required:<br />'"+needed.join("', '")+"'", 'Error');
		} else {
			ready = true;
		}
		
		if ( ready ) {
			$.ajax({
				type: 'POST',
				url: formurl,
				data: formdata,
				success: 
					function(dta) {
						if ( testMode === true ) { console.log(dta); }
						if ( dta.success === true ) {
							$.mobile.changePage(dta.location, { reloadPage: true, type: 'post', data: {'infobox': dta.msg}, transition:'slide'});
						} else {
							$.mobile.hidePageLoadingMsg();
							infobox(dta.msg,'Error');
						}
				},
				dataType: 'json'});
		}
		
	}); // END FORM HANDLING
			
	
	$('.ajax-email').die('click');
	$('.ajax-email').live('click', function(e) { // BEGIN: E-Mail Function
		$.mobile.showPageLoadingMsg();
		e.preventDefault();
		
		var linkurl = '',
			o = $(this).data('email');

		switch(o.action) {
			case 'todo':
				linkurl = "/json/email/base:todo/id:"+o.id+"/type:"+o.type+"/";
				break;
			case 'budget':
				linkurl = "/json/email/base:budget/id:"+o.id+"/";
				break;
		}
		
		if ( linkurl !== '' ) {
			$.getJSON(linkurl, function(data) {
				$.mobile.hidePageLoadingMsg();
				if ( data.success === true ) {
					infobox("E-Mail Sent ("+o.action+")");
				} else {
					infobox("E-Mail Send Failed!");
				}
			});
		}
	}); // END: E-Mail Function
	
	$('.todo-done').live( 'vclick', function(e) {  // BEGIN: Mark Todo Done
		e.preventDefault();
		var linkie = this;
		if ( ! $(this).data('done') ) {
			$(this).simpledialog({
				'mode' : 'bool', 
				'prompt' : 'Mark Todo Item #'+$(this).data('recid')+' Done?',
				'buttons' : {
					'Yes, Mark Done' : function () {
						$.getJSON("/json/mark/base:todo/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
							if ( data.success === true ) {
								$(linkie).parent().insertAfter('#todo-list-done');
								$(linkie).parent().find('span.ui-li-count').html('done');
								var count = $('#todo-list-header').find('.ui-li-count');
								count.text(count.text()-1);
								infobox("Todo Item #"+$(linkie).data('recid')+" Marked Done");
							} else {
								infobox("Todo Item #"+$(linkie).data('recid')+" Mark Failed!");
							}
							$(linkie).data('done', 1);
						}); },
					'Cancel': function () { return true; }
				}
			});
		}
	}); // END: Mark Todo Done
	
	$('.todo-menu').live( 'vclick', function(e) {  // BEGIN: Todo Menu
		e.preventDefault();
		var linkie = this;
		if ( ! $(this).data('done') ) {
			$(this).simpledialog({
				'mode' : 'bool',
				'prompt' : 'Todo Item #'+$(this).data('recid'),
				'buttons' : (($(this).data('edit'))?{
					'Edit' : {
						'click': function() { $.mobile.changePage("/todo/edit/id:"+$(linkie).data('recid')+"/"); },
						'icon': 'grid'
					},
					'Delete' : {
						'click': function() {
							$.getJSON("/json/delete/base:todo/id:"+$(linkie).data('recid')+"/", function(data) {
								if ( data.success === true ) {
									$(linkie).parent().find('h3').html('--Removed--');
									$(linkie).parent().find('span.ui-li-count').html('deleted');
									if ( ! $(linkie).parent().find('.todo-done').data('done') ) {
										var count = $('#todo-list-header').find('.ui-li-count');
										count.text(count.text()-1);
									}
									$(linkie).parent().find('.todo-done').data('done', 1);
									infobox("Todo Item #"+$(linkie).data('recid')+" Deleted");
								} else {
									infobox("Todo Item #"+$(linkie).data('recid')+" Delete Failed!");
								}
								$(linkie).data('done', 1);
							});
						},
						'icon': 'delete'
					},
					'Cancel' : function () { return true; }
				}:{'Cancel' : function () { return true; }})
			}); 
		}
	}); // END: Todo Menu
	
	$('.msg-delete').live('vclick', function (e) { // BEGIN: Delete Message
		e.preventDefault();
		var linkie = this;
		if ( ! $(this).data('done') ) {
			$(this).simpledialog({
				'mode': 'bool',
				'prompt': 'Delete Message #'+$(linkie).data('recid')+'?',
				'buttons': { 
					'Yes, Delete' : function () {
						$.getJSON("/json/delete/base:msg/id:"+$(linkie).data('recid')+"/", function(data) {
							if ( data.success === true ) {
								$(linkie).parent().find('h3').html('--Removed--');
								infobox("Message #"+$(linkie).data('recid')+" Deleted");
							} else {
								infobox("Message #"+$(linkie).data('recid')+" Delete Failed!");
							}
							$(linkie).data('done', 1);
						}); },
					'Cancel' : function () { return true; }
				}
			});
		}
	}); // END: Delete Message
	
	$('#mailClear').die('click');
	$('#mailClear').live('click', function(e) { // BEGIN: Message Clear
		$.mobile.showPageLoadingMsg();
		e.preventDefault();
		
		$.getJSON("/json/clear/base:msg/id:0", function(dta) {
			if ( dta.success === true ) {
				$.mobile.changePage(dta.location, { reloadPage: true, type: 'post', data: {'infobox': dta.msg}, transition:'slide'});
			} else {
				$.mobile.hidePageLoadingMsg();
				infobox(dta.msg,'Error');
			}
		});
		
	}); // END: Message Clear
	
	$('.show-menu').live('vclick', function (e) { // BEGIN: Show Menu
		e.preventDefault();
		var linkie = this;
		if ( ! $(this).data('done') ) {
			$(this).simpledialog( {
				'mode' : 'bool',
				'prompt' : 'Show #'+$(this).data('recid'),
				'buttons' : (($(this).data('admin'))?{
					'Edit' : {
						'click' : function() { $.mobile.changePage('/shows/edit/id:'+$(linkie).data('recid')+'/'); },
						'icon' : 'grid'
					},
					'Delete' : {
						'click' :function () {
							$.getJSON("/json/delete/base:show/id:"+$(linkie).data('recid')+"/", function(data) {
								if ( data.success === true ) {
									$(linkie).find('h3').html('--Deleted--');
									infobox("Show #"+$(linkie).data('recid')+" Deleted");
								} else {
									infobox("Show #"+$(linkie).data('recid')+" Delete Failed!");
								}
								$(linkie).data('done', 1);
							});
						},
						'icon' : 'delete'
					},
					'Cancel' : function () { return true; }
				}:{ 'Cancel' : function () { return true; } } )
			});
		}
	}); // END: Show Menu
	
	$('.budg-menu').live('vclick', function (e) { // BEGIN: Budget Menu
		e.preventDefault();
		var linkie = this;
		if ( ! $(this).data('done') ) {
			$(this).simpledialog({
				'mode' : 'bool',
				'prompt' : 'Budget Item #'+$(this).data('recid'),
				'buttons' : (($(this).data('edit'))?{
					'View Detail' : {
						'click': function() { $.mobile.changePage("/budget/item/id:"+$(linkie).data('recid')+"/"); },
						'icon': 'grid'
					},
					'Edit' : {
						'click': function() { $.mobile.changePage("/budget/edit/id:"+$(linkie).data('recid')+"/"); },
						'icon': 'grid'
					},
					'Delete' : {
						'click': function() {
							$.getJSON("/budget/delete/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
								if ( data.success === true ) {
									$(linkie).parent().find('h3').html('--Removed--');
									$(linkie).parent().find('span.ui-li-count').html('deleted');
									$(linkie).parent().find('.todo-done').data('done', 1);
									infobox("Budget Item #"+$(linkie).data('recid')+" Deleted");
								} else {
									infobox("Budget Item #"+$(linkie).data('recid')+" Delete Failed!");
								}
								$(linkie).data('done', 1);
							});
						},
						'icon': 'delete'
					},
					'Cancel' : function () { return true; }
					
				}:{
					'View' : {
						'click': function() { $.mobile.changePage("/budget/view/id:"+$(linkie).data('recid')+"/"); },
						'icon': 'grid'
					},
					'Cancel' : function () { return true; }
				})
			}); 
		}
	}); // END: Budget Menu
	
	// BEGIN : Recpt Functions
	$('.rcptrot').live('vclick', function (e) { 
		var self = this;
			date = new Date();
		
		$(self).removeClass('ui-btn-active');
		infobox("Reciept Rotating...");
		$.getJSON("/rcpt.php?imgid="+$(self).data('id')+"&rotate="+$(self).data('rot')+"&save", function(data) {
			if ( data.success === true ) {
				$('#rcptimg').attr('src', '/rcpt.php?imgid='+$(self).data('id')+'&rand='+parseInt(date.getTime()/1000));
				infobox("Reciept Saved");
			} else {
				infobox("Reciept Save Failed :"+data.msg);
			}
		});
	}); // END Rcpt Func
	
	$('.group-add').live( 'vclick', function(e) { // BEGIN: Group Add
		e.preventDefault();
		var linkie = this;
		$(this).simpledialog({
			'mode': 'string',
			'prompt': 'New Group Name?',
			'buttons': {
				'Add' : {
					'click' : function() {
						if ($(linkie).data('string') !== '') {
							$.mobile.showPageLoadingMsg();
							$.getJSON("/json/adm/base:admin/sub:savegroup/id:0/newname:"+$(linkie).data('string')+"/", function(dta) {
								if ( dta.success === true ) {
									$.mobile.changePage(dta.location, { reloadPage: true, transition: 'pop', changeHash: 'false', type: 'post', data: {'infobox': dta.msg}});
								} else {
									$.mobile.hidePageLoadingMsg();
									infobox("Add Failed: "+dta.msg);
								}
							});
						}
					},
					'icon' : 'plus'
				},
				'Cancel' : function () { return true; }
			}
		});
	}); // END : Group Add
	
	$('.group-menu').live( 'vclick', function(e) {  // BEGIN: Group Menu
		e.preventDefault();
		var linkie = this;
		$(this).simpledialog({
			'mode' : ($(this).data('id') > 1 ) ? 'string' : 'bool',
			'prompt' : 'Group #'+$(this).data('id'),
			'buttons' : ($(this).data('id') > 1 ) ? 
				{
					'Rename' : {
						'click': function() {
							if ($(linkie).data('string') !== '') {
								$.mobile.showPageLoadingMsg();
								$.getJSON("/json/adm/base:admin/sub:savegroup/id:"+$(linkie).data('id')+"/newname:"+$(linkie).data('string')+"/", function(dta) {
									if ( dta.success === true ) {
										$.mobile.changePage(dta.location, { reloadPage: true, transition: 'pop', changeHash: 'false', type: 'post', data: {'infobox': dta.msg}});
									} else {
										$.mobile.hidePageLoadingMsg();
										infobox("Rename Failed: "+dta.msg);
									}
								});
							}
						},
						'icon': 'grid'
					},
					'Change Perms' : {
						'click': function() { $.mobile.changePage("/admin/permsedit/id:"+$(linkie).data('id')+"/"); },
						'icon': 'grid'
					},
					'Delete' : {
						'click': function() {
							$.mobile.showPageLoadingMsg();
							$.getJSON("/json/adm/base:admin/sub:deletegroup/id:"+$(linkie).data('id')+"/", function(dta) {
								if ( dta.success === true ) {
									$.mobile.changePage(dta.location, { reloadPage: true, transition: 'pop', changeHash: 'false', type: 'post', data: {'infobox': dta.msg}});
								} else {
									$.mobile.hidePageLoadingMsg();
									infobox("Delete Failed: "+dta.msg);
								}
							});
						},
						'icon': 'delete'
					},
					'Cancel' : function () { return true; }
				} : {
					'Change Perms' : {
						'click': function() { $.mobile.changePage("/admin/permsedit/id:"+$(linkie).data('id')+"/"); },
						'icon': 'grid'
					},
					'Cancel' : function () { return true; }
				}
		}); 
	}); // END: Group Menu
	
	$('.user-menu').live( 'vclick', function(e) {  // BEGIN: User Menu
		e.preventDefault();
		var linkie = this;
		$(this).simpledialog({
			'mode' : 'bool',
			'prompt' : 'User #'+$(this).data('recid'),
			'buttons' : {
				'Edit' : {
					'icon' : 'grid',
					'click' : function() { $.mobile.changePage("/admin/useredit/id:"+$(linkie).data('recid')+"/"); }
				},
				'Toggle Active' : {
					'icon' : 'check',
					'click' : function() {
						$.getJSON("/json/adm/base:admin/sub:toggle/switch:active/id:"+$(linkie).data('recid')+"/", function(dta) {
								if ( dta.success === true ) {
									infobox(dta.msg);
									if ( dta.newval === 1 ) {
										$(linkie).find('.u-act').attr('src', '/images/perm-ya.png');
									} else {
										$(linkie).find('.u-act').attr('src', '/images/perm-no.png');
									}
								} else {
									infobox("Toggle Failed: "+dta.msg);
								}
							});
					}
				},
				'Toggle On Payroll' : {
					'icon' : 'check',
					'click' : function() {
						$.getJSON("/json/adm/base:admin/sub:toggle/switch:payroll/id:"+$(linkie).data('recid')+"/", function(dta) {
								if ( dta.success === true ) {
									infobox(dta.msg);
									if ( dta.newval === 1 ) {
										$(linkie).find('.u-pay').attr('src', '/images/perm-ya.png');
									} else {
										$(linkie).find('.u-pay').attr('src', '/images/perm-no.png');
									}
								} else {
									infobox("Toggle Failed: "+dta.msg);
								}
							});
					}
				},
				'Toggle Only Own Hours' : {
					'icon' : 'check',
					'click' : function() {
						$.getJSON("/json/adm/base:admin/sub:toggle/switch:limithours/id:"+$(linkie).data('recid')+"/", function(dta) {
								if ( dta.success === true ) {
									infobox(dta.msg);
									if ( dta.newval === 1 ) {
										$(linkie).find('.u-own').attr('src', '/images/perm-ya.png');
									} else {
										$(linkie).find('.u-own').attr('src', '/images/perm-no.png');
									}
								} else {
									infobox("Toggle Failed: "+dta.msg);
								}
							});
					}
				},
				'Toggle Notify' : {
					'icon' : 'check',
					'click' : function() {
						$.getJSON("/json/adm/base:admin/sub:toggle/switch:notify/id:"+$(linkie).data('recid')+"/", function(dta) {
								if ( dta.success === true ) {
									infobox(dta.msg);
									if ( dta.newval === 1 ) {
										$(linkie).find('.u-not').attr('src', '/images/perm-ya.png');
									} else {
										$(linkie).find('.u-not').attr('src', '/images/perm-no.png');
									}
								} else {
									infobox("Toggle Failed: "+dta.msg);
								}
							});
					}
				},
				'Cancel' : function() { return true; }
			}
		});
	}); // End User Menu
	
	
	$('select').live('change', function(e) { // BEGIN : Add Dropdown Option
		var self = this;

		$(self+':selected:not([data-placeholder])').each(function(){
			if ( $(this).attr('data-addoption') ) {
				$(self).simpledialog({
					'mode' : 'string',
					'prompt' : 'Add New Option',
					'useDialogForceFalse' : true,
					'buttons' : {
						'Yes, Add' : function () { 
							thisopt = $(self).attr('data-string');
							$('<option value="'+thisopt+'" selected="selected">'+thisopt+'</option>').appendTo($(self));
							$(self).selectmenu('refresh', true);
							return true; },
						'Cancel' : function () { $(self).selectmenu('open'); }
					}
				});
			}
		});
	}); // END : Add Dropdown Option
	
	
}) ( jQuery );
