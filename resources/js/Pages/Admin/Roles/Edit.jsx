import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import PermissionChecklist from '../../../Components/PermissionChecklist';

export default function RolesEdit({ role, permissions }) {
    const detailsForm = useForm({
        name: role.name,
        display_name: role.display_name,
        description: role.description ?? '',
    });

    const permissionsForm = useForm({
        permission_ids: role.permissions.map((p) => p.id),
    });

    function handleDetailsSubmit(e) {
        e.preventDefault();
        detailsForm.put(`/admin/roles/${role.id}`);
    }

    function handlePermissionsSubmit(e) {
        e.preventDefault();
        permissionsForm.post(`/admin/roles/${role.id}/permissions`);
    }

    return (
        <AdminLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Edit Role</h1>
                <p className="text-sm text-gray-500 mt-1 font-mono">{role.name}</p>
            </div>

            <div className="space-y-6 max-w-2xl">
                {/* Role details */}
                <form onSubmit={handleDetailsSubmit} className="bg-white rounded-lg shadow p-6 space-y-4">
                    <h2 className="text-sm font-semibold text-gray-700">Details</h2>

                    <Field label="Name (slug)" error={detailsForm.errors.name}>
                        <input
                            type="text"
                            value={detailsForm.data.name}
                            onChange={(e) => detailsForm.setData('name', e.target.value)}
                            disabled={role.is_system_role}
                            className="input disabled:bg-gray-100 disabled:cursor-not-allowed"
                        />
                    </Field>

                    <Field label="Display Name" error={detailsForm.errors.display_name}>
                        <input
                            type="text"
                            value={detailsForm.data.display_name}
                            onChange={(e) => detailsForm.setData('display_name', e.target.value)}
                            disabled={role.is_system_role}
                            className="input disabled:bg-gray-100 disabled:cursor-not-allowed"
                        />
                    </Field>

                    <Field label="Description" error={detailsForm.errors.description}>
                        <textarea
                            value={detailsForm.data.description}
                            onChange={(e) => detailsForm.setData('description', e.target.value)}
                            disabled={role.is_system_role}
                            rows={2}
                            className="input disabled:bg-gray-100 disabled:cursor-not-allowed"
                        />
                    </Field>

                    {!role.is_system_role && (
                        <button
                            type="submit"
                            disabled={detailsForm.processing}
                            className="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {detailsForm.processing ? 'Saving…' : 'Save Details'}
                        </button>
                    )}
                    {role.is_system_role && (
                        <p className="text-xs text-yellow-700 bg-yellow-50 border border-yellow-200 rounded px-3 py-2">
                            System roles cannot be renamed or deleted.
                        </p>
                    )}
                </form>

                {/* Permissions */}
                <form onSubmit={handlePermissionsSubmit} className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-sm font-semibold text-gray-700 mb-4">Permissions</h2>
                    <PermissionChecklist
                        permissions={permissions}
                        selected={permissionsForm.data.permission_ids}
                        onChange={(ids) => permissionsForm.setData('permission_ids', ids)}
                    />
                    <button
                        type="submit"
                        disabled={permissionsForm.processing}
                        className="mt-4 bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {permissionsForm.processing ? 'Saving…' : 'Save Permissions'}
                    </button>
                </form>
            </div>
        </AdminLayout>
    );
}

function Field({ label, error, children }) {
    return (
        <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">{label}</label>
            {children}
            {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
        </div>
    );
}
