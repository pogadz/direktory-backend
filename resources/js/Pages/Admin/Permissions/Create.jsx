import { useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function PermissionsCreate({ categories }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        display_name: '',
        description: '',
        category: '',
    });

    function handleSubmit(e) {
        e.preventDefault();
        post('/admin/permissions');
    }

    return (
        <AdminLayout>
            <div className="mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Create Permission</h1>
            </div>

            <form onSubmit={handleSubmit} className="bg-white rounded-lg shadow p-6 space-y-4 max-w-2xl">
                <Field label="Name (slug)" error={errors.name}>
                    <input
                        type="text"
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="e.g. booking.create"
                        className="input"
                    />
                </Field>

                <Field label="Display Name" error={errors.display_name}>
                    <input
                        type="text"
                        value={data.display_name}
                        onChange={(e) => setData('display_name', e.target.value)}
                        placeholder="e.g. Create Booking"
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
                        placeholder="e.g. booking"
                        list="categories"
                        className="input"
                    />
                    <datalist id="categories">
                        {categories.map((c) => <option key={c} value={c} />)}
                    </datalist>
                </Field>

                <div className="flex items-center gap-3 pt-2">
                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-5 py-2 rounded text-sm font-medium hover:bg-indigo-700 disabled:opacity-50"
                    >
                        {processing ? 'Creating…' : 'Create Permission'}
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
