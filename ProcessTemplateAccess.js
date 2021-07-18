(function($) {

	$(document).ready(function() {

		var $datatable = $('#ProcessTemplateAccessTable');
		var $pta_inputs = $('#pta-inputs');

		// Toggle the icon state and add/remove associated input
		function toggleIcon($icon, new_value) {
			var id = $icon.data('id');
			var orig = $icon.data('orig');

			if(new_value === orig) {
				// Value is being changed to what it was when originally loaded
				// So remove the input from the form
				var $input = $pta_inputs.find('#' + id.replaceAll(':', '\\:'));
				if($input.length) $input.remove();
			} else {
				// Add an input to the form
				$pta_inputs.append('<input type="hidden" id="' + id + '" name="' + id + '" value="' + new_value + '">');
			}

			// Managed icon clicked
			if($icon.hasClass('pta-managed')) {
				var $access_table = $icon.closest('td').next('td').find('.access-table');
				// Set icon classes
				if(new_value) {
					$icon.removeClass('fa-minus-circle state-false');
					$icon.addClass('fa-check state-true');
					$access_table.removeClass('unmanaged');
				} else {
					$icon.removeClass('fa-check state-true');
					$icon.addClass('fa-minus-circle state-false');
					$access_table.addClass('unmanaged');
				}
				// Toggle guest view icon
				toggleIcon($access_table.find('.role-guest .pta-view-icon'), new_value);
			}
			// Access icon clicked
			else {
				// Set icon classes
				if(new_value) {
					$icon.removeClass('state-false');
					$icon.addClass('state-true');
				} else {
					$icon.removeClass('state-true');
					$icon.addClass('state-false');
				}
			}

			// Set new value
			$icon.data('value', new_value);
			// Check form validity
			if($pta_inputs.children().length) {
				$('.pta-submit').prop('disabled', false);
			} else {
				$('.pta-submit').prop('disabled', true);
			}

			// The state of the create icon depends on the state of the edit icon
			if($icon.hasClass('pta-edit-icon')) {
				var $create_icon = $icon.closest('td').next('td').find('.pta-create-icon');
				if(new_value) {
					// Enable create
					$create_icon.removeClass('create-disabled');
				} else {
					// Disable create and set value to 0
					$create_icon.addClass('create-disabled');
					toggleIcon($create_icon, 0);
				}
			}

			// Guest view access implies all roles have view access
			if($icon.hasClass('pta-view-icon')) {
				var $tr = $icon.parents('tr.role-guest');
				if($tr.length) {
					console.log('go');
					var $other_view_icons = $tr.siblings('tr').find('.pta-view-icon');
					if(new_value) {
						$other_view_icons.addClass('view-disabled');
						$other_view_icons.each(function() {
							toggleIcon($(this), new_value);
						});
					} else {
						$other_view_icons.removeClass('view-disabled');
					}
				}
			}
		}

		// Icon clicked
		$datatable.find('.pta-icon').click(function() {
			// Disabled alerts
			if($(this).hasClass('create-disabled')) {
				ProcessWire.alert(ProcessWire.config.ProcessTemplateAccess.create_alert);
				return;
			}
			if($(this).hasClass('view-disabled')) {
				ProcessWire.alert(ProcessWire.config.ProcessTemplateAccess.view_alert);
				return;
			}

			// Call toggleIcon()
			var value = $(this).data('value');
			var new_value = 1 - value;
			toggleIcon($(this), new_value);
		});

	});

}(jQuery));
