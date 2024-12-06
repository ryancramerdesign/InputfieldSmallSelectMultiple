<?php namespace ProcessWire;

/**
 * Configuration
 * 
 * @param InputfieldWrapper $inputfields
 * @param InputfieldSmallSelectMultiple $module
 * 
 */
function InputfieldSmallSelectMultipleConfig(InputfieldWrapper $inputfields, InputfieldSmallSelectMultiple $module) {
	
	$iconPairs = [
		'● ○',
		'◉ ◯',
		'☑ □',
		'☒ □',
		'■ □',
		'◼ ◻',
		'▶ ▷',
		'✓ —',
		'✔ —',
		'✅ ',
		'custom', 
	];
	
	$f = $inputfields->InputfieldSelect; 
	$f->attr('name', 'iconPair');
	$f->label = __('Selected and unselected icons'); 
	foreach($iconPairs as $iconPair) {
		if($iconPair === 'custom') {
			$f->addOption('custom', __('Specify custom icons or text')); 
		} else {
			$f->addOption($iconPair);
		}
	}
	$f->val($module->iconPair);
	$inputfields->add($f);
	
	$f = $inputfields->InputfieldText;
	$f->attr('name', 'onIcon');
	$f->label = __('Selected icon (required)');
	$f->description = __('Text or UTF-8 icon to precede options that are selected.');
	$f->columnWidth = 50;
	$f->required = true;
	$f->val($module->onIcon);
	$f->showIf = 'iconPair=custom';
	$inputfields->add($f);

	$f = $inputfields->InputfieldText;
	$f->attr('name', 'offIcon');
	$f->label = __('Deselected icon (optional)');
	$f->description = __('Text or UTF-8 icon to precede options that are not selected (blank is fine too).');
	$f->columnWidth = 50;
	$f->showIf = 'iconPair=custom';
	$f->val($module->offIcon);
	$inputfields->add($f);

	$f = $inputfields->InputfieldRadios;
	$f->attr('name', 'labelType');
	$f->label = __('Selection label type');
	$f->addOption('qty', __('Quantity of selected items, i.e. “3 selected”'));
	$f->addOption('value', __('Selected option labels combined, i.e. “Red, Green, Blue”'));
	$f->val($module->labelType);
	$inputfields->add($f);

	$f = $inputfields->InputfieldText;
	$f->attr('name', 'qtyLabel');
	$f->label = __('Quantity label to use');
	$f->val($module->qtyLabel);
	$f->notes = __('The placeholder text `{n}` is automatically replaced by number of selected options.');
	$f->showIf = 'labelType=qty';
	$inputfields->add($f);

	$f = $inputfields->InputfieldText;
	$f->attr('name', 'separator');
	$f->label = __('Separator for items in label');
	$f->val($module->separator);
	$f->notes = __('Example: `,` is the separator in `Red,Green,Blue`. Underscore `_` is replaced with a space.');
	$f->showIf = 'labelType=value';
	$inputfields->add($f);

	$f = $inputfields->InputfieldText;
	$f->attr('name', 'emptyLabel');
	$f->label = __('Label when no selection');
	$f->val($module->emptyLabel);
	$f->notes = __('Example: “Please select…” (blank is default)');
	$inputfields->add($f);

	$f = $inputfields->InputfieldToggle;
	$f->attr('name', 'useTools');
	$f->label = __('Enable select/deselect all options?');
	$f->val($module->useTools);
	$inputfields->add($f);
	
	$f = $inputfields->getChildByName('defaultValue');
	if($f) {
		$f->collapsed = Inputfield::collapsedBlank;
		$inputfields->add($f);
	}
}