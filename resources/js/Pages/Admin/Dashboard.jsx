import AdminLayout from '../../Layouts/AdminLayout';

export default function Dashboard({ stats }) {
    return (
        <AdminLayout>
            <h1 className="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

            <div className="grid grid-cols-3 gap-6">
                <StatCard label="Roles" value={stats.roles} />
                <StatCard label="Permissions" value={stats.permissions} />
                <StatCard label="Users" value={stats.users} />
            </div>
        </AdminLayout>
    );
}

function StatCard({ label, value }) {
    return (
        <div className="bg-white rounded-lg shadow p-6">
            <p className="text-sm text-gray-500">{label}</p>
            <p className="text-3xl font-bold text-gray-800 mt-1">{value}</p>
        </div>
    );
}
