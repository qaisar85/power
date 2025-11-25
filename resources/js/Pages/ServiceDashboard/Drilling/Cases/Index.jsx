import React, { useMemo, useState } from 'react';
import { useForm, router, Link } from '@inertiajs/react';

export default function Index({ company, cases, rigs, filters }) {
  if (!company) {
    return (
      <div className="p-6">
        <div className="bg-yellow-50 border border-yellow-200 rounded p-4">
          <div className="font-semibold text-yellow-800">No company connected</div>
          <div className="text-sm text-yellow-700">Your account is not connected to a service company. Connect a company to manage drilling cases.</div>
          <div className="mt-3">
            <Link className="btn" href={route('service.profile.show')}>Connect Company</Link>
          </div>
        </div>
      </div>
    );
  }
  const { data, setData, post, processing, reset, errors } = useForm({
    title: '',
    client: '',
    region: '',
    method: '',
    depth: '',
    rig_id: '',
    start_date: '',
    end_date: '',
    status: 'planned',
    summary: '',
    tags: '',
    photos: [],
    documents: [],
  });

  const [editingId, setEditingId] = useState(null);
  const [editValues, setEditValues] = useState({
    title: '',
    client: '',
    region: '',
    method: '',
    depth: '',
    rig_id: '',
    start_date: '',
    end_date: '',
    status: '',
    summary: '',
    tags: '',
    photos: [],
    documents: [],
  });

  const [tagFilter, setTagFilter] = useState((filters?.tags || []).join(','));
  const filteredCases = useMemo(() => {
    const tags = tagFilter
      .split(',')
      .map((t) => t.trim())
      .filter((t) => t.length > 0);
    if (tags.length === 0) return cases;
    return cases.filter((c) => {
      const ct = (c.tags || []).map((t) => (t || '').toLowerCase());
      return tags.every((t) => ct.includes(t.toLowerCase()));
    });
  }, [cases, tagFilter]);

  const startEdit = (c) => {
    setEditingId(c.id);
    setEditValues({
      title: c.title || '',
      client: c.client || '',
      region: c.region || '',
      method: c.method || '',
      depth: c.depth || '',
      rig_id: c.rig_id || '',
      start_date: c.start_date || '',
      end_date: c.end_date || '',
      status: c.status || 'planned',
      summary: c.summary || '',
      tags: (c.tags || []).join(','),
      photos: [],
      documents: [],
    });
  };

  const cancelEdit = () => {
    setEditingId(null);
    setEditValues({
      title: '',
      client: '',
      region: '',
      method: '',
      depth: '',
      rig_id: '',
      start_date: '',
      end_date: '',
      status: '',
      summary: '',
      tags: '',
      photos: [],
      documents: [],
    });
  };

  const saveEdit = async () => {
    const formData = new FormData();
    Object.entries(editValues).forEach(([k, v]) => {
      if (k === 'photos' || k === 'documents') {
        for (let i = 0; i < (v?.length || 0); i++) {
          formData.append(`${k}[${i}]`, v[i]);
        }
      } else if (v !== null && v !== undefined) {
        formData.append(k, v);
      }
    });
    await router.put(`/service-dashboard/drilling/cases/${editingId}`, formData, {
      onSuccess: () => cancelEdit(),
    });
  };

  const submit = (e) => {
    e.preventDefault();
    const formData = new FormData();
    Object.entries(data).forEach(([k, v]) => {
      if (k === 'photos' || k === 'documents') {
        for (let i = 0; i < (v?.length || 0); i++) {
          formData.append(`${k}[${i}]`, v[i]);
        }
      } else {
        formData.append(k, v ?? '');
      }
    });
    post('/service-dashboard/drilling/cases', {
      data: formData,
      onSuccess: () => reset(),
    });
  };

  return (
    <div className="p-6 space-y-6">
      <h1 className="text-2xl font-semibold">Drilling Cases</h1>

      <div className="bg-white rounded shadow p-4 space-y-3">
        <h2 className="font-semibold">Create Case</h2>
        <form onSubmit={submit} className="grid grid-cols-1 md:grid-cols-2 gap-3">
          <input className="input" placeholder="Title" value={data.title} onChange={(e) => setData('title', e.target.value)} />
          <input className="input" placeholder="Client" value={data.client} onChange={(e) => setData('client', e.target.value)} />
          <input className="input" placeholder="Region" value={data.region} onChange={(e) => setData('region', e.target.value)} />
          <input className="input" placeholder="Method" value={data.method} onChange={(e) => setData('method', e.target.value)} />
          <input className="input" placeholder="Depth (m)" type="number" value={data.depth} onChange={(e) => setData('depth', e.target.value)} />
          <select className="input" value={data.rig_id} onChange={(e) => setData('rig_id', e.target.value)}>
            <option value="">Rig</option>
            {rigs.map((r) => (
              <option key={r.id} value={r.id}>{r.name}</option>
            ))}
          </select>
          <input className="input" type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
          <input className="input" type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
          <select className="input" value={data.status} onChange={(e) => setData('status', e.target.value)}>
            <option value="planned">Planned</option>
            <option value="in_progress">In Progress</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
          <textarea className="input md:col-span-2" placeholder="Summary" value={data.summary} onChange={(e) => setData('summary', e.target.value)} />
          <input className="input md:col-span-2" placeholder="Tags (comma separated)" value={data.tags} onChange={(e) => setData('tags', e.target.value)} />
          <div className="md:col-span-1">
            <label className="block text-sm mb-1">Photos</label>
            <input type="file" multiple accept="image/*" onChange={(e) => setData('photos', e.target.files)} />
          </div>
          <div className="md:col-span-1">
            <label className="block text-sm mb-1">Documents (PDF)</label>
            <input type="file" multiple accept="application/pdf" onChange={(e) => setData('documents', e.target.files)} />
          </div>
          <button className="btn md:col-span-2" disabled={processing || !company} title={!company ? 'Connect a company to create cases' : undefined}>Create</button>
        </form>
      </div>

      <div className="bg-white rounded shadow p-4 space-y-3">
        <div className="flex items-center gap-2">
          <input className="input" placeholder="Filter by tags (comma separated)" value={tagFilter} onChange={(e) => setTagFilter(e.target.value)} />
          <button className="btn" onClick={() => router.get('/service-dashboard/drilling/cases', { tags: tagFilter })}>Apply</button>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          {filteredCases.map((c) => (
            <div key={c.id} className="border rounded p-3 space-y-2">
              {editingId === c.id ? (
                <div className="space-y-2">
                  <input className="input" placeholder="Title" value={editValues.title} onChange={(e) => setEditValues({ ...editValues, title: e.target.value })} />
                  <input className="input" placeholder="Client" value={editValues.client} onChange={(e) => setEditValues({ ...editValues, client: e.target.value })} />
                  <input className="input" placeholder="Region" value={editValues.region} onChange={(e) => setEditValues({ ...editValues, region: e.target.value })} />
                  <input className="input" placeholder="Method" value={editValues.method} onChange={(e) => setEditValues({ ...editValues, method: e.target.value })} />
                  <input className="input" type="number" placeholder="Depth (m)" value={editValues.depth} onChange={(e) => setEditValues({ ...editValues, depth: e.target.value })} />
                  <select className="input" value={editValues.rig_id} onChange={(e) => setEditValues({ ...editValues, rig_id: e.target.value })}>
                    <option value="">Rig</option>
                    {rigs.map((r) => (
                      <option key={r.id} value={r.id}>{r.name}</option>
                    ))}
                  </select>
                  <input className="input" type="date" value={editValues.start_date} onChange={(e) => setEditValues({ ...editValues, start_date: e.target.value })} />
                  <input className="input" type="date" value={editValues.end_date} onChange={(e) => setEditValues({ ...editValues, end_date: e.target.value })} />
                  <select className="input" value={editValues.status} onChange={(e) => setEditValues({ ...editValues, status: e.target.value })}>
                    <option value="planned">Planned</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                  </select>
                  <textarea className="input" placeholder="Summary" value={editValues.summary} onChange={(e) => setEditValues({ ...editValues, summary: e.target.value })} />
                  <input className="input" placeholder="Tags (comma separated)" value={editValues.tags} onChange={(e) => setEditValues({ ...editValues, tags: e.target.value })} />
                  <div>
                    <label className="block text-sm mb-1">Append Photos</label>
                    <input type="file" multiple accept="image/*" onChange={(e) => setEditValues({ ...editValues, photos: e.target.files })} />
                  </div>
                  <div>
                    <label className="block text-sm mb-1">Append Documents (PDF)</label>
                    <input type="file" multiple accept="application/pdf" onChange={(e) => setEditValues({ ...editValues, documents: e.target.files })} />
                  </div>
                  <div className="flex gap-2">
                    <button className="btn" onClick={saveEdit} disabled={!company} title={!company ? 'Connect a company to edit cases' : undefined}>Save</button>
                    <button className="btn btn-outline" onClick={cancelEdit}>Cancel</button>
                  </div>
                </div>
              ) : (
                <div className="space-y-2">
                  <div className="font-semibold">{c.title}</div>
                  <div className="text-sm text-gray-600">{c.client} • {c.region} • {c.method}</div>
                  <div className="text-sm">Depth: {c.depth || '-'} m</div>
                  <div className="text-sm">Dates: {c.start_date || '-'} → {c.end_date || '-'}</div>
                  <div className="text-sm">Status: {c.status || '-'}</div>
                  {c.verified && (
                    <div className="text-xs inline-block px-2 py-1 bg-green-100 text-green-700 rounded">Verified</div>
                  )}
                  {Array.isArray(c.tags) && c.tags.length > 0 && (
                    <div className="flex flex-wrap gap-1">
                      {c.tags.map((t, i) => (
                        <span key={i} className="px-2 py-1 bg-blue-100 text-blue-700 text-xs rounded">{t}</span>
                      ))}
                    </div>
                  )}
                  <p className="text-sm whitespace-pre-line">{c.summary}</p>
                  <div className="flex gap-2">
                    <button className="btn" onClick={() => startEdit(c)} disabled={!company} title={!company ? 'Connect a company to edit cases' : undefined}>Edit</button>
                  </div>
                </div>
              )}
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}