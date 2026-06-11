<?php

return [

    // General
    'permission' => 'Permission',
    'permissions' => 'Manage Permissions',
    'permission_label' => 'User Permission',

    // Table & Form Labels
    'permission_name' => 'Permission Name',
    'guard_name' => 'Guard',

    // Placeholders
    'permission_placeholder' => 'Enter model name, e.g., User, Post, etc.',

    // Meta
    'created_at' => 'Created At',
    'updated_at' => 'Last Updated',

    // Actions
    'create_permission' => 'Create Permission',
    'edit_permission' => 'Edit Permission',
    'delete_permission' => 'Delete Permission',

    // Form Sections
    'permission_information' => 'Permission Information',
    'permission_information_desc' => 'Used to manage system access rights.',
    'helper_text_permission' =>
        'Enter the permission name in the format like <strong>View Any User</strong>, <strong>Create User</strong>, or <strong>Delete User</strong>.
        <br><strong>Note:</strong> Replace <strong>"User"</strong> with the relevant entity, e.g., <strong>View Any Order</strong> or <strong>Create Product</strong>.
        <br><strong>Required:</strong> Each entity should have <strong>7 permissions</strong> in the exact format below:<br>
        <ul>
            <li><strong>View Any</strong> - View all records</li>
            <li><strong>View</strong> - View single record</li>
            <li><strong>Create</strong> - Add new record</li>
            <li><strong>Update</strong> - Edit record</li>
            <li><strong>Delete</strong> - Delete record</li>
            <li><strong>Restore</strong> - Restore deleted record</li>
            <li><strong>Force Delete</strong> - Permanently delete record</li>
        </ul>
        <br><strong>Important:</strong> Use the correct format so the system recognizes the permissions properly.',
];
