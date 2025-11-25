import React, { useMemo, useState, useEffect } from 'react';
import { Head, useForm, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';

export default function Wizard({ roles = [], categories = [], packages = [] }) {
    const steps = [
        'Select Role',
        'Moderation Check',
        'Select Posting Type',
        'Select Category',
        'Details',
        'Preview',
        'Package & Payment'
    ];

    const [currentStep, setCurrentStep] = useState(0);
    const [photoPreviews, setPhotoPreviews] = useState([]);
    const [customPayment, setCustomPayment] = useState('');

    const form = useForm({
        role: roles[0] || 'Company',
        type: 'product',
        title: '',
        description: '',
        price: '',
        currency: 'USD',
        status: 'under_review',
        location: '',
        deal_type: 'sale',
        payment_options: [],
        category: '',
        subcategories: [],
        publish_in_rent: false,
        publish_in_auction: false,
        rent_fields: {
            rent_price: '', rent_currency: 'USD', rent_duration: '', rent_terms: '',
            min_period: '',
            rental_price_day: '',
            rental_price_week: '',
            rental_price_month: '',
            rental_price_year: '',
            request_countries: [],
            additional_description: '',
        },
        auction_fields: {
            start_price: '', auction_currency: 'USD', start_date: '', end_date: '', deposit: '', terms: '',
            buy_now_price: '',
            min_step: 1,
            time_limit_minutes: '',
        },
        logistics_fields: { dimensions: '', route: '', transport_type: '' },
        product_fields: {
            condition: 'new',
            manufacturer: '',
            model: '',
            year: '',
            availability_status: 'in_stock',
            placement_type: 'paid',
            placement_term: 30,
        },
        photos: [],
        rent_documents: [],
        preview_comment: '',
        package: '',
    });

    // Prefill demo data and submit
    const prefillAndSubmit = (type = 'product') => {
        const demoData = {
            role: 'Company',
            type,
            title: `Demo ${type.charAt(0).toUpperCase() + type.slice(1)} – Sample Item`,
            description: 'Demo listing submitted automatically for moderation flow testing.',
            price: 1000,
            currency: 'USD',
            location: 'Houston, TX, USA',
            deal_type: 'sale',
            payment_options: ['bank_transfer'],
            category: categories.includes('Pumps') ? 'Pumps' : (categories[0] || ''),
            subcategories: ['Centrifugal'],
            publish_in_rent: false,
            publish_in_auction: false,
            preview_comment: 'Auto-submitted demo listing for moderation.',
            package: 'vip',
            product_fields: {
                condition: 'used',
                manufacturer: 'ACME Pumps',
                model: 'X100',
                year: '2019',
                availability_status: 'in_stock',
                placement_type: 'paid',
                placement_term: 30,
            },
        };
        Object.entries(demoData).forEach(([key, val]) => form.setData(key, val));
        setCurrentStep(steps.length - 1);
        form.post('/listings', { forceFormData: true });
    };

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        const isDemo = params.get('demo');
        if (isDemo) {
            const type = params.get('type') || 'product';
            prefillAndSubmit(type);
        }
    }, []);

    const canNext = useMemo(() => {
        switch (currentStep) {
            case 0:
                return !!form.data.role;
            case 1:
                return true; // placeholder for moderation gating
            case 2:
                return !!form.data.type;
            case 3:
                return true; // category optional in MVP
            case 4:
                return !!form.data.title && form.data.deal_type === 'sale' ? !!form.data.price : true;
            case 5:
                return true;
            case 6:
                return !!form.data.package;
            default:
                return true;
        }
    }, [currentStep, form.data]);

    const next = () => setCurrentStep((s) => Math.min(s + 1, steps.length - 1));
    const prev = () => setCurrentStep((s) => Math.max(s - 1, 0));
    
    const countryOptions = useMemo(() => [
      { code: 'US', name: 'United States' },
      { code: 'CA', name: 'Canada' },
      { code: 'GB', name: 'United Kingdom' },
      { code: 'DE', name: 'Germany' },
      { code: 'AE', name: 'United Arab Emirates' },
      { code: 'SA', name: 'Saudi Arabia' },
      { code: 'AU', name: 'Australia' },
      { code: 'IN', name: 'India' },
      { code: 'CN', name: 'China' },
      { code: 'RU', name: 'Russia' },
    ], []);
    
    const fieldError = (prefix) => {
      const errs = form.errors || {};
      for (const k in errs) {
        if (k === prefix || k.startsWith(prefix)) return errs[k];
      }
      return null;
    };
    const handleDealTypeChange = (key) => {
      form.setData('deal_type', key);
      if (key === 'wanted') {
        form.setData('type', 'tender');
        form.setData('publish_in_rent', false);
        form.setData('publish_in_auction', false);
        form.setData('payment_options', []);
        form.setData('product_fields', { ...form.data.product_fields, placement_type: 'free' });
      }
    };
    
    const submit = (e) => {
        e.preventDefault();
        const ok = window.confirm('Are you sure you want to submit the listing for moderation?');
        if (!ok) return;
        form.post('/listings', { forceFormData: true });
    };

    return (
        <AppLayout title="Post an Advertisement">
            <Head title="Post an Advertisement" />

            <div className="max-w-5xl mx-auto py-6 px-4">
                {/* Progress */}
                <div className="flex items-center justify-between mb-6">
                    <h1 className="text-2xl font-bold">Unified Wizard</h1>
                    <div className="flex items-center gap-2 text-sm">
                        {steps.map((label, idx) => (
                            <div key={label} className={`px-2 py-1 rounded ${idx === currentStep ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700'}`}>
                                {idx + 1}. {label}
                            </div>
                        ))}
                    </div>
                </div>

                {/* Step Content */}
                <div className="bg-white shadow rounded p-6">
                    {currentStep === 0 && (
                        <div>
                            <h2 className="text-lg font-semibold mb-4">Select role</h2>
                            <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                                {['Company', 'Logistics', 'Journalist', 'Employer', 'Agent'].map((role) => (
                                    <button
                                        key={role}
                                        type="button"
                                        onClick={() => form.setData('role', role)}
                                        className={`px-3 py-2 border rounded ${form.data.role === role ? 'bg-indigo-600 text-white' : 'bg-gray-50'}`}
                                    >
                                        {role}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {currentStep === 1 && (
                        <div>
                            <h2 className="text-lg font-semibold mb-2">Confirmation / Moderation of Role</h2>
                            <p className="text-gray-600">If your company needs full rights to all services, moderation may be required. Continue to proceed; we will handle permissions during publishing.</p>
                        </div>
                    )}

                    {currentStep === 2 && (
                        <div>
                            <h2 className="text-lg font-semibold mb-4">Select type of posting</h2>
                            <div className="grid grid-cols-2 md:grid-cols-3 gap-3">
                                {['product','service','vacancy','news','tender','auction'].map((t) => (
                                    <button
                                        key={t}
                                        type="button"
                                        onClick={() => form.setData('type', t)}
                                        className={`px-3 py-2 border rounded ${form.data.type === t ? 'bg-indigo-600 text-white' : 'bg-gray-50'}`}
                                    >
                                        {t.charAt(0).toUpperCase() + t.slice(1)}
                                    </button>
                                ))}
                            </div>
                        </div>
                    )}

                    {currentStep === 3 && (
                        <div>
                            <h2 className="text-lg font-semibold mb-4">Select category and subcategories</h2>
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Category</label>
                                <select
                                    className="mt-1 w-full border rounded px-3 py-2"
                                    value={form.data.category}
                                    onChange={(e) => form.setData('category', e.target.value)}
                                >
                                    <option value="">Select...</option>
                                    {categories.map((c) => (
                                        <option key={c} value={c}>{c}</option>
                                    ))}
                                </select>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Subcategories</label>
                                <input
                                    type="text"
                                    placeholder="Comma separated"
                                    className="mt-1 w-full border rounded px-3 py-2"
                                    onChange={(e) => form.setData('subcategories', e.target.value.split(',').map(s => s.trim()).filter(Boolean))}
                                />
                            </div>
                        </div>
                    )}

                    {currentStep === 4 && (
                        <div>
                            <h2 className="text-lg font-semibold mb-4">Details</h2>

                            {/* Listing Type (deal_type) */}
                            <div className="mb-4">
                                <label className="block text-sm font-medium text-gray-700">Listing type</label>
                                <div className="mt-1 flex flex-wrap gap-4">
                                    {[
                                        { key: 'sale', label: 'For Sale' },
                                        { key: 'rent', label: 'For Rent' },
                                        { key: 'auction', label: 'Auction' },
                                        { key: 'wanted', label: 'Wanted (Tender)' },
                                    ].map((opt) => (
                                        <label key={opt.key} className="inline-flex items-center gap-2 text-sm">
                                            <input
                                                type="radio"
                                                name="deal_type"
                                                checked={form.data.deal_type === opt.key}
                                                onChange={() => handleDealTypeChange(opt.key)}
                                            />
                                            <span>{opt.label}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>

                            {/* Seller/Company default Sales fields */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Title</label>
                                    <input
                                        maxLength={120}
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.title}
                                        onChange={(e) => form.setData('title', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Location</label>
                                    <input
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.location}
                                        onChange={(e) => form.setData('location', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Price</label>
                                    <input
                                        type="number"
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.price}
                                        onChange={(e) => form.setData('price', e.target.value)}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Currency</label>
                                    <input
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.currency}
                                        onChange={(e) => form.setData('currency', e.target.value)}
                                    />
                                </div>
                                <div className="md:col-span-2">
                                    <label className="block text-sm font-medium text-gray-700">Description / specifications</label>
                                    <textarea
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        rows={4}
                                        value={form.data.description}
                                        onChange={(e) => form.setData('description', e.target.value)}
                                    />
                                </div>
                            </div>

                            {/* Product details */}
                            <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Condition</label>
                                    <select
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.product_fields.condition}
                                        onChange={(e) => form.setData('product_fields', { ...form.data.product_fields, condition: e.target.value })}
                                    >
                                        <option value="new">New</option>
                                        <option value="used">Used</option>
                                        <option value="refurbished">Refurbished</option>
                                    </select>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Manufacturer</label>
                                    <input
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.product_fields.manufacturer}
                                        onChange={(e) => form.setData('product_fields', { ...form.data.product_fields, manufacturer: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Model</label>
                                    <input
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.product_fields.model}
                                        onChange={(e) => form.setData('product_fields', { ...form.data.product_fields, model: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Year</label>
                                    <input
                                        type="number"
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.product_fields.year}
                                        onChange={(e) => form.setData('product_fields', { ...form.data.product_fields, year: e.target.value })}
                                    />
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Availability status</label>
                                    <select
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.product_fields.availability_status}
                                        onChange={(e) => form.setData('product_fields', { ...form.data.product_fields, availability_status: e.target.value })}
                                    >
                                        <option value="in_stock">In stock</option>
                                        <option value="on_rent">On rent</option>
                                        <option value="sold">Sold</option>
                                    </select>
                                </div>
                            </div>

                            {/* Payment methods */}
                            <div className="mt-6">
                                <label className="block text-sm font-medium text-gray-700">Payment method</label>
                                <div className="mt-1 flex flex-wrap gap-3 text-sm">
                                    {[
                                        { key: 'cash', label: 'Cash payment' },
                                        { key: 'bank_transfer', label: 'Bank transfer' },
                                        { key: 'visa_stripe', label: 'Through the site (Visa/Stripe)' },
                                    ].map((opt) => {
                                        const checked = form.data.payment_options.includes(opt.key);
                                        return (
                                            <label key={opt.key} className="inline-flex items-center gap-2">
                                                <input
                                                    type="checkbox"
                                                    checked={checked}
                                                    onChange={(e) => {
                                                        const next = new Set(form.data.payment_options);
                                                        if (e.target.checked) next.add(opt.key); else next.delete(opt.key);
                                                        form.setData('payment_options', Array.from(next));
                                                    }}
                                                />
                                                <span>{opt.label}</span>
                                            </label>
                                        );
                                    })}
                                </div>
                                <div className="mt-2 flex items-center gap-2">
                                    <input
                                        placeholder="Add another method"
                                        className="flex-1 border rounded px-3 py-2"
                                        value={customPayment}
                                        onChange={(e) => setCustomPayment(e.target.value)}
                                    />
                                    <button
                                        type="button"
                                        className="px-3 py-2 text-sm rounded bg-gray-100 border"
                                        onClick={() => {
                                            const val = customPayment.trim();
                                            if (!val) return;
                                            const next = new Set(form.data.payment_options);
                                            next.add(val);
                                            form.setData('payment_options', Array.from(next));
                                            setCustomPayment('');
                                        }}
                                    >
                                        Add
                                    </button>
                                </div>
                            </div>

                            {/* Placement type & term */}
                            <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Placement type</label>
                                    <div className="mt-1 flex gap-4 text-sm">
                                        {[
                                            { key: 'paid', label: 'Paid (contacts visible)' },
                                            { key: 'free', label: 'Free (contacts hidden)' },
                                        ].map((opt) => (
                                            <label key={opt.key} className="inline-flex items-center gap-2">
                                                <input
                                                    type="radio"
                                                    name="placement_type"
                                                    checked={form.data.product_fields.placement_type === opt.key}
                                                    onChange={() => form.setData('product_fields', { ...form.data.product_fields, placement_type: opt.key })}
                                                />
                                                <span>{opt.label}</span>
                                            </label>
                                        ))}
                                    </div>
                                </div>
                                <div>
                                    <label className="block text-sm font-medium text-gray-700">Placement term</label>
                                    <select
                                        className="mt-1 w-full border rounded px-3 py-2"
                                        value={form.data.product_fields.placement_term}
                                        onChange={(e) => form.setData('product_fields', { ...form.data.product_fields, placement_term: Number(e.target.value) })}
                                    >
                                        <option value={30}>30 days</option>
                                        <option value={90}>90 days</option>
                                        <option value={365}>365 days</option>
                                    </select>
                                    <p className="text-xs text-gray-500 mt-1">Placement duration may depend on selected package.</p>
                                </div>
                            </div>

                            {/* Photos uploader */}
                            <div className="mt-6">
                                <label className="block text-sm font-medium text-gray-700">Photos / videos</label>
                                <input
                                    type="file"
                                    multiple
                                    accept="image/*,video/mp4"
                                    className="mt-1 w-full border rounded px-3 py-2"
                                    onChange={(e) => {
                                        const files = Array.from(e.target.files || []);
                                        setPhotoPreviews(files.map((f) => URL.createObjectURL(f)));
                                        form.setData('photos', files);
                                    }}
                                />
                                {photoPreviews.length > 0 && (
                                    <div className="mt-3 grid grid-cols-2 md:grid-cols-3 gap-2">
                                        {photoPreviews.map((src, idx) => (
                                            <img key={idx} src={src} alt="preview" className="rounded object-cover h-24 w-full" />
                                        ))}
                                    </div>
                                )}
                                <p className="text-xs text-gray-500 mt-1">10–200 files depending on the selected package.</p>
                            </div>

                            {/* Highlighted dashboard notifications prompting Rent/Auction */}
                            <div className="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded">
                                <p className="uppercase font-semibold mb-2">Do you want to also publish this equipment in the Rent section?</p>
                                <p className="text-sm text-gray-600 mb-3">Check the box and fill in the additional fields required for the Rent section (see Technical Specification).</p>
                                <label className="inline-flex items-center">
                                    <input
                                        type="checkbox"
                                        checked={form.data.publish_in_rent}
                                        onChange={(e) => form.setData('publish_in_rent', e.target.checked)}
                                        className="mr-2"
                                    />
                                    Also publish in Rent
                                </label>
                            </div>

                            {form.data.publish_in_rent && (
                                <div className="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Minimum rental period</label>
                                        <input
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            placeholder="e.g., 7 days or 1 month"
                                            value={form.data.rent_fields.min_period}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, min_period: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Base Rent Price</label>
                                        <input
                                            type="number"
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.rent_fields.rent_price}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, rent_price: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Currency</label>
                                        <input
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.rent_fields.rent_currency}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, rent_currency: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Rental price per day</label>
                                        <input
                                            type="number"
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.rent_fields.rental_price_day}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, rental_price_day: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Rental price per week</label>
                                        <input
                                            type="number"
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.rent_fields.rental_price_week}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, rental_price_week: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Rental price per month</label>
                                        <input
                                            type="number"
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.rent_fields.rental_price_month}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, rental_price_month: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Rental price per year</label>
                                        <input
                                            type="number"
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.rent_fields.rental_price_year}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, rental_price_year: e.target.value })}
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700">Rental conditions / terms</label>
                                        <textarea
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            rows={3}
                                            value={form.data.rent_fields.rent_terms}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, rent_terms: e.target.value })}
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700">Desired request countries</label>
                                        <select
                                            multiple
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.rent_fields.request_countries || []}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, request_countries: Array.from(e.target.selectedOptions).map(o => o.value) })}
                                        >
                                            {countryOptions.map((c) => (
                                                <option key={c.code} value={c.name}>{c.name}</option>
                                            ))}
                                        </select>
                                        {fieldError('rent_fields.request_countries') && (
                                            <p className="mt-1 text-xs text-red-600">{fieldError('rent_fields.request_countries')}</p>
                                        )}
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700">Additional description</label>
                                        <textarea
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            rows={3}
                                            value={form.data.rent_fields.additional_description}
                                            onChange={(e) => form.setData('rent_fields', { ...form.data.rent_fields, additional_description: e.target.value })}
                                        />
                                    </div>
                                    <div className="md:col-span-2">
                                        <label className="block text-sm font-medium text-gray-700">Upload rental contracts / PDFs</label>
                                        <input
                                            type="file"
                                            multiple
                                            accept="application/pdf,.doc,.docx,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            onChange={(e) => form.setData('rent_documents', e.target.files)}
                                        />
                                        <p className="mt-1 text-xs text-gray-500">You can upload multiple files. Supported: PDF/DOC/DOCX.</p>
                                        {fieldError('rent_documents') && (
                                            <p className="mt-1 text-xs text-red-600">{fieldError('rent_documents')}</p>
                                        )}
                                    </div>
                                </div>
                            )}

                            {/* Role-specific fields (Logistics as example) */}
                            {form.data.role === 'Logistics' && (
                                <div className="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Dimensions</label>
                                        <input
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.logistics_fields.dimensions}
                                            onChange={(e) => form.setData('logistics_fields', { ...form.data.logistics_fields, dimensions: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Route</label>
                                        <input
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.logistics_fields.route}
                                            onChange={(e) => form.setData('logistics_fields', { ...form.data.logistics_fields, route: e.target.value })}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Transport Type</label>
                                        <input
                                            className="mt-1 w-full border rounded px-3 py-2"
                                            value={form.data.logistics_fields.transport_type}
                                            onChange={(e) => form.setData('logistics_fields', { ...form.data.logistics_fields, transport_type: e.target.value })}
                                        />
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {currentStep === 5 && (
                        <div>
                            <h2 className="text-lg font-semibold mb-4">Preview</h2>
                            <div className="border rounded p-4">
                                <div className="font-medium">{form.data.title || 'Untitled'}</div>
                                <div className="text-sm text-gray-600">{form.data.description || 'No description'}</div>
                                <div className="mt-2 text-sm">Price: {form.data.price || 'N/A'} {form.data.currency}</div>
                                <div className="mt-2 text-sm">Location: {form.data.location || 'N/A'}</div>
                                <div className="mt-2 text-sm">Category: {form.data.category || 'N/A'}</div>
                                <div className="mt-2 text-sm">Type: {form.data.type}</div>
                                <div className="mt-2 text-sm">Publish in Rent: {form.data.publish_in_rent ? 'Yes' : 'No'}</div>
                                <div className="mt-2 text-sm">Publish in Auction: {form.data.publish_in_auction ? 'Yes' : 'No'}</div>
                                {form.data.publish_in_rent && (
                                  <div className="mt-3 text-sm">
                                    <div className="font-medium">Rent details</div>
                                    <ul className="list-disc pl-5">
                                      <li>Min period: {form.data.rent_fields.min_period || '—'}</li>
                                      <li>Base price: {form.data.rent_fields.rent_price || '—'} {form.data.rent_fields.rent_currency}</li>
                                      <li>Per day/week/month/year: {form.data.rent_fields.rental_price_day || '—'} / {form.data.rent_fields.rental_price_week || '—'} / {form.data.rent_fields.rental_price_month || '—'} / {form.data.rent_fields.rental_price_year || '—'}</li>
                                      <li>Countries: {(form.data.rent_fields.request_countries || []).join(', ') || '—'}</li>
                                      <li>Documents: {Array.isArray(form.data.rent_documents) ? form.data.rent_documents.length : 0}</li>
                                    </ul>
                                  </div>
                                )}
                                {form.data.publish_in_auction && (
                                  <div className="mt-3 text-sm">
                                    <div className="font-medium">Auction details</div>
                                    <ul className="list-disc pl-5">
                                      <li>Start price: {form.data.auction_fields.start_price || '—'} {form.data.auction_fields.auction_currency}</li>
                                      <li>Buy now: {form.data.auction_fields.buy_now_price || '—'}</li>
                                      <li>Min step: {form.data.auction_fields.min_step || '—'}</li>
                                      <li>Time limit: {form.data.auction_fields.time_limit_minutes || '—'} minutes</li>
                                      <li>Dates: {form.data.auction_fields.start_date || '—'} → {form.data.auction_fields.end_date || '—'}</li>
                                    </ul>
                                  </div>
                                )}
                            </div>
                            <div className="mt-4">
                                <label className="block text-sm font-medium text-gray-700">Comment</label>
                                <textarea
                                    className="mt-1 w-full border rounded px-3 py-2"
                                    rows={3}
                                    value={form.data.preview_comment}
                                    onChange={(e) => form.setData('preview_comment', e.target.value)}
                                />
                            </div>
                        </div>
                    )}

                    {currentStep === 6 && (
                        <div>
                            <h2 className="text-lg font-semibold mb-4">Select a Package</h2>
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                {packages.map((p) => (
                                    <button
                                        key={p.key}
                                        type="button"
                                        onClick={() => form.setData('package', p.key)}
                                        className={`border rounded p-4 text-left ${form.data.package === p.key ? 'border-indigo-600 bg-indigo-50' : 'border-gray-200'}`}
                                    >
                                        <div className="font-semibold">{p.name}</div>
                                        <div className="text-sm text-gray-600">{p.desc}</div>
                                        <div className="mt-2 font-medium">${p.price}</div>
                                    </button>
                                ))}
                            </div>
                            <div className="mt-4 text-sm text-gray-600">
                                Payment flows will use dashboard balance and moderation will follow.
                            </div>
                        </div>
                    )}
                </div>

                {/* Controls */}
                <div className="mt-6 flex items-center justify-between">
                    <div>
                        <Link href="/dashboard" className="text-sm text-gray-600 underline">Back to Dashboard</Link>
                    </div>
                    <div className="flex items-center gap-2">
                        <button type="button" onClick={prev} className="px-4 py-2 border rounded">Previous</button>
                        {currentStep < steps.length - 1 ? (
                            <button type="button" onClick={next} disabled={!canNext} className="px-4 py-2 bg-indigo-600 text-white rounded disabled:opacity-50">Next</button>
                        ) : (
                            <button type="button" onClick={submit} disabled={form.processing || !canNext} className="px-4 py-2 bg-green-600 text-white rounded">Submit</button>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}