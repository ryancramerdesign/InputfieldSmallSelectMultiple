<?php namespace ProcessWire;

/**
 * Inputfield Small Select Multiple
 * 
 * ProcessWire module that adds multiple selection ability to a regular single <select>.
 * 
 * Copyright (c) 2024 by Ryan Cramer
 * 
 * @property string $iconPair Predefined selected and unselected icons separated by space.
 * @property string $onIcon Custom icon to indicate selected items
 * @property string $offIcon Custom optional icon to indicate unselected items
 * @property string $labelType One of "qty" or "value"
 * @property string $qtyLabel Example "{n} selected"
 * @property string $emptyLabel Example "Please select…"
 * @property string $separator What separates selected values when labelType is 'value'
 * @property bool|int $useTools Use select/deselect all tools?
 * @property bool|int $debug 
 * 
 *
 */
class InputfieldSmallSelectMultiple extends InputfieldSelectMultiple implements InputfieldHasArrayValue {
	
	/**
	 * Construct
	 * 
	 */
	public function __construct() {
		$this->setArray($this->getDefaults());
		parent::__construct();
	}

	/**
	 * Get default setting values
	 * 
	 * @return array
	 * 
	 */
	protected function getDefaults(): array {
		return [
			'iconPair' => '✔ —', 
			'onIcon' => '✔',
			'offIcon' => '—',
			'labelType' => 'qty', // qty or value
			'qtyLabel' => '{n} selected',
			'emptyLabel' => '',
			'separator' => ',_',
			'debug' => 0,
			'useTools' => 1, 
		];
	}

	/**
	 * Get all settings
	 * 
	 * @return array
	 * 
	 */
	public function getSettings(): array {
		$settings = $this->getDefaults();
		foreach(array_keys($settings) as $name) {
			$settings[$name] = $this->get($name);
		}
		$settings['separator'] = str_replace('_', ' ', $settings['separator']); 
		$iconPair = $settings['iconPair'];
		if($iconPair && $iconPair != 'custom') {
			if(strpos($iconPair, ' ') === false) $iconPair .= ' ';
			list($settings['onIcon'], $settings['offIcon']) = explode(' ', $iconPair);
		}
		if($settings['onIcon'] === $settings['offIcon']) $settings['offIcon'] = ' ';
		$this->onIcon = $settings['onIcon'];
		$this->offIcon = $settings['offIcon'];
		
		return $settings;
	}
	
	/**
	 * Called before render()
	 *
	 * @param Inputfield|null $parent
	 * @param bool $renderValueMode
	 * @return bool
	 *
	 */
	public function renderReady(?Inputfield $parent = null, $renderValueMode = false): bool {
		return parent::renderReady($parent, $renderValueMode);
	}

	/**
	 * Render select options
	 * 
	 * @param array $options
	 * @param bool $useIcons
	 * @return string
	 * 
	 */
	protected function renderSsmOptions(array $options, bool $useIcons = true): string {
		$sanitizer = $this->wire()->sanitizer;
		$selectedValue = $this->val();
		$settings = $this->getSettings();
		$out = '';
		
		foreach($options as $value => $label) {
			if(!strlen(trim("$value"))) continue;
			
			if(is_array($label)) {
				list($label, $optgroup) = [ $sanitizer->entities1($value), $label ];
				$out .= 
					"<optgroup label='$label'>" . 
						$this->renderSsmOptions($optgroup, $useIcons) . 
					"</optgroup>";
				continue;
			}
			
			$selected = in_array($value, $selectedValue);
		
			if($useIcons) {
				$icon = $selected ? $settings['onIcon'] : $settings['offIcon'];
			} else {
				$icon = '';
			}
			
			$value = $sanitizer->entities1($value);
			$label = $sanitizer->entities1(trim("$icon $label"));
			$class = $selected ? " class='ssm-selected'" : "";
			$out .= "<option$class value='$value'>$label</option>";
		}
		
		return $out;
	}

	/**
	 * Render optgroup/options for selection tools
	 * 
	 * @return string
	 * 
	 */
	protected function renderToolsOptions(): string {
		$label = $this->_('Tools');
		$tools = [
			'++' => "$this->onIcon " . $this->_('Select all'),
			'--' => "$this->offIcon " . $this->_('Deselect all'),
		];
		return $this->renderSsmOptions([ $label => $tools ], false);
	}

	/**
	 * Render
	 * 
	 * @return string
	 * 
	 */
	public function ___render(): string {
		$sanitizer = $this->wire()->sanitizer;
		$settings = $this->getSettings();
		
		$attrs = $this->getAttributes();
		$attrs['class'] = "$attrs[class] ssmInputSelect";
		$attrs['data-ssmopt'] = json_encode($settings);
		
		if(empty($attrs['id'])) $attrs['id'] = $attrs['name'];
		unset($attrs['multiple'], $attrs['value'], $attrs['size'], $attrs['name']);
		
		$attrStr = $this->getAttributesString($attrs);
		$options = $this->getOptions();
		$numSelected = 0;
		
		foreach($this->val() as $val) {
			if(!empty($val)) $numSelected++;
		}

		if($this->labelType === 'value' && $numSelected) {
			$selected = [];
			foreach($this->val() as $val) {
				$selected[$val] = $options[$val];
			}
			$label = implode($settings['separator'], $selected); 
		} else if($numSelected) {
			$label = str_replace('{n}', $numSelected, $this->qtyLabel);
		} else {
			$label = $this->emptyLabel;
		}
		
		$out = 
			"<select $attrStr>" . 
				"<option selected value=''>" . $sanitizer->entities($label) . "</option>" .
				$this->renderSsmOptions($options) .
				($this->useTools ? $this->renderToolsOptions() : '') . 
			"</select>";
	
		$this->addClass('ssmValueSelect');
		$this->attr('id', "_ssm_$attrs[id]");
		
		if($this->debug) {
			$this->attr('style', 'display:block; margin-top: 1em;');
		} else {
			$this->attr('hidden', 'hidden');
		}
		
		$out .= parent::___render();
		
		$this->removeClass('ssmValueSelect');
		$this->removeAttr('hidden');
		$this->removeAttr('style');
		$this->attr('id', $attrs['id']);
		
		return $out;
	}

	/**
	 * Field config
	 * 
	 * @return InputfieldWrapper
	 * 
	 */
	public function ___getConfigInputfields(): InputfieldWrapper {
		$inputfields = parent::___getConfigInputfields();
		require_once(__DIR__ . '/config.php');
		InputfieldSmallSelectMultipleConfig($inputfields, $this);
		return $inputfields;
	}
	
	public function ___install() {
		// enable this module for InputfieldPage
		$modules = $this->wire()->modules;
		$value = $modules->getConfig('InputfieldPage', 'inputfieldClasses');
		if(!is_array($value)) return;
		$value[] = $this->className();
		$modules->saveConfig('InputfieldPage', 'inputfieldClasses', $value);
	}
	
	public function ___uninstall() {
		// remove this module from InputfieldPage settings
		$modules = $this->wire()->modules;
		$value = $modules->getConfig('InputfieldPage', 'inputfieldClasses');
		if(!is_array($value)) return;
		$key = array_search($this->className(), $value, true);
		if($key === false) return;
		unset($value[$key]);
		$modules->saveConfig('InputfieldPage', 'inputfieldClasses', $value);
	}
}