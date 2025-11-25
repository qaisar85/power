import React, { useMemo, useState, useEffect } from 'react';
import { Head, usePage, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

const COUNTRIES = [
  'United States','United Kingdom','United Arab Emirates','Saudi Arabia','Germany','France','India','China','Canada','Brazil','Russia','Kazakhstan','Nigeria','South Africa','Spain','Italy','Netherlands','Turkey','Australia','Singapore','Qatar','Egypt','Norway','Mexico'
];

const CITIES = {
  'United States': ['Houston','New York','Los Angeles','Chicago'],
  'United Kingdom': ['London','Manchester','Birmingham'],
  'United Arab Emirates': ['Dubai','Abu Dhabi','Sharjah'],
  'Saudi Arabia': ['Riyadh','Jeddah','Dammam'],
  Germany: ['Berlin','Hamburg','Munich'],
  France: ['Paris','Lyon','Marseille'],
  India: ['Mumbai','Delhi','Chennai','Kolkata'],
  China: ['Shanghai','Beijing','Shenzhen'],
  Canada: ['Toronto','Vancouver','Montreal'],
  Brazil: ['Sao Paulo','Rio de Janeiro'],
  Russia: ['Moscow','Saint Petersburg'],
  Kazakhstan: ['Almaty','Astana'],
  Nigeria: ['Lagos','Abuja'],
  'South Africa': ['Johannesburg','Cape Town'],
  Spain: ['Madrid','Barcelona'],
  Italy: ['Rome','Milan'],
  Netherlands: ['Amsterdam','Rotterdam'],
  Turkey: ['Istanbul','Ankara'],
  Australia: ['Sydney','Melbourne'],
  Singapore: ['Singapore'],
  Qatar: ['Doha'],
  Egypt: ['Cairo','Alexandria'],
  Norway: ['Oslo'],
  Mexico: ['Mexico City','Monterrey'],
};

function CountrySelect({ value, onChange, id }) {
  const [options, setOptions] = useState([]);
  useEffect(() => {
    let cancelled = false;
    fetch('/geo/countries')
      .then(res => res.json())
      .then(data => {
        if (!cancelled) setOptions((data || []).map(c => c.name || c));
      })
      .catch(() => setOptions([]));
    return () => { cancelled = true; };
  }, []);
  return (
    <select id={id} className="mt-1 w-full border rounded px-3 py-2" value={value} onChange={(e) => onChange(e.target.value)}>
      <option value="">Select country…</option>
      {options.map(c => <option key={`country-${c}`} value={c}>{c}</option>)}
    </select>
  );
}

function CitySelect({ country, value, onChange, id }) {
  const [options, setOptions] = useState([]);
  useEffect(() => {
    let cancelled = false;
    if (!country) { setOptions([]); return; }
    fetch(`/geo/cities?country=${encodeURIComponent(country)}`)
      .then(res => res.json())
      .then(data => { if (!cancelled) setOptions(Array.isArray(data) ? data : []); })
      .catch(() => setOptions([]));
    return () => { cancelled = true; };
  }, [country]);
  if (options.length) {
    return (
      <select id={id} className="mt-1 w-full border rounded px-3 py-2" value={value} onChange={(e) => onChange(e.target.value)}>
        <option value="">Select city…</option>
        {options.map(c => <option key={`city-${country}-${c}`} value={c}>{c}</option>)}
      </select>
    );
  }
  return (
    <input id={id} className="mt-1 w-full border rounded px-3 py-2" value={value} onChange={(e) => onChange(e.target.value)} placeholder="City" />
  );
}

export default function LogisticsRoutesIndex() {
  const { company, routes, services, needs_company } = usePage().props;
  const [adding, setAdding] = useState(false);
  const [editingRoute, setEditingRoute] = useState(null);
  const [calcOpen, setCalcOpen] = useState({});
  const [calcValues, setCalcValues] = useState({});

  // Create Route form
  const form = useForm({
    service_id: '',
    transport_type: 'road',
    from_country: '',
    from_city: '',
    to_country: '',
    to_city: '',
    frequency: '',
    timeline_days: '',
    price_per_kg: '',
    price_per_ton: '',
    price_per_container: '',
    conditions: '',
    documents: [],
  });

  const submit = (e) => {
    e.preventDefault();
    form.post(route('logistics.routes.store'), {
      onSuccess: () => {
        setAdding(false);
        form.reset();
      },
      forceFormData: true,
    });
  };

  // Edit Route form
  const editForm = useForm({
    service_id: '',
    transport_type: 'road',
    from_country: '',
    from_city: '',
    to_country: '',
    to_city: '',
    frequency: '',
    timeline_days: '',
    price_per_kg: '',
    price_per_ton: '',
    price_per_container: '',
    conditions: '',
    documents: [],
  });

  const openEdit = (r) => {
    setEditingRoute(r);
    editForm.setData({
      service_id: r.service_id || '',
      transport_type: r.transport_type || 'road',
      from_country: r.from_country || '',
      from_city: r.from_city || '',
      to_country: r.to_country || '',
      to_city: r.to_city || '',
      frequency: r.frequency || '',
      timeline_days: r.timeline_days || '',
      price_per_kg: r.price_per_kg || '',
      price_per_ton: r.price_per_ton || '',
      price_per_container: r.price_per_container || '',
      conditions: r.conditions || '',
      documents: [],
    });
  };

  const submitEdit = (e) => {
    e.preventDefault();
    if (!editingRoute) return;
    editForm.post(route('logistics.routes.update', editingRoute.id), {
      onSuccess: () => setEditingRoute(null),
      forceFormData: true,
      preserveScroll: true,
      method: 'put',
    });
  };

  const delForm = useForm({});
  const deleteRoute = (r) => {
    if (!confirm('Delete this route?')) return;
    delForm.delete(route('logistics.routes.destroy', r.id), {
      preserveScroll: true,
    });
  };
  const deleteDoc = (r, i) => {
    if (!confirm('Remove this document?')) return;
    delForm.delete(route('logistics.routes.documents.destroy', { route: r.id, index: i }), {
      preserveScroll: true,
      onSuccess: () => {
        if (editingRoute && editingRoute.id === r.id) {
          setEditingRoute(er => ({ ...er, documents: (er.documents || []).filter((_, idx) => idx !== i) }));
        }
      },
    });
  };

  const compute = (r, vals) => {
    const w = parseFloat(vals.weightKg || 0);
    const c = parseFloat(vals.containers || 0);
    const perKg = r.price_per_kg ? (w > 0 ? (parseFloat(r.price_per_kg) * w) : null) : null;
    const perTon = r.price_per_ton ? (w > 0 ? (parseFloat(r.price_per_ton) * (w / 1000)) : null) : null;
    const perCont = r.price_per_container ? (c > 0 ? (parseFloat(r.price_per_container) * c) : null) : null;
    return { perKg, perTon, perCont };
  };

  return (
    <AppLayout title="Logistics — Routes">
      <Head title={`Logistics Routes — ${company?.name || ''}`} />
      <div className="max-w-6xl mx-auto py-8 px-4">
        <div className="flex items-center justify-between mb-4">
          <h1 className="text-xl font-semibold">Routes</h1>
          <button onClick={() => setAdding(v => !v)} className="bg-blue-600 text-white px-3 py-2 rounded text-sm" disabled={needs_company} title={needs_company ? 'Connect a company to add routes' : ''}>
            {adding ? 'Close' : 'Add Route'}
          </button>
        </div>

        {needs_company && (
          <div className="bg-yellow-50 border border-yellow-200 rounded p-4 mb-6">
            <div className="font-semibold text-yellow-800">No company connected</div>
            <div className="text-sm text-yellow-700">Connect a company to manage routes.</div>
          </div>
        )}

        {adding && !needs_company && (
          <form onSubmit={submit} className="bg-white rounded shadow p-4 mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium">Service (optional)</label>
              <select className="mt-1 w-full border rounded px-3 py-2" value={form.data.service_id} onChange={(e) => form.setData('service_id', e.target.value)}>
                <option value="">—</option>
                {(services || []).map(s => (
                  <option key={`svc-${s.id}`} value={s.id}>{s.title}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">Transport Type</label>
              <select className="mt-1 w-full border rounded px-3 py-2" value={form.data.transport_type} onChange={(e) => form.setData('transport_type', e.target.value)}>
                <option value="road">Road</option>
                <option value="air">Air</option>
                <option value="sea">Sea</option>
                <option value="rail">Rail</option>
                <option value="warehousing">Warehousing</option>
                <option value="customs">Customs</option>
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium">From Country</label>
              <CountrySelect id="from_country" value={form.data.from_country} onChange={(v) => form.setData('from_country', v)} />
            </div>
            <div>
              <label className="block text-sm font-medium">From City</label>
              <CitySelect id="from_city" country={form.data.from_country} value={form.data.from_city} onChange={(v) => form.setData('from_city', v)} />
            </div>
            <div>
              <label className="block text-sm font-medium">To Country</label>
              <CountrySelect id="to_country" value={form.data.to_country} onChange={(v) => form.setData('to_country', v)} />
            </div>
            <div>
              <label className="block text-sm font-medium">To City</label>
              <CitySelect id="to_city" country={form.data.to_country} value={form.data.to_city} onChange={(v) => form.setData('to_city', v)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Frequency</label>
              <input className="mt-1 w-full border rounded px-3 py-2" value={form.data.frequency} onChange={(e) => form.setData('frequency', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Timeline (days)</label>
              <input type="number" className="mt-1 w-full border rounded px-3 py-2" value={form.data.timeline_days} onChange={(e) => form.setData('timeline_days', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Price per kg</label>
              <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={form.data.price_per_kg} onChange={(e) => form.setData('price_per_kg', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Price per ton</label>
              <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={form.data.price_per_ton} onChange={(e) => form.setData('price_per_ton', e.target.value)} />
            </div>
            <div>
              <label className="block text-sm font-medium">Price per container</label>
              <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={form.data.price_per_container} onChange={(e) => form.setData('price_per_container', e.target.value)} />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium">Conditions</label>
              <textarea className="mt-1 w-full border rounded px-3 py-2" value={form.data.conditions} onChange={(e) => form.setData('conditions', e.target.value)} />
            </div>
            <div className="md:col-span-2">
              <label className="block text-sm font-medium">Documents</label>
              <input type="file" multiple className="mt-1 w-full" onChange={(e) => form.setData('documents', Array.from(e.target.files || []))} />
            </div>
            <div className="md:col-span-2 flex items-center justify-end gap-2">
              <button type="submit" className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700" disabled={form.processing}>Create</button>
            </div>
          </form>
        )}

        {/* Routes table */}
        <div className="bg-white rounded shadow overflow-x-auto">
          <table className="min-w-full text-sm">
            <thead>
              <tr className="border-b">
                <th className="text-left p-3">Type</th>
                <th className="text-left p-3">From</th>
                <th className="text-left p-3">To</th>
                <th className="text-left p-3">Frequency</th>
                <th className="text-left p-3">Timeline</th>
                <th className="text-left p-3">Tariffs</th>
                <th className="text-left p-3">Actions</th>
              </tr>
            </thead>
            <tbody>
              {(routes || []).length ? routes.map((r) => {
                const vals = calcValues[r.id] || { weightKg: '', containers: '' };
                const estimates = compute(r, vals);
                return (
                  <tr key={`r-${r.id}`} className="border-b align-top">
                    <td className="p-3 capitalize">{r.transport_type}</td>
                    <td className="p-3">{r.from_city ? `${r.from_city}, ` : ''}{r.from_country}</td>
                    <td className="p-3">{r.to_city ? `${r.to_city}, ` : ''}{r.to_country}</td>
                    <td className="p-3">{r.frequency || '—'}</td>
                    <td className="p-3">{r.timeline_days ? `${r.timeline_days} days` : '—'}</td>
                    <td className="p-3">
                      <div className="text-xs text-gray-700 space-y-1">
                        {r.price_per_kg ? (<div>Per kg: <span className="font-medium">{r.price_per_kg} USD</span></div>) : null}
                        {r.price_per_ton ? (<div>Per ton: <span className="font-medium">{r.price_per_ton} USD</span></div>) : null}
                        {r.price_per_container ? (<div>Per container: <span className="font-medium">{r.price_per_container} USD</span></div>) : null}
                        {!r.price_per_kg && !r.price_per_ton && !r.price_per_container ? '—' : null}
                      </div>
                      {calcOpen[r.id] && (
                        <div className="mt-3 p-2 border rounded">
                          <div className="grid grid-cols-2 gap-2">
                            <div>
                              <label className="block text-xs font-medium">Weight (kg)</label>
                              <input type="number" className="mt-1 w-full border rounded px-2 py-1" value={vals.weightKg} onChange={(e) => setCalcValues(v => ({...v, [r.id]: { ...v[r.id], weightKg: e.target.value }}))} />
                            </div>
                            <div>
                              <label className="block text-xs font-medium">Containers</label>
                              <input type="number" className="mt-1 w-full border rounded px-2 py-1" value={vals.containers} onChange={(e) => setCalcValues(v => ({...v, [r.id]: { ...v[r.id], containers: e.target.value }}))} />
                            </div>
                          </div>
                          <div className="mt-2 text-xs text-gray-700 space-y-1">
                            {estimates.perKg != null && (<div>Est. by kg: <span className="font-semibold">{estimates.perKg.toFixed(2)} USD</span></div>)}
                            {estimates.perTon != null && (<div>Est. by ton: <span className="font-semibold">{estimates.perTon.toFixed(2)} USD</span></div>)}
                            {estimates.perCont != null && (<div>Est. by container: <span className="font-semibold">{estimates.perCont.toFixed(2)} USD</span></div>)}
                            {estimates.perKg == null && estimates.perTon == null && estimates.perCont == null && (
                              <div className="text-gray-500">No tariff data available.</div>
                            )}
                          </div>
                        </div>
                      )}
                    </td>
                    <td className="p-3">
                      <div className="flex items-center gap-2">
                        <button className="px-2 py-1 text-xs rounded bg-slate-100 hover:bg-slate-200" onClick={() => openEdit(r)}>Edit</button>
                        <button className="px-2 py-1 text-xs rounded bg-red-600 text-white hover:bg-red-700" onClick={() => deleteRoute(r)}>Delete</button>
                        <button className="px-2 py-1 text-xs rounded bg-emerald-100 hover:bg-emerald-200" onClick={() => setCalcOpen(v => ({...v, [r.id]: !v[r.id]}))}>{calcOpen[r.id] ? 'Hide Calc' : 'Calculator'}</button>
                      </div>
                      {Array.isArray(r.documents) && r.documents.length > 0 && (
                         <div className="mt-2 text-xs text-gray-600">
                           Docs: {r.documents.map((d, i) => (
                             <span key={`doc-${r.id}-${i}`} className="inline-flex items-center mr-2">
                               <a href={d.path ? `/storage/${d.path}` : '#'} target="_blank" rel="noreferrer" className="underline">{d.original || 'file'}</a>
                               <button type="button" className="ml-1 text-red-600 hover:text-red-800" title="Remove" onClick={() => deleteDoc(r, i)}>×</button>
                             </span>
                           ))}
                         </div>
                       )}
                    </td>
                  </tr>
                );
              }) : (
                <tr>
                  <td className="p-4 text-center text-gray-500" colSpan={7}>No routes yet.</td>
                </tr>
              )}
            </tbody>
          </table>
        </div>
      </div>

      {/* Edit drawer/modal */}
      {editingRoute && (
        <div className="fixed inset-0 bg-black/40 flex items-center justify-center z-50" onClick={() => setEditingRoute(null)}>
          <div className="bg-white rounded shadow-xl w-full max-w-2xl p-4" onClick={(e) => e.stopPropagation()}>
            <div className="flex items-center justify-between mb-3">
              <div className="font-semibold">Edit Route</div>
              <button className="text-sm" onClick={() => setEditingRoute(null)}>Close</button>
            </div>
            <form onSubmit={submitEdit} className="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label className="block text-sm font-medium">Service (optional)</label>
                <select className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.service_id} onChange={(e) => editForm.setData('service_id', e.target.value)}>
                  <option value="">—</option>
                  {(services || []).map(s => (
                    <option key={`svc-edit-${s.id}`} value={s.id}>{s.title}</option>
                  ))}
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium">Transport Type</label>
                <select className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.transport_type} onChange={(e) => editForm.setData('transport_type', e.target.value)}>
                  <option value="road">Road</option>
                  <option value="air">Air</option>
                  <option value="sea">Sea</option>
                  <option value="rail">Rail</option>
                  <option value="warehousing">Warehousing</option>
                  <option value="customs">Customs</option>
                </select>
              </div>
              <div>
                <label className="block text-sm font-medium">From Country</label>
                <CountrySelect id="edit_from_country" value={editForm.data.from_country} onChange={(v) => editForm.setData('from_country', v)} />
              </div>
              <div>
                <label className="block text-sm font-medium">From City</label>
                <CitySelect id="edit_from_city" country={editForm.data.from_country} value={editForm.data.from_city} onChange={(v) => editForm.setData('from_city', v)} />
              </div>
              <div>
                <label className="block text-sm font-medium">To Country</label>
                <CountrySelect id="edit_to_country" value={editForm.data.to_country} onChange={(v) => editForm.setData('to_country', v)} />
              </div>
              <div>
                <label className="block text-sm font-medium">To City</label>
                <CitySelect id="edit_to_city" country={editForm.data.to_country} value={editForm.data.to_city} onChange={(v) => editForm.setData('to_city', v)} />
              </div>
              <div>
                <label className="block text-sm font-medium">Frequency</label>
                <input className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.frequency} onChange={(e) => editForm.setData('frequency', e.target.value)} />
              </div>
              <div>
                <label className="block text-sm font-medium">Timeline (days)</label>
                <input type="number" className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.timeline_days} onChange={(e) => editForm.setData('timeline_days', e.target.value)} />
              </div>
              <div>
                <label className="block text-sm font-medium">Price per kg</label>
                <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.price_per_kg} onChange={(e) => editForm.setData('price_per_kg', e.target.value)} />
              </div>
              <div>
                <label className="block text-sm font-medium">Price per ton</label>
                <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.price_per_ton} onChange={(e) => editForm.setData('price_per_ton', e.target.value)} />
              </div>
              <div>
                <label className="block text-sm font-medium">Price per container</label>
                <input type="number" step="0.01" className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.price_per_container} onChange={(e) => editForm.setData('price_per_container', e.target.value)} />
              </div>
              <div className="md:col-span-2">
                <label className="block text-sm font-medium">Conditions</label>
                <textarea className="mt-1 w-full border rounded px-3 py-2" value={editForm.data.conditions} onChange={(e) => editForm.setData('conditions', e.target.value)} />
              </div>
              <div className="md:col-span-2">
                 <label className="block text-sm font-medium">Add Documents</label>
                 <input type="file" multiple className="mt-1 w-full" onChange={(e) => editForm.setData('documents', Array.from(e.target.files || []))} />
                 <div className="text-xs text-gray-600 mt-1">Existing documents remain; new uploads are appended.</div>
               </div>
               {editingRoute && Array.isArray(editingRoute.documents) && editingRoute.documents.length > 0 && (
                 <div className="md:col-span-2">
                   <label className="block text-sm font-medium">Existing Documents</label>
                   <ul className="mt-1 text-xs text-gray-600">
                     {editingRoute.documents.map((d, i) => (
                       <li key={`edit-doc-${editingRoute.id}-${i}`} className="flex items-center gap-2">
                         <a href={d.path ? `/storage/${d.path}` : '#'} target="_blank" rel="noreferrer" className="underline">{d.original || 'file'}</a>
                         <button type="button" className="px-2 py-1 rounded bg-red-600 text-white hover:bg-red-700" onClick={() => deleteDoc(editingRoute, i)}>Remove</button>
                       </li>
                     ))}
                   </ul>
                 </div>
               )}
               <div className="md:col-span-2 flex items-center justify-end gap-2">
                 <button type="submit" className="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700" disabled={editForm.processing}>Save</button>
               </div>
            </form>
          </div>
        </div>
      )}
    </AppLayout>
  );
}