import { Link, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function PermissionsIndex({ permissions }) {
    const grouped = permissions.reduce((acc, p) => {
        (acc[p.category] ??= []).push(p);
        return acc;
    }, {});

    function handleDelete(permission) {
        if (!confirm(`Delete permission "${permission.name}"? This cannot be undone.`)) return;
        router.delete(`/admin/permissions/${permission.id}`);
    }

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Permissions</h1>
                <Link
                    href="/admin/permissions/create"
                    className="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700"
                >
                    Create Permission
                </Link>
            </div>

            <div className="space-y-6">
                {Object.entries(grouped).map(([category, perms]) => (
                    <div key={category} className="bg-white rounded-lg shadow overflow-hidden">
                        <div className="px-6 py-3 bg-gray-50 border-b border-gray-200">
                            <span className="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                {category}
                            </span>
                        </div>
                        <table className="min-w-full divide-y divide-gray-100 text-sm">
                            <tbody className="divide-y divide-gray-50">
                                {perms.map((p) => (
                                    <tr key={p.id} className="hover:bg-gray-50">
                                        <td className="px-6 py-3 font-mono text-gray-700 w-64">{p.name}</td>
                                        <td className="px-6 py-3 text-gray-800">{p.display_name}</td>
                                        <td className="px-6 py-3 text-gray-400 text-xs">{p.description}</td>
                                        <td className="px-6 py-3 text-right space-x-3">
                                            <Link
                                                href={`/admin/permissions/${p.id}/edit`}
                                                className="text-indigo-600 hover:text-indigo-800 font-medium"
                                            >
                                                Edit
                                            </Link>
                                            <button
                                                onClick={() => handleDelete(p)}
                                                className="text-red-500 hover:text-red-700 font-medium"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                ))}
            </div>
        </AdminLayout>
    );
}
