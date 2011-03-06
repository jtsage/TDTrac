(function($) {
	
	$('form').live('submit', function(e) {
		infobox('Please wait...');
		var formdata = $(this).serialize();
		var formurl = $(this).attr('action');
		$.post(formurl, formdata, function(dta) {
			console.log(dta);
			if ( dta.success === true ) {
				$.mobile.changePage({ url: dta.location, type: 'post', data: {'infobox': dta.msg} },'slide', true);
			} else {
				infobox('<span style="color: red">'+dta.msg+'</span>');
			}
		}, 'json');
		e.preventDefault();
	});
			
	function infobox(text) { // CONTROL INFOBOX CONTENT
		$('.ui-page-active #infobox h2').fadeTo(300, .01, function() {
			$(this).html(text).fadeTo(1000,1, function() {
				$(this).delay(4000).delay(4000).fadeTo(300, .01, function() {
					$(this).html('&nbsp;').fadeTo(1000,1); 
				}); 
			});
		});
	}
	
	$('.ajax-email').live('click', function(e) { // BEGIN: E-Mail Function
		var linkurl = '',
			o = $(this).data('email');

		switch(o.action) {
			case 'todo':
				linkurl = "/todo/email/json:1/id:"+o.id+"/type:"+o.type+"/";
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
	
	$('.todo-done').live( 'click', function(e) {  // BEGIN: Mark Todo Done
		var linkie = this;
		if ( ! $(this).data('done') && confirm('Mark Todo Item #'+$(this).data('recid')+' Done?')) {
			$.getJSON("/todo/mark/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
				if ( data.success === true ) {
					$(linkie).parent().find('span.ui-li-count').html('done');
					var count = $(linkie).parentsUntil('#list_todo_view').parent().find('[data-role="list-divider"]').find('.ui-li-count');
					count.text(count.text()-1);
					infobox("Todo Item #"+$(linkie).data('recid')+" Marked Done");
				} else {
					infobox("Todo Item #"+$(linkie).data('recid')+" Mark Failed!");
				}
				$(linkie).data('done', 1);
			});
		}
		e.preventDefault();
	}); // END: Mark Todo Done
	
	$('.todo-delete').live( 'click', function(e) {  // BEGIN: Mark Todo Delete
		var linkie = this;
		if ( ! $(this).data('done') && confirm('Delete Todo Item #'+$(this).data('recid')+'?')) {
			$.getJSON("/todo/delete/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
				if ( data.success === true ) {
					$(linkie).parent().find('h3').html('--Removed--');
					$(linkie).parent().find('span.ui-li-count').html('deleted');
					if ( ! $(linkie).parent().find('.todo-done').data('done') ) {
						var count = $(linkie).parentsUntil('#list_todo_view').parent().find('[data-role="list-divider"]').find('.ui-li-count');
						count.text(count.text()-1);
					}
					$(linkie).parent().find('.todo-done').data('done', 1);
					infobox("Todo Item #"+$(linkie).data('recid')+" Deleted");
				} else {
					infobox("Todo Item #"+$(linkie).data('recid')+" Delete Failed!");
				}
				$(linkie).data('done', 1);
			});
		}
		e.preventDefault();
	}); // END: Mark Todo Delete
	
	$('.msg-delete').live('click', function (e) { // BEGIN: Delete Message
		var linkie = this;
		if ( ! $(this).data('done') && confirm('Delete Message #'+$(this).data('recid')+'?')) {
			$.getJSON("/mail/delete/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
				if ( data.success === true ) {
					$(linkie).parent().find('h3').html('--Removed--');
					infobox("Message #"+$(linkie).data('recid')+" Deleted");
				} else {
					infobox("Message #"+$(linkie).data('recid')+" Delete Failed!");
				}
				$(linkie).data('done', 1);
			});
		}
		e.preventDefault();
	}); // END: Delete Message
	
	$('.show-delete').live('click', function (e) { // BEGIN: Delete Show
		var linkie = this;
		if ( ! $(this).data('done') && confirm('Delete Show #'+$(this).data('recid')+'?')) {
			$.getJSON("/shows/delete/json:1/id:"+$(linkie).data('recid')+"/", function(data) {
				if ( data.success === true ) {
					$(linkie).parent().find('h3').html('--Deleted--');
					infobox("Show #"+$(linkie).data('recid')+" Deleted");
				} else {
					infobox("Show #"+$(linkie).data('recid')+" Delete Failed!");
				}
				$(linkie).data('done', 1);
			});
		}
		e.preventDefault();
	}); // END: Delete Show
	
}) ( jQuery );
