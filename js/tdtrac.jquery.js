(function($) {
	
	function infobox(text) { // CONTROL INFOBOX CONTENT
		$('#infobox h4').fadeTo(300, .01, function() {
			$(this).html(text).fadeTo(1000,1, function() {
				$(this).delay(4000).delay(4000).fadeTo(300, .01, function() {
					$(this).html('--').fadeTo(1000,1); 
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
	
	
	
}) ( jQuery );
	/*	
		
		
		global $TDTRAC_SITE, $SITE_SCRIPT;
		$SITE_SCRIPT[] = "var tdelrow{$this->currentrow} = true;";
		$SITE_SCRIPT[] = "$(function() { $('#link_tdel_{$this->listname}_{$this->currentrow}').click( function() {";
		$SITE_SCRIPT[] = "	if ( tdelrow{$this->currentrow} && confirm('Delete Item #{$raw['id']}?')) {";
		$SITE_SCRIPT[] = "		$.getJSON(\"{$TDTRAC_SITE}todo/delete/json:1/id:{$raw['id']}/\", function(data) {";
		$SITE_SCRIPT[] = "			if ( data.success === true ) { ";
		$SITE_SCRIPT[] = "				$('#link_tdel_{$this->listname}_{$this->currentrow}').parent().find('h3').html('--Removed--');";
		$SITE_SCRIPT[] = "				$('#link_tdel_{$this->listname}_{$this->currentrow}').parent().find('span.ui-li-count').html('deleted');";
		$SITE_SCRIPT[] = "				infobox(\"To-Do Item #{$raw['id']} Deleted\");";
		$SITE_SCRIPT[] = "			} else { infobox(\"To-Do Item #{$raw['id']} Delete :: Failed\"); }";
		$SITE_SCRIPT[] = "			tdelrow{$this->currentrow} = false;";
		$SITE_SCRIPT[] = "			tdonerow{$this->currentrow} = false;";
		$SITE_SCRIPT[] = "			setTimeout(function () { $('#infobox h4').html('--'); }, 10000);";		
		$SITE_SCRIPT[] = "	});} return false;";
		$SITE_SCRIPT[] = "});});";
*/
