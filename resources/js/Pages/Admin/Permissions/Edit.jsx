import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function PermissionsEdit({ permission, categories }) {
    const { data, setData, put, processing, errors } = useForm({
        name: permission.name,
        display_name: permission.display_name,
        description: permission.description ?? '',
        category: permission.category,
    });

    function handleSubmit(e) {
        e.preventDefault();
        put(`/admin/permissions/${permission.id}`);
    }

    return (
        <AdminLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Edit Permission</h1>
                <p className="text-sm text-gray-500 mt-1 font-mono">{permission.name}</p>
            </div>

            <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-6 space-y-4 max-w-2xl">
                <Field label="Name (slug)" error={errors.name}>
                    <input
                        type="text"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        className="input"
                    />
                </Field>

                <Field label="Display Name" error={errors.display_name}>
                    <input
                        type="text"
                        value={data.display_name}
                        onChange={(e) => setData('display_name', e.target.value)}
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

                <Field label="Category" error={errors.category}>
                    <input
                        type="text"
                        value={data.category}
                        onChange={(e) => setData('category', e.target.value)}
                        list="categories"
                        className="input"
                    />
                    <datalist id="categories">
                        {categories.map((c) => <option key={c} value={c} />)}
                    </datalist>
                </Field>

                <div className="pt-2 border-t border-gray-100">
                    <p className="text-xs text-gray-500 mb-3">
                        Assigned to roles:{' '}
                        {permission.roles.length
                            ? permission.roles.map((r) => r.display_name).join(', ')
                            : 'none'}
                    </p>
                </div>

                <div className="flex items-center gap-3">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-5 py-2 rounded text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {processing ? 'Saving…' : 'Save Changes'}
                    </button>
                    <a href="/admin/permissions" className="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
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
