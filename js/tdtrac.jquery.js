(function($) {
	
	$('form').live('submit', function(e) { // FORM HANDLEING
		infobox('Please wait...');
		var formdata = $(this).serialize();
		var formurl = $(this).attr('action');
		
		$.post(formurl, formdata, function(dta) {
			if ( dta.success === true ) {
				$.mobile.changePage(dta.location, { reloadPage: true, type: 'post', data: {'infobox': dta.msg}, transition:'slide'});
			} else {
				infobox('<span style="color: red">'+dta.msg+'</span>');
			}
		}, 'json');
		e.preventDefault();
	}); // END FORM HANDLING
			
	function infobox(text) { // CONTROL INFOBOX CONTENT
		$('.ui-page-active #infobox h2').stop(true);
		$('.ui-page-active #infobox h2').fadeTo(300, .01, function() {
			$(this).html(text).fadeTo(1000,1, function() {
				$(this).delay(4000).delay(4000).fadeTo(300, .01, function() {
					$(this).html('&nbsp;').fadeTo(1000,1); 
				}); 
			});
		});
	} // END INFOBOX CONTENT
	
	$('.ajax-email').die('click');
	$('.ajax-email').live('vclick', function(e) { // BEGIN: E-Mail Function
		var linkurl = '',
			o = $(this).data('email');

		switch(o.action) {
			case 'todo':
				linkurl = "/todo/email/json:1/id:"+o.id+"/type:"+o.type+"/";
				break;
			case 'budget':
				linkurl = "/budget/email/json:1/id:"+o.id+"/";
				break;
		}
		
		if ( linkurl !== '' ) {
			infobox("Please Wait...");
			$.getJSON(linkurl, function(data) {
				if ( data.success === true ) {
					infobox("E-Mail Sent ("+o.action+")");
				} else {
					inforbox("E-Mail Send Failed!");
				}
			});
		}
		e.preventDefault();
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
						$.getJSON("/todo/mark/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
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
							$.getJSON("/todo/delete/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
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
						$.getJSON("/mail/delete/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
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
							$.getJSON("/shows/delete/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
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
							$.getJSON("/admin/group/json:1/newname:"+$(linkie).data('string')+"/", function(dta) {
								if ( dta.success === true ) {
									$.mobile.changePage(dta.location, { reloadPage: true, transition: 'pop', changeHash: 'false', type: 'post', data: {'infobox': dta.msg}});
								} else {
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
								$.getJSON("/admin/group/json:1/oldname:"+$(linkie).data('id')+"/newname:"+$(linkie).data('string')+"/", function(dta) {
									if ( dta.success === true ) {
										$.mobile.changePage(dta.location, { reloadPage: true, transition: 'pop', changeHash: 'false', type: 'post', data: {'infobox': dta.msg}});
									} else {
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
							$.getJSON("/admin/remgroup/json:1/oldname:"+$(linkie).data('id')+"/", function(dta) {
								if ( dta.success === true ) {
									$.mobile.changePage(dta.location, { reloadPage: true, transition: 'pop', changeHash: 'false', type: 'post', data: {'infobox': dta.msg}});
								} else {
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
						$.getJSON("/admin/toggle/json:1/switch:active/id:"+$(linkie).data('recid')+"/", function(dta) {
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
						$.getJSON("/admin/toggle/json:1/switch:payroll/id:"+$(linkie).data('recid')+"/", function(dta) {
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
						$.getJSON("/admin/toggle/json:1/switch:limit/id:"+$(linkie).data('recid')+"/", function(dta) {
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
						$.getJSON("/admin/toggle/json:1/switch:notify/id:"+$(linkie).data('recid')+"/", function(dta) {
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
