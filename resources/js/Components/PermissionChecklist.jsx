export default function PermissionChecklist({ permissions, selected, onChange }) {
    const grouped = permissions.reduce((acc, p) => {
        (acc[p.category] ??= []).push(p);
        return acc;
    }, {});

    function toggle(id) {
        onChange(
            selected.includes(id)
                ? selected.filter((x) => x !== id)
                : [...selected, id],
        );
    }

    function toggleAll(ids, allChecked) {
        onChange(
            allChecked
                ? selected.filter((x) => !ids.includes(x))
                : [...new Set([...selected, ...ids])],
        );
    }

    return (
        <div className="space-y-4">
            {Object.entries(grouped).map(([category, perms]) => {
                const ids = perms.map((p) => p.id);
                const allChecked = ids.every((id) => selected.includes(id));

                return (
                    <div key={category}>
                        <div className="flex items-center gap-2 mb-2">
                            <input
                                type="checkbox"
                                id={`cat-${category}`}
                                checked={allChecked}
                                onChange={() => toggleAll(ids, allChecked)}
                                className="h-4 w-4 rounded border-gray-300 text-indigo-600"
                            />
                            <label
                                htmlFor={`cat-${category}`}
                                className="text-xs font-semibold uppercase tracking-wider text-gray-500 cursor-pointer"
                            >
                                {category}
                            </label>
                        </div>
                        <div className="ml-6 grid grid-cols-2 gap-1">
                            {perms.map((p) => (
                                <label key={p.id} className="flex items-center gap-2 cursor-pointer">
                                    <input
                                        type="checkbox"
                                        checked={selected.includes(p.id)}
                                        onChange={() => toggle(p.id)}
                                        className="h-4 w-4 rounded border-gray-300 text-indigo-600"
                                    />
                                    <span className="text-sm text-gray-700" title={p.description ?? ''}>
                                        {p.display_name}
                                    </span>
                                </label>
                            ))}
                        </div>
                    </div>
                );
            })}
        </div>
    );
}
