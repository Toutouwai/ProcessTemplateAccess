# Template Access

A Process module that provides an editable overview of roles that can access each template.

## Usage

The Template Access page under the Setup menu shows access information for all non-system templates in a table.

You can filter the table rows by template name if needed. 

Click an icon in the table to toggle its state. The changes are applied once you click the "Save" button.

Sometimes icons cannot be toggled because of logical rules described below. When an icon cannot be toggled the cursor changes to "not-allowed" when the icon is hovered and if the icon is clicked an explanatory alert appears.

* A role must have edit access before it can be granted create access.
* If the guest role has view access for a template then all roles have view access for that template.

![ta-1](https://github.com/Toutouwai/ProcessTemplateAccess/assets/1538852/83c7aae8-e52d-4338-8cb3-1c2a28d66311)

