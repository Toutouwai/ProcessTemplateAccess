# Template Access

A Process module for ProcessWire CMS/CMF. Provides an editable overview of roles that can access each template.

## Usage

The Template Access page under the Setup menu shows an overview of the roles that can access each template. The module only deals with access that is explicitly defined for each template - it doesn't show "inherited" access.

Click an icon to toggle its state. The changes are applied once you click the "Save" button.

Sometimes icons cannot be toggled because of logical rules described below. When an icon cannot be toggled the cursor changes to "not-allowed" when the icon is hovered and if the icon is clicked an explantory alert appears.

* A role must have edit access before it can be granted create access.
* If the guest role has view access for a template then all roles have view access for that template.
