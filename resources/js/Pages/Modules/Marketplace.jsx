import React, { useState } from 'react';
import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Search, Filter, MapPin, Star, Briefcase } from 'lucide-react';

export default function Marketplace({ module, config, filters = {}, results = { data: [], links: [] }, categories = [], countries = [] }) {
    const [form, setForm] = useState({
        q: filters.q || '',
        category: filters.category || '',
        country: filters.country || '',
        type: filters.type || 'product',
        min_price: filters.min_price || '',
        max_price: filters.max_price || '',
        sort: filters.sort || 'latest',
    });

    const listings = results?.data || [];
    const links = results?.links || [];

    const categoryOptions = [{ name: 'All', value: '' },
        ...categories.flatMap((s) => [
            { name: s.name, value: s.name },
            ...(s.subs || []).map((sub) => ({ name: `${s.name} › ${sub.name}`, value: sub.name }))
        ])
    ];

    const onChange = (e) => setForm((f) => ({ ...f, [e.target.name]: e.target.value }));
    // Accept optional overrides to avoid stale state during quick actions
    const applyFilters = (overrides = {}) => {
        const payload = { ...form, ...overrides };
        router.get('/marketplace', payload, { preserveState: true, preserveScroll: true, replace: true });
    };

    return (
        <AppLayout title="Global Marketplace">
            <Head>
                <meta name="description" content="Find global businesses, products, and services." />
                <meta property="og:title" content="Global Marketplace" />
                <meta property="og:type" content="website" />
            </Head>
            <div className="py-6">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    {/* Header */}
                    <div className="mb-8">
                        <h1 className="text-3xl font-bold text-gray-900">Global Business Marketplace</h1>
                        <p className="mt-2 text-gray-600">
                            Connect with businesses worldwide across all sectors
                        </p>
                    </div>

                    {/* Search and Filters */}
                    <div className="mb-8 bg-white shadow rounded-lg p-6">
                        <div className="flex flex-col lg:flex-row gap-4">
                            <div className="flex-1">
                                <div className="relative">
                                    <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                    <input
                                        type="text"
                                        name="q"
                                        placeholder="Search businesses, products, services..."
                                        className="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                        value={form.q}
                                        onChange={onChange}
                                        onKeyDown={(e) => e.key === 'Enter' && applyFilters()}
                                    />
                                </div>
                            </div>
                            <div className="lg:w-48">
                                <select
                                    name="category"
                                    className="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    value={form.category}
                                    onChange={onChange}
                                >
                                    {categoryOptions.map((c) => (
                                        <option key={c.name} value={c.value}>
                                            {c.name}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="lg:w-48">
                                <select
                                    name="country"
                                    className="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    value={form.country}
                                    onChange={onChange}
                                >
                                    <option value="">All Countries</option>
                                    {countries.map((c) => (
                                        <option key={c} value={c}>{c}</option>
                                    ))}
                                </select>
                            </div>
                            <div className="lg:w-36">
                                <select
                                    name="type"
                                    className="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    value={form.type}
                                    onChange={onChange}
                                >
                                    <option value="product">Products</option>
                                    <option value="service">Services</option>
                                </select>
                            </div>
                            <div className="lg:w-36">
                                <select
                                    name="sort"
                                    className="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                    value={form.sort}
                                    onChange={onChange}
                                >
                                    <option value="latest">Latest</option>
                                    <option value="price_asc">Price: Low → High</option>
                                    <option value="price_desc">Price: High → Low</option>
                                </select>
                            </div>
                            <button
                                className="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-white bg-indigo-600 hover:bg-indigo-700"
                                onClick={() => applyFilters()}
                            >
                                <Filter className="h-4 w-4 mr-2" />
                                Apply
                            </button>
                        </div>
                        <div className="mt-4 grid grid-cols-2 gap-2">
                            <input
                                type="number"
                                name="min_price"
                                placeholder="Min price"
                                className="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                value={form.min_price}
                                onChange={onChange}
                            />
                            <input
                                type="number"
                                name="max_price"
                                placeholder="Max price"
                                className="w-full py-2 px-3 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500"
                                value={form.max_price}
                                onChange={onChange}
                            />
                        </div>
                        {/* Quick price presets */}
                        <div className="mt-3 flex flex-wrap gap-2">
                            <button type="button" className="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-100" onClick={() => { setForm((f) => ({ ...f, min_price: '', max_price: 100 })); applyFilters({ min_price: '', max_price: 100 }); }}>
                                Under $100
                            </button>
                            <button type="button" className="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-100" onClick={() => { setForm((f) => ({ ...f, min_price: '', max_price: 500 })); applyFilters({ min_price: '', max_price: 500 }); }}>
                                Under $500
                            </button>
                            <button type="button" className="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-100" onClick={() => { setForm((f) => ({ ...f, min_price: '', max_price: 1000 })); applyFilters({ min_price: '', max_price: 1000 }); }}>
                                Under $1000
                            </button>
                            <button type="button" className="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-100" onClick={() => { setForm((f) => ({ ...f, min_price: 5000, max_price: '' })); applyFilters({ min_price: 5000, max_price: '' }); }}>
                                Over $5000
                            </button>
                            <button type="button" className="px-3 py-1 text-sm rounded border border-gray-300 hover:bg-gray-100" onClick={() => { setForm((f) => ({ ...f, min_price: '', max_price: '' })); applyFilters({ min_price: '', max_price: '' }); }}>
                                Clear price
                            </button>
                        </div>
                    </div>

                    {/* Results */}
                    <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        {/* Listings */}
                        <div className="lg:col-span-2">
                            {listings.length === 0 && (
                                <div className="bg-white shadow rounded-lg p-6 text-gray-600">No results found.</div>
                            )}
                            <div className="space-y-6">
                                {listings.map((l) => (
                                    <div key={l.id} className="bg-white shadow rounded-lg p-6">
                                        <div className="flex items-start justify-between">
                                            <div className="flex-1">
                                                <div className="flex items-center">
                                                    <h3 className="text-lg font-medium text-gray-900">
                                                        {l.title}
                                                    </h3>
                                                </div>
                                                <div className="mt-1 flex items-center text-sm text-gray-500">
                                                    <MapPin className="h-4 w-4 mr-1" />
                                                    {l.location || '—'}
                                                    <span className="mx-2">•</span>
                                                    <span className="bg-gray-100 px-2 py-1 rounded text-xs">
                                                        {l.category || 'Uncategorized'}
                                                    </span>
                                                </div>
                                                <div className="mt-2 text-gray-600">
                                                    <span className="font-semibold">
                                                        {typeof l.price === 'number' ? l.price.toLocaleString() : l.price} {l.currency}
                                                    </span>
                                                </div>
                                            </div>
                                            <div className="ml-4">
                                                <button className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                                    <Briefcase className="h-4 w-4 mr-2" />
                                                    View
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* Pagination */}
                            {links.length > 0 && (
                                <div className="mt-6 flex items-center gap-2">
                                    {links.map((link, idx) => (
                                        <Link
                                            key={idx}
                                            href={link.url || '#'}
                                            preserveScroll
                                            preserveState
                                            className={`px-3 py-1 rounded border ${link.active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300'} ${!link.url ? 'opacity-50 cursor-not-allowed' : ''}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </div>
                            )}
                        </div>

                        {/* Sidebar */}
                        <div className="space-y-6">
                            {/* Featured Categories */}
                            <div className="bg-white shadow rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Featured Categories
                                </h3>
                                <div className="space-y-3">
                                    {categoryOptions.slice(1, 6).map((c) => (
                                        <div key={c.name} className="flex items-center justify-between">
                                            <button
                                                className="text-sm text-gray-600 hover:text-indigo-600"
                                                onClick={() => { setForm((f) => ({ ...f, category: c.value })); applyFilters({ category: c.value }); }}
                                            >
                                                {c.name}
                                            </button>
                                            <span className="text-xs text-gray-400">•</span>
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Quick Stats */}
                            <div className="bg-white shadow rounded-lg p-6">
                                <h3 className="text-lg font-medium text-gray-900 mb-4">
                                    Platform Stats
                                </h3>
                                <div className="space-y-3">
                                    <div className="flex justify-between">
                                        <span className="text-sm text-gray-600">Total Listings</span>
                                        <span className="text-sm font-medium">{results?.total ?? listings.length}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-gray-600">Countries</span>
                                        <span className="text-sm font-medium">{countries.length}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-gray-600">Active Today</span>
                                        <span className="text-sm font-medium">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}