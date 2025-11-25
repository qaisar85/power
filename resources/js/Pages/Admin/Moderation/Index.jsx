import React, { useMemo } from 'react';
import { Link, useForm } from '@inertiajs/react';

export default function ModerationIndex({ filters, tasks, allowedPermissions, moderators = [], isChief = false }) {
  const can = (perm) => allowedPermissions?.includes(perm);
  const { data, setData, post, processing, reset } = useForm({ comment: '', reason: '', moderator_id: '' });

  const sections = [
    { key: '', label: 'All' },
    { key: 'company_doc', label: 'Company documents' },
    { key: 'product_card', label: 'Products/services' },
    { key: 'tender', label: 'Tenders' },
    { key: 'auction', label: 'Auctions' },
    { key: 'inspection_request', label: 'Inspection requests' },
    { key: 'logistics', label: 'Logistics' },
    { key: 'complaint', label: 'Complaints' },
    { key: 'spam', label: 'Spam' },
    { key: 'user', label: 'New users/companies' },
    { key: 'news', label: 'News/articles/vacancies' },
  ];

  const statuses = [
    { key: 'pending', label: 'Pending Moderation' },
    { key: 'approved', label: 'Approved' },
    { key: 'declined', label: 'Declined' },
    { key: 'revision_requested', label: 'Revision Requested' },
  ];

  const changeFilter = (key, value) => {
    const url = route('admin.moderation.index', { ...filters, [key]: value || undefined });
    window.location.href = url;
  };

  const approve = (taskId) => {
    post(route('admin.moderation.tasks.approve', taskId), { preserveScroll: true });
  };
  const decline = (taskId) => {
    if (!data.reason) return alert('Please provide a reason');
    post(route('admin.moderation.tasks.decline', taskId), { preserveScroll: true, data: { reason: data.reason } });
  };
  const requestRevision = (taskId) => {
    if (!data.comment) return alert('Please provide a comment');
    post(route('admin.moderation.tasks.revision', taskId), { preserveScroll: true, data: { comment: data.comment } });
  };
  const assignSelf = (taskId) => {
    post(route('admin.moderation.tasks.assignSelf', taskId), { preserveScroll: true });
  };
  const assignModerator = (taskId) => {
    if (!data.moderator_id) return alert('Select a moderator');
    post(route('admin.moderation.tasks.assign', taskId), { preserveScroll: true, data: { moderator_id: data.moderator_id } });
  };

  const sortOptions = [
    { key: 'date', label: 'By date' },
    { key: 'priority', label: 'By priority' },
  ];

  return (
    <div className="p-6 space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Moderator Dashboard</h1>
        <div className="flex items-center gap-3">
          <Link href="/admin/moderation/report" className="text-indigo-600">View Report</Link>
          {!can('moderation') && (
            <div className="text-red-600">You do not have moderation access.</div>
          )}
        </div>
      </div>

      {/* Filters */}
      <div className="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <div>
          <label className="block text-sm">Section</label>
          <select
            value={filters?.type || ''}
            onChange={(e) => changeFilter('type', e.target.value)}
            className="border rounded px-3 py-2 w-full"
          >
            {sections.map((s) => (
              <option key={s.key} value={s.key}>{s.label}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="block text-sm">Status</label>
          <select
            value={filters?.status || 'pending'}
            onChange={(e) => changeFilter('status', e.target.value)}
            className="border rounded px-3 py-2 w-full"
          >
            {statuses.map((s) => (
              <option key={s.key} value={s.key}>{s.label}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="block text-sm">Sort</label>
          <select
            value={filters?.sort || 'date'}
            onChange={(e) => changeFilter('sort', e.target.value)}
            className="border rounded px-3 py-2 w-full"
          >
            {sortOptions.map((s) => (
              <option key={s.key} value={s.key}>{s.label}</option>
            ))}
          </select>
        </div>
        <div>
          <label className="block text-sm">Priority</label>
          <select
            value={filters?.priority ?? ''}
            onChange={(e) => changeFilter('priority', e.target.value)}
            className="border rounded px-3 py-2 w-full"
          >
            <option value="">All</option>
            <option value="2">Urgent</option>
            <option value="1">High</option>
            <option value="0">Normal</option>
          </select>
        </div>
        <div>
          <label className="block text-sm">Assign to moderator</label>
          <select
            value={data.moderator_id}
            onChange={(e) => setData('moderator_id', e.target.value)}
            className="border rounded px-3 py-2 w-full"
            disabled={!isChief}
          >
            <option value="">Select moderator</option>
            {moderators.map((m) => (
              <option key={m.id} value={m.id}>{m.name}</option>
            ))}
          </select>
          {!isChief && (
            <div className="text-xs text-gray-500 mt-1">Only Chief Moderator can assign others.</div>
          )}
        </div>
      </div>

      {/* Actions input */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
          <label className="block text-sm">Comment</label>
          <textarea
            value={data.comment}
            onChange={(e) => setData('comment', e.target.value)}
            className="border rounded px-3 py-2 w-full"
            placeholder="Moderator comment"
          />
        </div>
        <div>
          <label className="block text-sm">Reject reason</label>
          <input
            value={data.reason}
            onChange={(e) => setData('reason', e.target.value)}
            className="border rounded px-3 py-2 w-full"
            placeholder="Provide rejection reason"
          />
        </div>
      </div>

      {/* Tasks table */}
      <div className="overflow-x-auto">
        <table className="min-w-full border">
          <thead>
            <tr className="bg-gray-50">
              <th className="p-2 text-left">Type</th>
              <th className="p-2 text-left">Date</th>
              <th className="p-2 text-left">Author</th>
              <th className="p-2 text-left">Status</th>
              <th className="p-2 text-left">Priority</th>
              <th className="p-2 text-left">Assigned</th>
              <th className="p-2 text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            {tasks?.data?.map((t) => (
              <tr key={t.id} className="border-t">
                <td className="p-2">{t.item_type}</td>
                <td className="p-2">{t.created_at}</td>
                <td className="p-2">{t.submitted_by_name || t.submitted_by}</td>
                <td className="p-2">{t.status}</td>
                <td className="p-2">{t.priority}</td>
                <td className="p-2">{t.moderator_name || '-'}</td>
                <td className="p-2 flex flex-wrap gap-2">
                  <button
                    onClick={() => approve(t.id)}
                    className="px-3 py-1 bg-green-600 text-white rounded"
                    disabled={!can('moderation') || processing}
                  >✅ Approve</button>
                  <button
                    onClick={() => decline(t.id)}
                    className="px-3 py-1 bg-red-600 text-white rounded"
                    disabled={!can('moderation') || processing}
                  >❌ Reject</button>
                  <button
                    onClick={() => requestRevision(t.id)}
                    className="px-3 py-1 bg-yellow-600 text-white rounded"
                    disabled={!can('moderation') || processing}
                  >🔄 Send for revision</button>
                  <button
                    onClick={() => assignSelf(t.id)}
                    className="px-3 py-1 bg-gray-700 text-white rounded"
                    disabled={!can('moderation') || processing}
                  >Assign to self</button>
                  {isChief && (
                    <button
                      onClick={() => assignModerator(t.id)}
                      className="px-3 py-1 bg-indigo-600 text-white rounded"
                      disabled={processing || !data.moderator_id}
                    >Assign to moderator</button>
                  )}
                </td>
              </tr>
            ))}
            {!tasks?.data?.length && (
              <tr>
                <td className="p-3" colSpan={7}>No tasks found.</td>
              </tr>
            )}
          </tbody>
        </table>
      </div>

      {/* Pagination */}
      <div className="flex items-center gap-2">
        {tasks?.links?.map((l, idx) => (
          <Link
            key={idx}
            href={l.url || '#'}
            className={`px-3 py-1 border rounded ${l.active ? 'bg-gray-200' : ''} ${!l.url ? 'opacity-50' : ''}`}
          >{l.label}</Link>
        ))}
      </div>
    </div>
  );
}