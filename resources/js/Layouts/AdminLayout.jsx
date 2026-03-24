import { Link, usePage, router } from '@inertiajs/react';

export default function AdminLayout({ children }) {
    const { auth, flash } = usePage().props;

    function handleLogout(e) {
        e.preventDefault();
        router.post('/admin/logout');
    }

    return (
        <div className="min-h-screen flex">
            {/* Sidebar */}
            <aside className="w-64 bg-gray-900 text-gray-100 flex flex-col">
                <div className="px-6 py-5 border-b border-gray-700">
                    <span className="text-lg font-semibold tracking-wide">Direktory Admin</span>
                </div>

                <nav className="flex-1 px-4 py-6 space-y-1">
                    <NavLink href="/admin/dashboard">Dashboard</NavLink>
                    <NavLink href="/admin/roles">Roles</NavLink>
                    <NavLink href="/admin/permissions">Permissions</NavLink>
                    <NavLink href="/admin/global-configurations">Global Configurations</NavLink>
                </nav>

                <div className="px-4 py-4 border-t border-gray-700">
                    <p className="text-sm text-gray-400 mb-2">
                        {auth.user?.firstname} {auth.user?.lastname}
                    </p>
                    <form onSubmit={handleLogout}>
                        <button
                            type="submit"
                            className="text-sm text-red-400 hover:text-red-300"
                        >
                            Sign out
                        </button>
                    </form>
                </div>
            </aside>

            {/* Main content */}
            <div className="flex-1 flex flex-col">
                <main className="flex-1 p-8">
                    {flash?.success && (
                        <div className="mb-4 px-4 py-3 bg-green-100 text-green-800 rounded border border-green-200 text-sm">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 px-4 py-3 bg-red-100 text-red-800 rounded border border-red-200 text-sm">
                            {flash.error}
                        </div>
                    )}
                    {children}
                </main>
            </div>
        </div>
    );
}

function NavLink({ href, children }) {
    const { url } = usePage();
    const active = url.startsWith(href);

    return (
        <Link
            href={href}
            className={`block px-3 py-2 rounded text-sm font-medium transition-colors ${
                active
                    ? 'bg-indigo-600 text-white'
                    : 'text-gray-300 hover:bg-gray-700 hover:text-white'
            }`}
        >
            {children}
        </Link>
    );
}
