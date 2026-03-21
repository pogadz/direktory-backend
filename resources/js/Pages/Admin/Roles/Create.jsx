import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import PermissionChecklist from '../../../Components/PermissionChecklist';

export default function RolesCreate({ permissions }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        display_name: '',
        description: '',
        permission_ids: [],
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/admin/roles');
    }

    return (
        <AdminLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Create Role</h1>
            </div>

            <form onSubmit={handleSubmit} className="space-y-6 max-w-2xl">
                <div className="bg-white rounded-lg shadow p-6 space-y-4">
                    <Field label="Name (slug)" error={errors.name}>
                        <input
                            type="text"
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="e.g. moderator"
                            className="input"
                        />
                    </Field>

                    <Field label="Display Name" error={errors.display_name}>
                        <input
                            type="text"
                            value={data.display_name}
                            onChange={(e) => setData('display_name', e.target.value)}
                            placeholder="e.g. Moderator"
                            className="input"
                        />
                    </Field>

                    <Field label="Description" error={errors.description}>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows={2}
                            className="input"
                        />
                    </Field>
                </div>

                <div className="bg-white rounded-lg shadow p-6">
                    <h2 className="text-sm font-semibold text-gray-700 mb-4">Permissions</h2>
                    <PermissionChecklist
                        permissions={permissions}
                        selected={data.permission_ids}
                        onChange={(ids) => setData('permission_ids', ids)}
                    />
                </div>

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-5 py-2 rounded text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {processing ? 'Creating…' : 'Create Role'}
                    </button>
                    <a href="/admin/roles" className="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
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
