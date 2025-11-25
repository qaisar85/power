import React from 'react';
import { Head, router, usePage } from '@inertiajs/react';

export default function Logs({ logs, filters }) {
  const { props } = usePage();
  const [form, setForm] = React.useState({
    action_type: filters?.action_type || '',
    admin_id: filters?.admin_id || '',
    from: filters?.from || '',
    to: filters?.to || '',
  });

  const submit = (e) => {
    e.preventDefault();
    router.get(route('admin.logs.index'), form, {
      preserveState: true,
      replace: true,
    });
  };

  const reset = () => {
    setForm({ action_type: '', admin_id: '', from: '', to: '' });
    router.get(route('admin.logs.index'));
  };

  const onChange = (e) => {
    const { name, value } = e.target;
    setForm((f) => ({ ...f, [name]: value }));
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <Head title="Admin Action Logs" />
      <div className="mx-auto max-w-7xl px-4 py-8">
        <h1 className="text-2xl font-semibold mb-4">Action Logs</h1>

        <form onSubmit={submit} className="bg-white shadow rounded p-4 mb-6 grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label className="block text-sm font-medium text-gray-700">Action Type</label>
            <input name="action_type" value={form.action_type} onChange={onChange} className="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. login, approve, delete" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">Admin ID</label>
            <input name="admin_id" value={form.admin_id} onChange={onChange} className="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. 1" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">From</label>
            <input type="datetime-local" name="from" value={form.from} onChange={onChange} className="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
          </div>
          <div>
            <label className="block text-sm font-medium text-gray-700">To</label>
            <input type="datetime-local" name="to" value={form.to} onChange={onChange} className="mt-1 w-full rounded-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" />
          </div>
          <div className="flex items-end gap-2">
            <button type="submit" className="px-3 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Filter</button>
            <button type="button" onClick={reset} className="px-3 py-2 bg-gray-100 text-gray-800 rounded hover:bg-gray-200">Reset</button>
          </div>
        </form>

        <div className="bg-white shadow rounded overflow-x-auto">
          <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
              <tr>
                <Th>Time</Th>
                <Th>Admin</Th>
                <Th>Action</Th>
                <Th>Target</Th>
                <Th>Comment</Th>
              </tr>
            </thead>
            <tbody className="divide-y divide-gray-200">
              {(logs?.data || []).map((row) => (
                <tr key={row.id} className="hover:bg-gray-50">
                  <Td>{row.performed_at}</Td>
                  <Td>{row.admin?.name || `#${row.admin_id}`}</Td>
                  <Td className="font-medium">{row.action_type}</Td>
                  <Td>{row.target_type ? `${row.target_type} #${row.target_id}` : '-'}</Td>
                  <Td className="text-gray-600">{row.comment || '-'}</Td>
                </tr>
              ))}
              {(!logs?.data || logs.data.length === 0) && (
                <tr>
                  <Td colSpan={5} className="text-center text-gray-500 py-6">No logs found for the selected filters.</Td>
                </tr>
              )}
            </tbody>
          </table>
        </div>

        <Pagination links={logs?.links || []} />
      </div>
    </div>
  );
}

function Th({ children }) {
  return (
    <th scope="col" className="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
      {children}
    </th>
  );
}

function Td({ children, colSpan }) {
  return (
    <td colSpan={colSpan} className="px-4 py-3 whitespace-nowrap text-sm text-gray-800">
      {children}
    </td>
  );
}

function Pagination({ links }) {
  if (!links.length) return null;
  return (
    <div className="flex items-center justify-center gap-2 mt-6">
      {links.map((link, i) => (
        <a
          key={i}
          href={link.url || '#'}
          className={`px-3 py-2 rounded border ${link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50'}`}
          dangerouslySetInnerHTML={{ __html: link.label }}
        />
      ))}
    </div>
  );
}