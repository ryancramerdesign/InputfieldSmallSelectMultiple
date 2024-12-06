/**
 * Inputfield Small Select Multiple
 * 
 * Copyright 2024 by Ryan Cramer Design, LLC
 * 
 */
function InputfieldSmallSelectMultiple() {
	
	var defaults = {
		onIcon: '✅',
		offIcon: '',
		debug: false,
		labelType: 'qty',
		qtyLabel: '{n} selected',
		emptyLabel: '',
		separator: ', ', 
	};

	/**
	 * Update label of visible select to reflect quantity or selected items
	 * 
 	 */	
	function updateSelectLabel($select1) {
		var $select2 = $select1.next('select');
		var $option1 = $select1.children('option[value=""]');
		var $selected = $select2.children('option:selected');
		var numSelected = $selected.length;
		var settings = $select1.data('settings');
		
		if(settings.labelType === 'value') {
			var labels = [];
			$selected.each(function() {
				labels.push($(this).html());
			}); 
			var stringValue = labels.length ? labels.join(settings.separator) : '';
			$option1.text(stringValue);
			
		} else if(numSelected) {
			$option1.text(settings.qtyLabel.replace('{n}', numSelected)); 
			
		} else {
			$option1.text(settings.emptyLabel);
		}
		
		$select1.val('');
	}
	
	function setOptionSelected($option, selected) {
		if(selected) {
			if(!$option.prop('selected')) $option.prop('selected', true);
			$option.addClass('ssm-selected');
		} else {
			if($option.prop('selected')) $option.prop('selected', false);
			$option.removeClass('ssm-selected');
		}
	}
	
	/**
	 * Update the <option> to have a checked or unchecked icon
	 * 
	 * @param $option
	 * @param settings
	 * 
	 */
	function updateOptionState($option, settings) {
		
		var removeIcon = '';
		var addIcon = '';
		var optionLabel = $option.text();
		var $select1 = $option.closest('select');
		
		if(typeof settings === 'undefined') settings = $select1.data('settings');
		
		if($option.prop('selected')) {
			if(optionLabel.indexOf(settings.onIcon) === 0) return; // already has it
			if(settings.offIcon.length && optionLabel.indexOf(settings.offIcon) === 0) {
				removeIcon = settings.offIcon;
			}
			addIcon = settings.onIcon;
			setOptionSelected($option, true);
		} else {
			if(optionLabel.indexOf(settings.offIcon) === 0) return; // already has it
			if(optionLabel.indexOf(settings.onIcon) === 0) removeIcon = settings.onIcon;
			addIcon = settings.offIcon;
			setOptionSelected($option, false);
		}
		
		if(removeIcon.length) {
			optionLabel = optionLabel.replace(removeIcon, addIcon);
		} else if(addIcon.length) {
			optionLabel = addIcon + ' ' + optionLabel;
		}
		
		$option.text(optionLabel);
	}
	
	/**
	 * Apply action to all options
	 * 
	 * @param $options
	 * @param action i.e. '++' for select all, '--' for deselect all
	 * 
	 */
	function applyActionToAllOptions($options, action) {
		var qty = 0;
		var $select = null;
		
		$options.each(function() {
			var $option = $(this);
			if(action === '++' && !$option.prop('selected')) {
				setOptionSelected($option, true);
				qty ++;
			} else if(action === '--' && $option.prop('selected')) {
				setOptionSelected($option, false);
				qty++;
			}
			if($select === null) $select = $option.parent();
		}); 
		
		if(qty) $select.trigger('change');
	}
	
	/**
	 * Event called when visible select has a change (selection or deselection)
	 * 
	 */
	function select1ChangeEvent() {
	
		var $select1 = $(this);
		var $select2 = $select1.next('select');
		var value = $select1.val();
		var $optionSelect1 = false;
		var $optionSelect2 = false;
		var wasSelected = false;
		var applyAll = value === '++' || value === '--';
		var $options = $select2.children('option');
		var settings = $select1.data('settings');
		
		if(applyAll) {
			applyActionToAllOptions($options, value);
			return;
		}
		
		$options.each(function() {
			var $option = $(this);
			
			if(applyAll) return;
			if($optionSelect2) return;
			if($option.val() !== value) return;
			
			if($option.prop('selected')) {
				setOptionSelected($option, false);
				wasSelected = true;
			} else {
				setOptionSelected($option, true);
				$optionSelect2 = $option;
			}
		});
		
		$select1.children('option').each(function() {
			var $option = $(this);
			if($option.val() !== value) return;
			if(wasSelected) {
				$optionSelect1 = $option;
			} else {
				updateOptionState($option, settings);
			}
			setOptionSelected($option, false);
		});
		
		if($optionSelect1) {
			setOptionSelected($optionSelect1, false);
			updateOptionState($optionSelect1, settings);
		}
		
		updateSelectLabel($select1);
		$select2.trigger('change', [ 'ssm' ]);
	}
	
	/**
	 * Change event on the non-visible input that represents the value during POST
	 *
	 * @param e
	 * @param param1
	 *
	 */
	function select2ChangeEvent(e, param1) {
		if(typeof param1 !== 'undefined' && param1 === 'ssm') return;
		var $select2 = $(this);
		var $select1 = $select2.prev('select');
		var values = $select2.val();
		if(!values) values = [];
		$select1.children('option').each(function() {
			var $option = $(this);
			var optionValue = $option.attr('value');
			if(!optionValue) return;
			var selected = values.includes(optionValue);
			setOptionSelected($option, selected);
			updateOptionState($option);
		});
		updateSelectLabel($select1);
	}
	
	/**
	 * Initialize 1 select
	 * 
	 * @param $select1
	 * 
	 */
	function initSelect($select1) {
		var settings = jQuery.extend(defaults, JSON.parse($select1.attr('data-ssmopt')));
		$select1.data('settings', settings);
		$select1.addClass('ssmInit');
	}
	
	/**
	 * Initialize at document.ready
	 * 
	 */
	function init() {
		$(document)
			.on('change', '.ssmInputSelect', select1ChangeEvent)
			.on('change', '.ssmValueSelect', select2ChangeEvent);
		
		$(".InputfieldSmallSelectMultiple select.ssmInputSelect:not(.ssmInit)").each(function() {
			initSelect($(this));
		});
		
		$(document).on('reloaded', '.InputfieldSmallSelectMultiple, .InputfieldPage', function() {
			var $t = $(this);
			if($t.hasClass('InputfieldPage')) $t = $t.find('.InputfieldSmallSelectMultiple');
			if(!$t.length) return;
			$(this).find('select.ssmInputSelect:not(.ssmInit)').each(function() {
				initSelect($(this));
			});
		});
	}
	
	init();
}

jQuery(function($) {
	InputfieldSmallSelectMultiple();
}); 