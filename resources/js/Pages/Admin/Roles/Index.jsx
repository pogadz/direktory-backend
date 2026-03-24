import { Link, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function RolesIndex({ roles }) {
    function handleDelete(role) {
        if (!confirm(`Delete role "${role.display_name}"? This cannot be undone.`)) return;
        router.delete(`/admin/roles/${role.id}`);
    }

    return (
        <AdminLayout>
            <div className="flex items-center justify-between mb-6">
                <h1 className="text-2xl font-bold text-gray-800">Roles</h1>
                <Link
                    href="/admin/roles/create"
                    className="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-medium hover:bg-indigo-700"
                >
                    Create Role
                </Link>
            </div>

            <div className="bg-white rounded-lg shadow overflow-hidden">
                <table className="min-w-full divide-y divide-gray-200 text-sm">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th className="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Display Name</th>
                            <th className="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">Permissions</th>
                            <th className="px-6 py-3 text-left font-medium text-gray-500 uppercase tracking-wider">System</th>
                            <th className="px-6 py-3" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {roles.map((role) => (
                            <tr key={role.id} className="hover:bg-gray-50">
                                <td className="px-6 py-4 font-mono text-gray-700">{role.name}</td>
                                <td className="px-6 py-4 text-gray-800">{role.display_name}</td>
                                <td className="px-6 py-4">
                                    <div className="flex flex-wrap gap-1">
                                        {role.permissions.slice(0, 4).map((p) => (
                                            <span key={p.id} className="px-2 py-0.5 bg-indigo-100 text-indigo-700 rounded text-xs">
                                                {p.name}
                                            </span>
                                        ))}
                                        {role.permissions.length > 4 && (
                                            <span className="px-2 py-0.5 bg-gray-100 text-gray-500 rounded text-xs">
                                                +{role.permissions.length - 4} more
                                            </span>
                                        )}
                                    </div>
                                </td>
                                <td className="px-6 py-4">
                                    {role.is_system_role
                                        ? <span className="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded">System</span>
                                        : <span className="text-xs text-gray-400">—</span>
                                    }
                                </td>
                                <td className="px-6 py-4 text-right space-x-3">
                                    <Link href={`/admin/roles/${role.id}/edit`} className="text-indigo-600 hover:text-indigo-800 font-medium">
                                        Edit
                                    </Link>
                                    {!role.is_system_role && (
                                        <button
                                            onClick={() => handleDelete(role)}
                                            className="text-red-500 hover:text-red-700 font-medium"
                                        >
                                            Delete
                                        </button>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
        </AdminLayout>
    );
}
