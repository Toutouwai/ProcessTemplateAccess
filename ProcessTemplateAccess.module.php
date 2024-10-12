<?php namespace ProcessWire;

class ProcessTemplateAccess extends Process {

	/**
	 * Init
	 */
	public function init() {
		$this->wire()->modules->get('JqueryUI')->use('vex');
		$this->wire()->config->js($this->className, [
			'create_alert' => $this->_('A role must have edit access in order to have create access. Please add edit access first.'),
			'view_alert' => $this->_('If the guest role has view access then all roles have view access.'),
		]);
		parent::init();
	}

	/**
	 * Execute
	 */
	public function ___execute() {
		$templates = $this->wire()->templates;
		$modules = $this->wire()->modules;
		$roles = $this->wire()->roles;
		$input = $this->wire()->input;

		if($input->post('submit')) {
			// The template permissions that require the role to have "page-edit" permission in order to be effective
			$page_edit_permissions = [
				'edit',
				'create',
				'add',
			];
			$changed_templates = new TemplatesArray();
			foreach($input->post as $key => $value) {
				if(substr($key, 0, 4) !== 'pta:') continue;
				$value = (int) $value;
				$pieces = explode(':', $key);
				// Managed
				if($pieces[2] === 'managed') {
					$template = $templates->get($pieces[1]);
					$changed_templates->add($template);
					$template->useRoles = $value;
				}
				// Role access
				else {
					list($junk, $template_name, $role_id, $type) = $pieces;
					$role_id = (int) $role_id;
					$role = $roles->get($role_id);
					$template = $templates->get($template_name);
					$changed_templates->add($template);
					if($value) {
						// Add "page-edit" permission to the role when needed
						if(!$role->hasPermission('page-edit') && in_array($type, $page_edit_permissions)) {
							$role->addPermission('page-edit');
							$role->save();
						}
						$template->addRole($role, $type);
					} else {
						$template->removeRole($role, $type);
					}
				}
			}
			foreach($changed_templates as $changed_template) {
				$changed_template->save();
			}
			$this->wire()->session->redirect('./');
		}

		$out = '';

		// Filter
		$placeholder = $this->_('Filter by template name...');
		$out .= <<<EOT
<div id="pta-filter-wrap">
	<input class="uk-input" id="pta-filter" type="text" placeholder="$placeholder">
	<i class="fa fa-search" id="pta-icon-search"></i>
	<i class="fa fa-times-circle" id="pta-icon-clear"></i>
</div>
EOT;


		/* @var $table MarkupAdminDataTable */
		$table = $modules->get('MarkupAdminDataTable');
		$table->setID($this->className . 'Table');
		$table->encodeEntities = false;
		$table->sortable = false;
		$table->headerRow([
			[$this->_('Template'), 'pta-template-col'],
			[$this->_('Managed'), 'pta-managed-col'],
			[$this->_('Role access'), 'pta-access-col'],
		]);

		foreach($templates as $template) {
			/** @var Template $template */

			// Skip system templates
			if($template->flags) continue;

			$managed = (int) $template->useRoles;
			$row = [];

			// Template name and edit link
			$link = $this->wire()->config->urls->admin . "setup/template/edit?id={$template->id}#tab_access";
			$link = "<a class='pta-template' href='$link'>$template->name</a>";
			$row[] = $link;

			// Is access managed for the template?
			$class = $managed ? 'fa-check state-true' : 'fa-minus-circle state-false';
			$row[] = "<i data-id='pta:{$template->name}:managed' data-orig='$managed' data-value='$managed' class='pta-icon pta-managed fa $class'></i>";

			// The access that each role has
			$access_str = "<table class='access-table'>";
			$guest_viewable = $template->roles->has('guest');
			foreach($roles->find("name!=superuser") as $role) {
				$access_str .= "<tr class='role-{$role->name}'><td>$role->name</td>";
				// View
				$viewable = (int) $template->roles->has($role);
				$class = $viewable ? 'state-true' : 'state-false';
				$icon_class = $role->name !== 'guest' && $guest_viewable ? ' view-disabled' : '';
				$title = $this->_('View');
				$access_str .= "<td class='$class'><i class='pta-icon pta-view-icon fa fa-eye{$icon_class}' uk-tooltip='$title' data-id='pta:{$template->name}:{$role->id}:view' data-orig='$viewable' data-value='$viewable'></i></td>";
				if($role->name === 'guest') {
					$access_str .= '<td></td><td></td><td></td>';
				} else {
					// Edit
					$editable = (int) in_array($role->id, $template->editRoles);
					$class = $editable ? 'state-true' : 'state-false';
					$title = $this->_('Edit');
					$access_str .= "<td class='$class'><i class='pta-icon pta-edit-icon fa fa-pencil-square-o' uk-tooltip='$title' data-id='pta:{$template->name}:{$role->id}:edit' data-orig='$editable' data-value='$editable'></i></td>";
					// Create
					$createable = (int) in_array($role->id, $template->createRoles);
					$class = $createable ? 'state-true' : 'state-false';
					$icon_class = $editable ? '' : ' create-disabled';
					$title = $this->_('Create');
					$access_str .= "<td class='$class'><i class='pta-icon pta-create-icon fa fa-plus-circle{$icon_class}' uk-tooltip='$title' data-id='pta:{$template->name}:{$role->id}:create' data-orig='$createable' data-value='$createable'></i></td>";
					// Add children
					$addable = (int) in_array($role->id, $template->addRoles);
					$class = $addable ? 'state-true' : 'state-false';
					$title = $this->_('Add children');
					$access_str .= "<td class='$class'><i class='pta-icon pta-add-icon fa fa-indent' uk-tooltip='$title' data-id='pta:{$template->name}:{$role->id}:add' data-orig='$addable' data-value='$addable'></i></td>";
					$access_str .= '</tr>';
				}
			}
			$access_str .= '</table>';
			$row[] = $access_str;

			$class = $managed ? '' : ' unmanaged';
			$table->row($row, ['class' => "pta-row$class"]);
		}

		$out .= $table->render();

		/** @var InputfieldForm $form */
		$form = $modules->get('InputfieldForm');
		$form->id = $this->className . 'Form';
		$form->prependMarkup = '<div id="pta-inputs"></div>';

		/** @var InputfieldSubmit $f */
		$f = $modules->get('InputfieldSubmit');
		$f->id = 'submit_save'; // For compatibility with AOS hotkey
		$f->value = $this->_('Save');
		$f->class .= ' pta-submit head_button_clone';
		$f->attr('disabled', 'disabled');
		$form->add($f);

		$out .= $form->render();
		return $out;
	}

}
